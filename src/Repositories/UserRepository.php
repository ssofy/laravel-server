<?php

namespace SSOfy\Laravel\Repositories;

use Illuminate\Hashing\BcryptHasher;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use SSOfy\Enums\FilterOperator;
use SSOfy\Enums\SortOrder;
use SSOfy\Helper;
use SSOfy\Models\Filter;
use SSOfy\Models\Sort;
use SSOfy\Models\Entities\PaginatedResponseEntity;
use SSOfy\Models\Entities\TokenEntity;
use SSOfy\Repositories\UserRepositoryInterface;
use SSOfy\Laravel\Models\UserSocialLink;
use SSOfy\Laravel\UserTokenManager;
use SSOfy\Laravel\Transformers\UserTransformer;
use ReflectionClass;

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
    public function find($filters, $ip = null)
    {
        $model = $this->getUserModel();
        $query = $model::query();

        foreach ($filters as $filter) {
            $query = $this->setFilterCriteria($query, $filter);
        }

        $user = $query->first();
        if (is_null($user)) {
            return null;
        }

        return $this->userTransformer->transform($user);
    }

    public function findAll($filters = [], $sorts = [], $count = 10, $page = 1, $ip = null)
    {
        $model = $this->getUserModel();
        $query = $model::query();

        foreach ($filters as $filter) {
            $query = $this->setFilterCriteria($query, $filter);
        }

        foreach ($sorts as $sort) {
            $query = $this->setSortCriteria($query, $sort);
        }

        /** @var LengthAwarePaginator $result */
        $result     = $query->paginate($count);
        $resultData = $result
            ->getCollection()
            ->transform(function ($user) {
                return $this->userTransformer->transform($user);
            })
            ->toArray();

        return new PaginatedResponseEntity([
            'data'        => $resultData,
            'page'        => intval($result->currentPage()),
            'page_size'   => intval($result->perPage()),
            'total_pages' => intval($result->lastPage()),
            'total_count' => intval($result->total()),
        ]);
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
            $found = $this->findById($link->user_id, $ip);
            if (!is_null($found)) {
                return $found;
            }
        }

        $providerId = $user->id;

        $userEntity = $this->findByEmailOrCreate($user, null, $ip);

        UserSocialLink::create([
            'provider'    => $provider,
            'provider_id' => $providerId,
            'user_id'     => $userEntity->id,
        ]);

        return $userEntity;
    }

    /**
     * @inheritDoc
     */
    public function findByEmailOrCreate($user, $password = null, $ip = null)
    {
        $filter = new Filter([
            'key'   => 'email',
            'value' => $user->email,
        ]);

        $existingUser = $this->find([$filter], $ip);
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

        $userAttributes = $user->toArray(false);

        if (isset($userAttributes['id'])) {
            unset($userAttributes['id']);
        }

        if (is_null($password)) {
            $password = Helper::randomString(16);
        }

        $userAttributes['password'] = Hash::make($password);

        $userModel = new $model;

        $userEntity = $this->storeUser($userAttributes, $userModel);

        $userEntity->created = true;
        return $userEntity;
    }

    /**
     * @inheritDoc
     */
    public function update($user, $ip = null)
    {
        if (is_null($user->hash)) {
            // to avoid mandatory field error on "hash" attribute.
            $user->hash = $user->id;
        }

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

        if (is_null($password)) {
            return $user->$passwordColumn === null;
        }

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
            $passwordColumn = $this->getDBColumn('password');

            if (isset($userAttributes[$passwordColumn])) {
                unset($userAttributes[$passwordColumn]);
            }

            $userModelCasts = $this->getModelCasts($userModel);
            if (isset($userModelCasts) && $userModelCasts === 'array') {
                $userModel->$metadataColumn = $userAttributes;
            } else {
                $userModel->$metadataColumn = json_encode($userAttributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        $userModel->save();
        $userModel->refresh();

        return $this->userTransformer->transform($userModel);
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

    /**
     * @param Filter $filter
     */
    private function setFilterCriteria($query, $filter)
    {
        if (is_array($filter)) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter as $subFilter) {
                    $this->setFilterCriteria($query, $subFilter);
                }
            });
            return $query;
        }

        $claim    = $filter->key;
        $operator = $filter->operator;
        $value    = $filter->value;

        $column = $this->getDBColumn($claim);
        if (is_null($column)) {
            return $query;
        }

        switch ($operator) {
            case FilterOperator::EQUALS:
                return $query->where($column, $value);
            case FilterOperator::NOT_EQUALS:
                return $query->where($column, '<>', $value);
            case FilterOperator::GREATER_THAN:
                return $query->where($column, '>', $value);
            case FilterOperator::GREATER_THAN_OR_EQUAL_TO:
                return $query->where($column, '>=', $value);
            case FilterOperator::LESS_THAN:
                return $query->where($column, '<', $value);
            case FilterOperator::LESS_THAN_OR_EQUAL_TO:
                return $query->where($column, '<=', $value);
            case FilterOperator::CONTAINS:
                return $query->where($column, 'LIKE', "%$value%");
            case FilterOperator::STARTS_WITH:
                return $query->where($column, 'LIKE', "$value%");
            case FilterOperator::ENDS_WITH:
                return $query->where($column, 'LIKE', "%$value");
        }

        return $query;
    }

    /**
     * @param Sort $sort
     */
    private function setSortCriteria($query, $sort)
    {
        $claim = $sort->key;

        $column = $this->getDBColumn($claim);
        if (is_null($column)) {
            return $query;
        }

        switch ($sort->order) {
            case SortOrder::ASCENDING:
                return $query->orderBy($column, 'asc');
            case SortOrder::DESCENDING:
                return $query->orderBy($column, 'desc');
        }

        return $query;
    }
}
