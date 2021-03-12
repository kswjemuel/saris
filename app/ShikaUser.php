<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShikaUser extends Model
{
    protected $table = 'users';
    protected $hidden = ['password', 'user_pin', 'confirmation_code', 'email_confirmed', 'api_key', 'user_account_block_status', 'user_account_block_reason', 'is_temporary_pin', 'remember_token', 'last_login_date', 'temporary_pin_expire_at', 'created_at', 'updated_at'];

    public function customer(){
        return $this->hasOne('App\Customer');
    }
}
