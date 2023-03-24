<?php

namespace SSOfy\Laravel\Repositories;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Hashing\BcryptHasher;
use SSOfy\Laravel\Models\UserSocialLink;
use SSOfy\Laravel\OTP;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Traits\Guard;
use SSOfy\Laravel\Transformers\UserTransformer;
use SSOfy\Models\Entities\TokenEntity;

class UserRepository implements UserRepositoryInterface
{
    use Guard;

    /**
     * @var OTP
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

    public function __construct(OTP $otp, UserTransformer $userTransformer)
    {
        $this->otp             = $otp;
        $this->userTransformer = $userTransformer;
    }

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

    public function findByToken($token, $ip = null)
    {
        $userId = $this->otp->verify($token, null, true);

        if (false === $userId) {
            return null;
        }

        return $this->findById($userId, $ip);
    }

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

    public function find($field, $value, $ip = null)
    {
        $user = $this->cache([$field => $value], function () use ($field, $value) {
            $column = $this->getColumn($field);

            return $this->userProvider()->retrieveByCredentials([
                $column => $value,
            ]);
        });

        if (is_null($user)) {
            return null;
        }

        return $this->userTransformer->transform($user);
    }

    public function findByEmailOrCreate($user, $ip = null)
    {
        $model = config('ssofy.user.model');

        $userModel = $model::firstOrCreate([
            $this->getColumn('email') => $user->email,
        ], [
            $this->getColumn('name')     => !is_null($user->name) ? $user->name : explode('@', $user->email)[0],
            $this->getColumn('email')    => $user->email,
            $this->getColumn('password') => '',
        ]);

        return $this->userTransformer->transform($userModel);
    }

    /**
     * @inheritDoc
     */
    public function createToken($userId, $ttl = 0)
    {
        return new TokenEntity([
            'token' => $this->otp->generateRandom($userId, $ttl),
            'ttl'   => $ttl,
        ]);
    }

    public function deleteToken($token)
    {
        $this->otp->forget($token);
    }

    public function verifyPassword($userId, $password, $ip = null)
    {
        $user = $this->cache(['id' => $userId], function () use ($userId) {
            return $this->userProvider()->retrieveById($userId);
        });

        if (is_null($user)) {
            return false;
        }

        return $this->userProvider()->validateCredentials($user, [
            config('ssofy.user.map.password') => $password,
        ]);
    }

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
            $column = $this->getColumn($field);
            if (isset($user->$column)) {
                $this->index["$field:{$user->$column}"] = $user;
            }
        }

        return $user;
    }

    protected function getColumn($method)
    {
        return config("ssofy.user.map.$method");
    }
}
