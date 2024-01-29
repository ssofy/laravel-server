<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Repositories\OTPRepositoryInterface;
use SSOfy\Repositories\UserRepositoryInterface;
use SSOfy\Models\Entities\OTPOptionEntity;
use SSOfy\Laravel\UserTokenManager;
use SSOfy\Laravel\Traits\Mask;

class OTPRepository implements OTPRepositoryInterface
{
    use Mask;

    /**
     * @var UserTokenManager
     */
    private $otp;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(UserTokenManager $otp, UserRepositoryInterface $userRepository)
    {
        $this->otp            = $otp;
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     */
    public function findAllByAction($userId, $action, $ip = null)
    {
        $user = $this->userRepository->findById($userId, $ip);
        if (is_null($userId)) {
            return null;
        }

        $options = [];

        if (!is_null($user->email) && $this->methodIsEnabled('email')) {
            $options[] = $this->generateEmailOtpOption($action, $user);
        }

        if (!is_null($user->phone) && $this->methodIsEnabled('sms')) {
            $options[] = $this->generateSMSOtpOption($action, $user);
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function findById($optionId, $ip = null)
    {
        $parts = explode('-', $optionId);

        switch ($parts[1]) {
            case 'email':
                return $this->generateEmailOtpOption($parts[0], $this->userRepository->findById($parts[2], $ip));

            case 'sms':
                return $this->generateSMSOtpOption($parts[0], $this->userRepository->findById($parts[2], $ip));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function newVerificationCode($option, $ip = null)
    {
        $group = "otp-{$option->id}";
        return $this->otp->randomDigitsToken($option->user_id, 60 * 60, 6, $group);
    }

    /**
     * @inheritDoc
     */
    public function destroyVerificationCode($optionId, $code, $ip = null)
    {
        $group = "otp-$optionId";
        $this->otp->forget($code, $group);
    }

    public function verify($optionId, $code, $ip = null)
    {
        $group = "otp-$optionId";

        $ok = $this->otp->verify($code, $group) !== false;

        if (!$ok) {
            return false;
        }

        // mark user's email/phone as verified

        $option = $this->findById($optionId);
        if (is_null($option)) {
            return false;
        }

        $user = $this->userRepository->findById($option->user_id, $ip);
        if (is_null($user)) {
            return false;
        }

        if ($option->type === 'email' && !$user->email_verified) {
            $user->email_verified = true;
            $this->userRepository->update($user, $ip);
        }

        if ($option->type === 'sms' && !$user->phone_verified) {
            $user->phone_verified = true;
            $this->userRepository->update($user, $ip);
        }

        return true;
    }

    private function generateEmailOtpOption($action, $user)
    {
        return new OTPOptionEntity([
            'id'      => "$action-email-{$user->id}",
            'type'    => 'email',
            'to'      => $user->email,
            'hint'    => $this->hideEmailAddress($user->email),
            'user_id' => $user->id,
            'action'  => $action,
        ]);
    }

    private function generateSMSOtpOption($action, $user)
    {
        return new OTPOptionEntity([
            'id'      => "$action-sms-{$user->id}",
            'type'    => 'sms',
            'to'      => $user->phone,
            'hint'    => $this->hidePhoneNumber($user->phone),
            'user_id' => $user->id,
            'action'  => $action,
        ]);
    }

    private function methodIsEnabled($method)
    {
        return in_array($method, config('ssofy-server.otp.methods'));
    }
}
