<?php

namespace App\Models;

use App\Models\Car;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasApiTokens as HasApiTokensContract;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Authenticatable implements HasApiTokensContract
{
    use HasApiTokens, HasFactory;

    protected $table = 'wp_latepoint_customers';

    protected $fillable = ['first_name', 'last_name', 'email','phone', 'status'];

    public function linkedSocialAccounts()
    {
        return $this->hasOne(LinkedSocialAccount::class);
    }
}
