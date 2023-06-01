<?php

namespace SSOfy\Laravel\Repositories\Contracts;

use SSOfy\Models\Entities\OTPOptionEntity;

interface OTPRepositoryInterface
{
    /**
     * List of OTP options for the requested action.
     *
     * @param string $userId
     * @param string $action
     * @param string|null $ip
     * @return OTPOptionEntity[]
     */
    public function options($userId, $action, $ip = null);

    /**
     * Generate and store a new OTP code for the selected option.
     * Returns the generated code.
     *
     * @param OTPOptionEntity $option
     * @return string
     */
    public function newVerificationCode($option);

    /**
     * Expire a previously generated OTP.
     *
     * @param string $optionId
     * @param string $code
     * @return void
     */
    public function destroyVerificationCode($optionId, $code);

    /**
     * Get user id which was previously stored for a selected option and code.
     *
     * @param string $optionId
     * @param string $code
     * @return integer|null User ID or null if OTP is invalid
     */
    public function getUserId($optionId, $code);
}
