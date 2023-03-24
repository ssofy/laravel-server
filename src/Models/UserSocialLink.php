<?php

namespace SSOfy\Laravel\Models;

use Illuminate\Database\Eloquent\Model;

class UserSocialLink extends Model
{
    protected $fillable = [
        'provider',
        'provider_id',
        'user_id',
    ];
}
