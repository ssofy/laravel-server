<?php

namespace SSOfy\Laravel\Repositories\Contracts;

use SSOfy\Models\Entities\TokenEntity;
use SSOfy\Models\Entities\UserEntity;

interface UserRepositoryInterface
{
    /**
     * Find user by id.
     *
     * @param string $id
     * @param string $ip
     * @return UserEntity|null
     */
    public function findById($id, $ip = null);

    /**
     * Find user by token.
     *
     * @param string $token
     * @param string|null $ip
     * @return UserEntity|null
     */
    public function findByToken($token, $ip = null);

    /**
     * Find user by social provided user.
     *
     * @param string $provider
     * @param UserEntity $user
     * @param string|null $ip
     * @return UserEntity|null
     */
    public function findBySocialLink($provider, $user, $ip = null);

    /**
     * Find user by credentials such as email, phone, etc.
     *
     * @param string $field
     * @param string $value
     * @param string|null $ip
     * @return UserEntity|null
     */
    public function find($field, $value, $ip = null);

    /**
     * Find or create user by email.
     *
     * @param UserEntity $user
     * @param string|null $ip
     * @return UserEntity|null
     */
    public function findByEmailOrCreate($user, $ip = null);

    /**
     * Create a user.
     *
     * @param UserEntity $user
     * @param string|null $password
     * @param string|null $ip
     * @return UserEntity
     */
    public function create($user, $password = null, $ip = null);

    /**
     * Update a user.
     *
     * @param UserEntity $user
     * @param string|null $ip
     * @return UserEntity
     */
    public function update($user, $ip = null);

    /**
     * Generate and store a new token for password-less authentication.
     * Returns the generated token.
     *
     * @param string $userId
     * @param int $ttl
     * @return TokenEntity
     */
    public function createToken($userId, $ttl = 0);

    /**
     * Expire a token.
     *
     * @param string $token
     * @return void
     */
    public function deleteToken($token);

    /**
     * Verify user's password.
     *
     * @param string $userId
     * @param string|null $password
     * @param string|null $ip
     * @return boolean
     */
    public function verifyPassword($userId, $password = null, $ip = null);

    /**
     * Update user's password.
     *
     * @param string $userId
     * @param string $password
     * @param string|null $ip
     * @return void
     */
    public function updatePassword($userId, $password, $ip = null);
}
