<?php

namespace SSOfy\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use SSOfy\Models\Entities\UserEntity;

class UserEntityValidation implements Rule
{
    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var boolean
     */
    protected $requiredFieldsCheck;

    public function __construct($requiredFieldsCheck = false)
    {
        $this->requiredFieldsCheck = $requiredFieldsCheck;
    }

    /**
     * @return self
     */
    public static function make($requiredFieldsCheck = false)
    {
        return app(static::class, [
            'requiredFieldsCheck' => $requiredFieldsCheck,
        ]);
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $userEntity = new UserEntity([
                $value['key'] => isset($value['value']) ? $value['value'] : null,
            ]);

            $userEntity->toArray($this->requiredFieldsCheck); // Triggers validation in the UserEntity class
            return true;
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * @return string
     */
    public function message()
    {
        return 'The :attribute contains invalid data. ' . $this->message . '.';
    }
}
