<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use SSOfy\Laravel\Notifications\OTPNotification;
use SSOfy\Laravel\Repositories\Contracts\APIRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\OTPRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Rules\OTPVerification;
use SSOfy\Laravel\Traits\Validation;
use SSOfy\Models\Entities\OTPOptionEntity;

class EventController extends AbstractController
{
    use Validation;

    /*
     ------------------------------------------------------------
      PUBLIC METHODS
     ------------------------------------------------------------
     */

    /**
     * Handle event webhook.
     *
     * @param Request $request
     * @return bool[]
     */
    public function handle(
        Request                 $request,
        APIRepositoryInterface  $apiRepository,
        OTPRepositoryInterface  $otpRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->validateEventRequest($request);

        $action  = $request->input('action');
        $payload = $request->input('payload');

        switch ($action) {
            case 'token_deleted':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                if ($validatorFactory->make($payload, [
                    'token' => ['bail', 'required', 'string', 'min:1'],
                ])->failed()) {
                    abort(400, 'Bad Request');
                }
                //

                $this->tokenDeleted($payload, $apiRepository);
                break;

            case 'safety_reset':
                $this->safetyReset($apiRepository);
                break;

            case 'send_otp':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                if ($validatorFactory->make($payload, [
                    'option'         => ['bail', 'required', 'array'],
                    'option.id'      => ['bail', 'required', 'string'],
                    'option.type'    => ['bail', 'required', 'string'],
                    'option.to'      => ['bail', 'required', 'string'],
                    'option.hint'    => ['bail', 'required', 'string'],
                    'option.user_id' => ['bail', 'required', 'string'],
                    'option.action'  => ['bail', 'required', 'string'],
                    'ip'             => ['bail', 'nullable', 'string'],
                ])->failed()) {
                    abort(400, 'Bad Request');
                }
                //

                $this->sendOTP($payload, $otpRepository);
                break;

            case 'password_reset':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                if ($validatorFactory->make($payload, [
                    'token'    => ['bail', 'required', 'string', 'min:1', OTPVerification::make()],
                    'password' => ['bail', 'required', 'string', 'min:1'],
                    'ip'       => ['bail', 'nullable', 'ip'],
                ])->failed()) {
                    abort(400, 'Bad Request');
                }
                //

                $this->passwordReset($payload, $userRepository);
                break;
        }

        return [
            'success' => true
        ];
    }

    /*
     ------------------------------------------------------------
      PROTECTED METHODS
     ------------------------------------------------------------
     */

    /**
     * Token Deleted event handler.
     *
     * @param array $payload
     * @return void
     */
    protected function tokenDeleted($payload, APIRepositoryInterface $apiRepository)
    {
        $apiRepository->deleteToken($payload['token']);
    }

    /**
     * Safety Reset event handler.
     *
     * @return void
     */
    protected function safetyReset(APIRepositoryInterface $apiRepository)
    {
        $apiRepository->deleteAllTokens();
    }

    /**
     * Send OTP event handler.
     *
     * @param array $payload
     * @return void
     */
    protected function sendOTP($payload, OTPRepositoryInterface $otpRepository)
    {
        $option = new OTPOptionEntity($payload['option']);

        $code = $otpRepository->createCode($option);

        /*
         * Send notification
         */
        $brand   = config('ssofy.otp.notification.brand');
        $channel = config("ssofy.otp.notification.{$option->type}_channel");

        if (isset($channel)) {
            Notification::route($channel, $option->to)
                        ->notify(new OTPNotification($brand, $code, [$channel]));
        }
    }

    /**
     * Password Reset event handler.
     *
     * @param array $payload
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    public function passwordReset($payload, UserRepositoryInterface $userRepository)
    {
        $user = $userRepository->findByToken($payload['token'], $payload['ip']);
        if (is_null($user)) {
            abort(401, 'Unauthorized');
        }

        $userRepository->updatePassword($user->id, $payload['password'], $payload['ip']);

        $userRepository->deleteToken($payload['token']);
    }
}
