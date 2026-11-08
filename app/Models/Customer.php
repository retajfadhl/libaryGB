<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        "name",
        "password",
        "phone_number",
        "balance",
        "address"
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
}
