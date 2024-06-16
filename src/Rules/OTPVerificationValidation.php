<?php

namespace SSOfy\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use SSOfy\Laravel\UserTokenManager;

class OTPVerificationValidation implements Rule
{
    /**
     * @var UserTokenManager
     */
    private $otp;

    /**
     * @var string
     */
    private $group;

    /**
     * @var false
     */
    private $forget;

    public function __construct(UserTokenManager $otp, $group = null, $forget = false)
    {
        $this->otp    = $otp;
        $this->group  = $group;
        $this->forget = boolval($forget);
    }

    /**
     * @param null|string $group
     * @param bool $forget
     * @return self
     */
    public static function make($group = null, $forget = false)
    {
        return app(static::class, [
            'group'  => $group,
            'forget' => $forget,
        ]);
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return boolval($this->otp->verify($value, $this->group, $this->forget));
    }

    /**
     * @return string
     */
    public function message()
    {
        return 'The :attribute is not a valid token.';
    }
}
