<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use SSOfy\Laravel\Filters\Contracts\UserFilterInterface;
use SSOfy\Laravel\Repositories\Contracts\ClientRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\ScopeRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Traits\Validation;
use SSOfy\Models\Entities\ClientEntity;
use SSOfy\Models\Entities\ScopeEntity;
use SSOfy\Models\Entities\UserEntity;

class OAuthServerController extends AbstractController
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

        $userId = $request->input('id');
        $scopes = $request->input('scopes');

        $user = $userRepository->findById($userId);

        if (is_null($user)) {
            abort(204, 'Not Found');
        }

        /** @var UserFilterInterface $filter */
        $filter = app(config('ssofy.user.filter'));

        return $filter->filter($user, $scopes);
    }
}
