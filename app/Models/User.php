<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Firevel\FirebaseAuthentication\FirebaseAuthenticable;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;



class User extends Authenticatable
{
    use HasFactory, Notifiable, FirebaseAuthenticable;

    protected $table = 'users';

    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * 
     */
    protected $fillable = [
        'name',
        'email',
        'picture',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function notify($a):string
    {
        return $a;
        $user = Customer::where('votes', '=', 100)->value('id');

        return $user;
    }
}
