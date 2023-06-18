<?php

namespace SSOfy\Laravel\Repositories;

use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use ReflectionClass;
use SSOfy\Helper;
use SSOfy\Laravel\Models\UserSocialLink;
use SSOfy\Laravel\UserTokenManager;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Transformers\UserTransformer;
use SSOfy\Models\Entities\TokenEntity;

class UserRepository implements UserRepositoryInterface
{
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
        $model = $this->getUserModel();

        $user = $model::find($id);

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
    public function findBySocialLinkOrCreate($provider, $user, $ip = null)
    {
        // the user entity holds the id provided by the provider

        $link = UserSocialLink::where('provider', $provider)
                              ->where('provider_id', $user->id)
                              ->first();

        if (!is_null($link)) {
            return $this->findById($link->user_id, $ip);
        }

        $providerId = $user->id;

        $created = $this->findByEmailOrCreate($user, null, $ip);

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
            $model = $this->getUserModel();

            $column = $this->getDBColumn($field);

            return $model::where($column, $value)->first();
        });

        if (is_null($user)) {
            return null;
        }

        return $this->userTransformer->transform($user);
    }

    /**
     * @inheritDoc
     */
    public function findByEmailOrCreate($user, $password = null, $ip = null)
    {
        $existingUser = $this->find('email', $user->email, $ip);
        if (!is_null($existingUser)) {
            return $existingUser;
        }

        if (empty($user->name)) {
            $user->name = explode('@', $user->email)[0];
        }

        return $this->create($user, $password, $ip);
    }

    /**
     * @inheritDoc
     */
    public function create($user, $password = null, $ip = null)
    {
        $model = $this->getUserModel();

        $userAttributes = $user->toArray();

        if (isset($userAttributes['id'])) {
            unset($userAttributes['id']);
        }

        if (is_null($password)) {
            $password = Helper::randomString(16);
        }

        $userAttributes['password'] = Hash::make($password);

        $userModel = new $model;

        return $this->storeUser($userAttributes, $userModel);
    }

    /**
     * @inheritDoc
     */
    public function update($user, $ip = null)
    {
        $userAttributes = $user->toArray();

        $model = $this->getUserModel();

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
        $model = $this->getUserModel();

        $user = $model::find($userId);

        if (is_null($user)) {
            return false;
        }

        /** @var BcryptHasher $hasher */
        $hasher = app(BcryptHasher::class);

        $passwordColumn = $this->getDBColumn('password');

        return $hasher->check($password, $user->$passwordColumn);
    }

    /**
     * @inheritDoc
     */
    public function updatePassword($userId, $password, $ip = null)
    {
        $model = $this->getUserModel();

        $userModel = $model::findOrFail($userId);

        /** @var BcryptHasher $hasher */
        $hasher = app(BcryptHasher::class);

        $passwordColumn = $this->getDBColumn('password');

        $userModel->$passwordColumn = $hasher->make($password);

        $userModel->save();
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

    protected function getDBColumn($claim)
    {
        return config("ssofy-server.user.columns.{$claim}");
    }

    protected function getUserModel()
    {
        return config('ssofy-server.user.model');
    }

    protected function getModelCasts($model)
    {
        $reflection    = new ReflectionClass($model);
        $castsProperty = $reflection->getProperty('casts');
        $castsProperty->setAccessible(true);
        return $castsProperty->getValue($model);
    }
}
