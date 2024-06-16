<?php

namespace SSOfy\Laravel\Rules;

use SSOfy\Enums\FilterOperator;

class UserFiltersValidation extends FilterValidation
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
     * @param array $filterData
     * @return bool
     */
    protected function validateFilter($attribute, $filterData)
    {
        $ok = parent::validateFilter($attribute, $filterData);
        if (!$ok) {
            return false;
        }

        if (isset($filterData['operator']) && $filterData['operator'] === FilterOperator::IN) {
            foreach ($filterData['value'] as $value) {
                $subFilterData = $filterData;
                $subFilterData['value'] = $value;
                if (!$this->userEntityValidation->passes($attribute, $subFilterData)) {
                    $this->message = $this->userEntityValidation->message();
                    return false;
                }
            }
            return true;
        }

        if (!$this->userEntityValidation->passes($attribute, $filterData)) {
            $this->message = $this->userEntityValidation->message();
            return false;
        }

        return true;
    }
}
