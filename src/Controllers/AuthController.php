<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use SSOfy\Laravel\Filters\Contracts\UserFilterInterface;
use SSOfy\Laravel\Repositories\Contracts\OTPRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Traits\Mask;
use SSOfy\Laravel\Traits\Validation;
use SSOfy\Models\Entities\AuthResponseEntity;
use SSOfy\Models\Entities\OTPOptionEntity;
use SSOfy\Models\Entities\UserEntity;

class AuthController extends AbstractController
{
    use Validation;
    use Mask;

    /*
     ------------------------------------------------------------
      PUBLIC METHODS
     ------------------------------------------------------------
     */

    /**
     * @return AuthResponseEntity
     */
    public function passwordAuth(
        Request                 $request,
        UserRepositoryInterface $userRepository,
        OTPRepositoryInterface  $otpRepository
    ) {
        $this->validatePasswordAuthRequest($request);

        $method       = $request->input('method');
        $identifier   = $request->input('identifier');
        $password     = $request->input('password');
        $requestToken = $request->input('request_token');
        $ip           = $request->input('ip');

        switch ($method) {
            case 'otp':
                $user = $this->authenticateByOTP($identifier, $password, $ip, $otpRepository, $userRepository);
                break;
            case 'username':
            case 'email':
            case 'phone':
                $user = $this->authenticateByPassword($method, $identifier, $password, $ip, $userRepository);
                break;
            default:
                $user = false;
        }

        if (false === $user) {
            abort(401, 'Unauthorized');
        }

        $token = null;
        if ($requestToken) {
            $ttl   = 60 * 60;
            $token = $userRepository->createToken($user->id, $ttl);
        }

        /** @var UserFilterInterface $filter */
        $filter = app(config('ssofy.user.filter'));

        return new AuthResponseEntity([
            'user'  => $filter->filter($user, []),
            'token' => $token,
        ]);
    }

    /**
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @return AuthResponseEntity
     */
    public function tokenAuth(Request $request, UserRepositoryInterface $userRepository)
    {
        $this->validateTokenAuthRequest($request);

        $token = $request->input('token');
        $ip    = $request->input('ip');

        $user = $userRepository->findByToken($token, $ip);
        if (is_null($user)) {
            abort(401, 'Unauthorized');
        }

        return new AuthResponseEntity([
            'user' => $user
        ]);
    }

    /**
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @return AuthResponseEntity
     */
    public function socialAuth(Request $request, UserRepositoryInterface $userRepository)
    {
        // validations
        $validated = $this->validateSocialAuthRequest($request);

        try {
            $validated['user']['hash'] = '0';
            $user = new UserEntity($validated['user']);
        } catch (\SSOfy\Exceptions\Exception $exception) {
            abort(400, 'Bad Request');
        }
        //

        $provider = $request->input('provider');
        $ip       = $request->input('ip');

        $user = $userRepository->findBySocialLink($provider, $user, $ip);
        if (is_null($user)) {
            abort(409, 'Duplicate');
        }

        return new AuthResponseEntity([
            'user' => $user
        ]);
    }

    /**
     * @return OTPOptionEntity[]
     */
    public function otpOptions(
        Request                 $request,
        UserRepositoryInterface $userRepository,
        OTPRepositoryInterface  $otpRepository
    ) {
        $this->validateOTPOptionsRequest($request);

        $action     = $request->input('action');
        $method     = $request->input('method');
        $identifier = $request->input('identifier');
        $ip         = $request->input('ip');

        $user = $userRepository->find($method, $identifier, $ip);

        if (is_null($user)) {
            abort(401, 'Unauthorized');
        }

        return $otpRepository->options($user->id, $action, $ip);
    }


    /*
     ------------------------------------------------------------
      PROTECTED METHODS
     ------------------------------------------------------------
     */

    protected function authenticateByOTP(
        $optionId,
        $code,
        $ip,
        OTPRepositoryInterface $otpRepository,
        UserRepositoryInterface $userRepository
    ) {
        $userId = $otpRepository->getUserId($optionId, $code);
        if (is_null($userId)) {
            return false;
        }

        $user = $userRepository->findById($userId, $ip);
        if (is_null($user)) {
            return false;
        }

        $otpRepository->destroyVerificationCode($optionId, $code);

        return $user;
    }

    protected function authenticateByPassword(
        $method,
        $identifier,
        $password,
        $ip,
        UserRepositoryInterface $userRepository
    ) {
        $user = $userRepository->find($method, $identifier, $ip);
        if (is_null($user)) {
            return false;
        }

        if (!empty($password)) {
            $ok = $userRepository->verifyPassword($user->id, $password, $ip);
            if (!$ok) {
                return false;
            }
        }

        return $user;
    }
}
