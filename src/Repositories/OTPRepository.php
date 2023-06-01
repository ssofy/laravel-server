<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Laravel\OTP;
use SSOfy\Laravel\Repositories\Contracts\OTPRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Traits\Mask;
use SSOfy\Models\Entities\OTPOptionEntity;

class OTPRepository implements OTPRepositoryInterface
{
    use Mask;

    /**
     * @var OTP
     */
    private $otp;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(OTP $otp, UserRepositoryInterface $userRepository)
    {
        $this->otp            = $otp;
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     */
    public function options($userId, $action, $ip = null)
    {
        $user = $this->userRepository->findById($userId, $ip);
        if (is_null($userId)) {
            return null;
        }

        $email = $user->email;
        $phone = $user->phone;

        $options = [];

        if (!empty($email)) {
            $options[] = new OTPOptionEntity([
                'id'      => "$action-email-{$user->id}",
                'type'    => 'email',
                'to'      => $email,
                'hint'    => $this->hideEmailAddress($email),
                'user_id' => $user->id,
                'action'  => $action,
            ]);
        }

        if (!empty($phone)) {
            $options[] = new OTPOptionEntity([
                'id'      => "$action-sms-{$user->id}",
                'type'    => 'sms',
                'to'      => $phone,
                'hint'    => $this->hidePhoneNumber($phone),
                'user_id' => $user->id,
                'action'  => $action,
            ]);
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function newVerificationCode($option)
    {
        $group = "otp-{$option->id}";
        return $this->otp->randomDigitsOTP($option->user_id, 60 * 60, 6, $group);
    }

    /**
     * @inheritDoc
     */
    public function destroyVerificationCode($optionId, $code)
    {
        $group = "otp-$optionId";
        $this->otp->forget($code, $group);
    }

    /**
     * @inheritDoc
     */
    public function getUserId($optionId, $code)
    {
        $group = "otp-$optionId";
        return $this->otp->verify($code, $group);
    }
}
