<?php

namespace SSOfy\Laravel\Requests;

use Illuminate\Foundation\Http\FormRequest;
use SSOfy\Laravel\Traits\Rules;

class OAuth2ClientRedirectRequest extends FormRequest
{
    use Rules;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return $this->oauth2ClientRedirectRules();
    }
}
