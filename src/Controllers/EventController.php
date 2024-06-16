<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use SSOfy\Repositories\OTPRepositoryInterface;
use SSOfy\Repositories\UserRepositoryInterface;
use SSOfy\Models\Entities\OTPOptionEntity;
use SSOfy\Models\Entities\UserEntity;
use SSOfy\Laravel\Traits\Validation;
use SSOfy\Laravel\Rules\OTPVerificationValidation;
use SSOfy\Laravel\Events\OTPSent;
use SSOfy\Laravel\Events\SafetyReset;
use SSOfy\Laravel\Events\TokenDeleted;
use SSOfy\Laravel\Events\UserCreated;
use SSOfy\Laravel\Events\UserUpdated;

class EventController extends Controller
{
    use Validation;

    /*
     ------------------------------------------------------------
      PUBLIC METHODS
     ------------------------------------------------------------
     */

    /**
     * Handle event webhook.
     */
    public function handle(
        Request $request,
        OTPRepositoryInterface $otpRepository,
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
                $validatorFactory->make($payload, [
                    'token' => ['bail', 'required', 'string', 'min:1'],
                ])->validate();
                //

                $this->tokenDeleted($payload);
                break;

            case 'safety_reset':
                $this->safetyReset();
                break;

            case 'otp_requested':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                $validatorFactory->make($payload, [
                    'option'         => ['bail', 'required', 'array'],
                    'option.id'      => ['bail', 'required', 'string'],
                    'option.type'    => ['bail', 'required', 'string'],
                    'option.to'      => ['bail', 'required', 'string'],
                    'option.hint'    => ['bail', 'required', 'string'],
                    'option.user_id' => ['bail', 'required', 'string'],
                    'option.action'  => ['bail', 'required', 'string'],
                    'ip'             => ['bail', 'nullable', 'string'],
                ])->validate();
                //

                $this->sendOTP($payload, $otpRepository, $request->input('ip'));
                break;

            case 'password_reset':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                $validatorFactory->make($payload, [
                    'token'    => ['bail', 'required', 'string', 'min:1', OTPVerificationValidation::make()],
                    'password' => ['bail', 'required', 'string', 'min:1'],
                    'ip'       => ['bail', 'nullable', 'ip'],
                ])->validate();
                //

                $this->passwordReset($payload, $userRepository);
                break;

            case 'user_created':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                $validatorFactory->make($payload, [
                    'ip'   => ['bail', 'nullable', 'ip'],
                    'user' => ['bail', 'required', 'array'],
                ])->validate();
                //

                try {
                    $this->userCreated($payload, $userRepository);
                } catch (\SSOfy\Exceptions\Exception $exception) {
                    abort(400, 'Bad Request');
                }
                break;

            case 'user_updated':
                /*
                 * Payload validation
                 */
                $validatorFactory = app('Illuminate\Validation\Factory');
                $validatorFactory->make($payload, [
                    'user'    => ['bail', 'required', 'array'],
                    'user.id' => ['bail', 'required', 'string'],
                    'ip'      => ['bail', 'nullable', 'ip'],
                ])->validate();
                //

                try {
                    $this->userUpdated($payload, $userRepository);
                } catch (\SSOfy\Exceptions\Exception $exception) {
                    abort(400, 'Bad Request');
                }
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
    protected function tokenDeleted($payload)
    {
        event(new TokenDeleted($payload['token']));
    }

    /**
     * Safety Reset event handler.
     *
     * @return void
     */
    protected function safetyReset()
    {
        event(new SafetyReset());
    }

    /**
     * Send OTP event handler.
     *
     * @param array $payload
     * @return void
     */
    protected function sendOTP($payload, OTPRepositoryInterface $otpRepository, $ip = null)
    {
        $option = new OTPOptionEntity($payload['option']);

        $code = $otpRepository->newVerificationCode($option, $ip);

        /*
         * Send notification
         */
        $vars     = config('ssofy-server.otp.notification.vars', config('ssofy-server.otp.notification.settings'));
        $channels = config("ssofy-server.otp.notification.channels");

        $notificationClass = config('ssofy-server.otp.notification.class');

        if (isset($channels[$option->type])) {
            $channel = $channels[$option->type];
            Notification::route($channel, $option->to)
                        ->notify(
                            app($notificationClass, [
                                'option'   => $option,
                                'vars'     => $vars,
                                'code'     => $code,
                                'channels' => [$channel],
                            ])
                        );

            event(new OTPSent($option));
        }
    }

    /**
     * User Added event handler.
     *
     * @param array $payload
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    protected function userCreated($payload, UserRepositoryInterface $userRepository)
    {
        $payload['user']['id']   = '0';
        $payload['user']['hash'] = '0';

        $ip = Arr::get($payload, 'ip');

        /** @var UserEntity $user */
        $user = UserEntity::make($payload['user']);

        $user = $userRepository->create($user, Arr::get($payload['user'], 'password'), $ip);

        event(new UserCreated($user, $ip));
    }

    /**
     * User Updated event handler.
     *
     * @param array $payload
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    protected function userUpdated($payload, UserRepositoryInterface $userRepository)
    {
        $ip = Arr::get($payload, 'ip');

        /** @var UserEntity $user */
        $user = UserEntity::make($payload['user']);

        $user = $userRepository->update($user, $ip);

        event(new UserUpdated($user, $ip));
    }

    /**
     * Password Reset event handler.
     *
     * @param array $payload
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    protected function passwordReset($payload, UserRepositoryInterface $userRepository)
    {
        $ip = Arr::get($payload, 'ip');

        $user = $userRepository->findByToken($payload['token'], $ip);
        if (is_null($user)) {
            abort(401, 'Unauthorized');
        }

        $userRepository->updatePassword($user->id, $payload['password'], $ip);

        $userRepository->deleteToken($payload['token']);

        event(new UserUpdated($user, $ip));
    }

    protected function getEventQueueName()
    {
        return config('ssofy-server.event_queue');
    }
}
