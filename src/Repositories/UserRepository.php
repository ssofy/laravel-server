<?php

namespace SSOfy\Laravel\Repositories;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use ReflectionClass;
use SSOfy\Laravel\Models\UserSocialLink;
use SSOfy\Laravel\UserTokenManager;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Traits\Guard;
use SSOfy\Laravel\Transformers\UserTransformer;
use SSOfy\Models\Entities\TokenEntity;

class UserRepository implements UserRepositoryInterface
{
    use Guard;

    /**
     * @var UserTokenManager
     */
    private $otp;

    /**
     * @var UserTransformer
     */
    private $userTransformer;

    /**
     * @var array
     */
    private $index = [];

    public function __construct(UserTokenManager $otp, UserTransformer $userTransformer)
    {
        $this->otp             = $otp;
        $this->userTransformer = $userTransformer;
    }

    /**
     * @inheritDoc
     */
    public function findById($id, $ip = null)
    {
        $user = $this->cache(['id' => $id], function () use ($id) {
            return $this->userProvider()->retrieveById($id);
        });

        if (is_null($user)) {
            return null;
        }

        return $this->userTransformer->transform($user);
    }

    /**
     * @inheritDoc
     */
    public function findByToken($token, $ip = null)
    {
        $userId = $this->otp->verify($token, null, true);

        if (false === $userId) {
            return null;
        }

        return $this->findById($userId, $ip);
    }

    /**
     * @inheritDoc
     */
    public function findBySocialLink($provider, $user, $ip = null)
    {
        // the user entity holds the id provided by the provider

        $link = UserSocialLink::where('provider', $provider)
                              ->where('provider_id', $user->id)
                              ->first();

        if (!is_null($link)) {
            return $this->findById($link->user_id, $ip);
        }

        $providerId = $user->id;

        $created = $this->findByEmailOrCreate($user, $ip);

        UserSocialLink::create([
            'provider'    => $provider,
            'provider_id' => $providerId,
            'user_id'     => $created->id,
        ]);

        return $created;
    }

    /**
     * @inheritDoc
     */
    public function find($field, $value, $ip = null)
    {
        $user = $this->cache([$field => $value], function () use ($field, $value) {
            $column = $this->getDBColumn($field);

            return $this->userProvider()->retrieveByCredentials([
                $column => $value,
            ]);
        });

        if (is_null($user)) {
            return null;
        }

        return $this->userTransformer->transform($user);
    }

    /**
     * @inheritDoc
     */
    public function findByEmailOrCreate($user, $ip = null)
    {
        $existingUser = $this->find('email', $user->email, $ip);
        if (!is_null($existingUser)) {
            return $existingUser;
        }

        if (empty($user->name)) {
            $user->name = explode('@', $user->email)[0];
        }

        return $this->create($user, null, $ip);
    }

    /**
     * @inheritDoc
     */
    public function create($user, $password = null, $ip = null)
    {
        $model = config('ssofy.user.model');

        $userAttributes = $user->toArray();

        if (!is_null($password)) {
            $userAttributes['password'] = Hash::make($password);
        }

        $userModel = new $model;

        return $this->storeUser($userAttributes, $userModel);
    }

    /**
     * @inheritDoc
     */
    public function update($user, $ip = null)
    {
        $model = config('ssofy.user.model');

        $userAttributes = $user->toArray();

        $userModel = $model::findOrFail($userAttributes['id']);

        unset($userAttributes['id']);

        return $this->storeUser($userAttributes, $userModel);
    }

    /**
     * @inheritDoc
     */
    public function createToken($userId, $ttl = 0)
    {
        return new TokenEntity([
            'token' => $this->otp->randomStringToken($userId, $ttl),
            'ttl'   => $ttl,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function deleteToken($token)
    {
        $this->otp->forget($token);
    }

    /**
     * @inheritDoc
     */
    public function verifyPassword($userId, $password = null, $ip = null)
    {
        $user = $this->cache(['id' => $userId], function () use ($userId) {
            return $this->userProvider()->retrieveById($userId);
        });

        if (is_null($user)) {
            return false;
        }

        return $this->userProvider()->validateCredentials($user, [
            config('ssofy.user.column.password') => $password,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function updatePassword($userId, $password, $ip = null)
    {
        $user = $this->cache(['id' => $userId], function () use ($userId) {
            return $this->userProvider()->retrieveById($userId);
        });

        /** @var BcryptHasher $hasher */
        $hasher = app(BcryptHasher::class);
        $user->forceFill([
            'password' => $hasher->make($password),
        ]);

        $user->save();
    }

    protected function storeUser($userAttributes, $userModel)
    {
        if (!isset($userAttributes['name']) && !empty($this->getDBColumn('name'))) {
            $userAttributes['name'] = trim(Arr::get($userAttributes, 'given_name', '') . ' ' . Arr::get($userAttributes, 'family_name', ''));
        }

        foreach ($userAttributes as $attribute => $value) {
            $dbColumn = $this->getDBColumn($attribute);
            if (empty($dbColumn)) {
                continue;
            }

            $userModel->$dbColumn = $value;
        }

        $metadataColumn = $this->getDBColumn('metadata');

        if (!empty($metadataColumn)) {
            if (isset($userAttributes['password'])) {
                unset($userAttributes['password']);
            }

            $userModelCasts = $this->getModelCasts($userModel);
            if (isset($userModelCasts) && $userModelCasts === 'array') {
                $userModel->$metadataColumn = $userAttributes;
            } else {
                $userModel->$metadataColumn = json_encode($userAttributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        $userModel->save();

        return $this->userTransformer->transform($userModel);
    }

    /**
     * @return UserProvider
     */
    protected function userProvider()
    {
        $guardSettings = config('auth.guards.' . $this->findGuard());

        return app('auth')->createUserProvider($guardSettings['provider']);
    }

    protected function cache($criteria, $callback)
    {
        foreach ($criteria as $field => $value) {
            $key = "$field:$value";

            if (isset($this->index[$key])) {
                return $this->index[$key];
            }
        }

        $user = $callback();
        if (is_null($user)) {
            return null;
        }

        foreach (['id', 'email', 'phone'] as $field) {
            $column = $this->getDBColumn($field);
            if (isset($user->$column)) {
                $this->index["$field:{$user->$column}"] = $user;
            }
        }

        return $user;
    }

    protected function getDBColumn($column)
    {
        return config("ssofy.user.column.$column");
    }

    protected function getModelCasts($model)
    {
        $reflection    = new ReflectionClass($model);
        $castsProperty = $reflection->getProperty('casts');
        $castsProperty->setAccessible(true);
        return $castsProperty->getValue($model);
    }
}
