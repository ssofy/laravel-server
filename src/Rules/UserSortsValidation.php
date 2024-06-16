<?php

namespace SSOfy\Laravel\Rules;

use SSOfy\Models\Sort;

class UserSortsValidation extends SortValidation
{
    /**
     * @var UserEntityValidation
     */
    protected $userEntityValidation;

    public function __construct(UserEntityValidation $userEntityValidation)
    {
        $this->userEntityValidation = $userEntityValidation;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $filter = new Sort($value);
            $filter->toArray(); // Triggers validation in the Sort class
            return true;
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }
}
