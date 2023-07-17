<?php

namespace SSOfy\Laravel\Repositories\Contracts;

use SSOfy\Models\Entities\OTPOptionEntity;

interface OTPRepositoryInterface
{
    /**
     * Get list of OTP options for the requested action.
     *
     * @param string $userId
     * @param string $action
     * @param string|null $ip
     * @return OTPOptionEntity[]
     */
    public function findAllByAction($userId, $action, $ip = null);

    /**
     * Find OTP Option by id
     *
     * @param string $optionId
     * @param string|null $ip
     * @return OTPOptionEntity|null
     */
    public function findById($optionId, $ip = null);

    /**
     * Generate and store a new OTP code for the selected option.
     * Returns the generated code.
     *
     * @param OTPOptionEntity $option
     * @param string|null $ip
     * @return string
     */
    public function newVerificationCode($option, $ip = null);

    /**
     * Expire a previously generated OTP.
     *
     * @param string $optionId
     * @param string $code
     * @param string|null $ip
     * @return void
     */
    public function destroyVerificationCode($optionId, $code, $ip = null);

    /**
     * @param string $optionId
     * @param string $code
     * @param string|null $ip
     * @return bool
     */
    public function verify($optionId, $code, $ip = null);
}
