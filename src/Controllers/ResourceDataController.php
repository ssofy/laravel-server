<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SSOfy\Laravel\Filters\Contracts\UserFilterInterface;
use SSOfy\Laravel\Repositories\Contracts\ClientRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\ScopeRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Traits\Validation;
use SSOfy\Models\Entities\ClientEntity;
use SSOfy\Models\Entities\ScopeEntity;
use SSOfy\Models\Entities\UserEntity;

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
        $this->validateScopeEntitiesRequest($request);

        $lang = $request->input('lang');

        return $scopeRepository->findAll($lang);
    }

    /**
     * @param Request $request
     * @param ClientRepositoryInterface $clientRepository
     * @return ClientEntity
     */
    public function client(Request $request, ClientRepositoryInterface $clientRepository)
    {
        $this->validateClientEntityRequest($request);

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
        $this->validateUserEntityRequest($request);

        $scopes = $request->input('scopes');

        foreach ([
            'id',
            'username',
            'email',
            'phone',
        ] as $field) {
            if ($request->has($field)) {
                $user = $userRepository->find($field, $request->input($field));

                if (is_null($user)) {
                    abort(204, 'Not Found');
                }

                /** @var UserFilterInterface $filter */
                $filter = app(config('ssofy-server.user.filter'));

                return $filter->filter($user, $scopes);
            }
        }

        abort(204, "Not Found");
    }
}
