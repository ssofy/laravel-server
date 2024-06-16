<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SSOfy\Models\Filter;
use SSOfy\Models\Sort;
use SSOfy\Repositories\ClientRepositoryInterface;
use SSOfy\Repositories\ScopeRepositoryInterface;
use SSOfy\Repositories\UserRepositoryInterface;
use SSOfy\Models\Entities\ClientEntity;
use SSOfy\Models\Entities\ScopeEntity;
use SSOfy\Models\Entities\UserEntity;
use SSOfy\Laravel\Filters\Contracts\UserFilterInterface;
use SSOfy\Laravel\Traits\Validation;

class ResourceDataController extends Controller
{
    use Validation;

    /*
     ------------------------------------------------------------
      PUBLIC METHODS
     ------------------------------------------------------------
     */

    /**
     * @param Request $request
     * @param ScopeRepositoryInterface $scopeRepository
     * @return ScopeEntity[]
     */
    public function scopes(Request $request, ScopeRepositoryInterface $scopeRepository)
    {
        $this->validateScopeResourceRequest($request);

        $lang = $request->input('lang');

        return $scopeRepository->all($lang);
    }

    /**
     * @param Request $request
     * @param ClientRepositoryInterface $clientRepository
     * @return ClientEntity
     */
    public function client(Request $request, ClientRepositoryInterface $clientRepository)
    {
        $this->validateClientResourceRequest($request);

        $clientId = $request->input('id');

        $client = $clientRepository->findById($clientId);
        if (is_null($client)) {
            abort(204, "Not Found");
        }

        return $client;
    }

    /**
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @return UserEntity
     */
    public function user(Request $request, UserRepositoryInterface $userRepository)
    {
        $this->validateUserResourceRequest($request);

        $scopes = $request->input('scopes');

        foreach (
            [
                'id',
                'username',
                'email',
                'phone',
            ] as $field
        ) {
            if (!$request->has($field)) {
                continue;
            }

            $filter = new Filter([
                'key' => $field,
                'value' => $request->input($field),
            ]);

            $user = $userRepository->find([$filter]);

            if (is_null($user)) {
                abort(204, 'Not Found');
            }

            /** @var UserFilterInterface $filter */
            $filter = app(config('ssofy-server.user.filter'));

            return $filter->filter($user, $scopes);
        }

        abort(204, "Not Found");
    }

    public function users(Request $request, UserRepositoryInterface $userRepository)
    {
        $this->validateUsersResourceRequest($request);

        $filters = $request->input('filters', []);
        $sorts   = $request->input('sorts', []);
        $count   = $request->input('count', 25);
        $ip      = $request->input('ip');

        $filters = $this->transformFilters($filters);
        $sorts   = $this->transformSorts($sorts);

        return $userRepository->findAll($filters, $sorts, $count, $ip);
    }

    protected function transformFilters($filters)
    {
        $result = [];
        foreach ($filters as $filter) {
            if (isset($filter['key'])) {
                $result[] = new Filter($filter);
            } elseif (is_array($filter)) {
                $result[] = $this->transformFilters($filter);
            }
        }
        return $result;
    }

    protected function transformSorts($sorts)
    {
        return array_map(function ($sort) {
            return new Sort($sort);
        }, $sorts);
    }
}
