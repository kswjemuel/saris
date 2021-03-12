<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomerSMS extends Model
{
    protected $table = 'customer_smss';

    public function getMessageAmountAttribute($amount){
    	return $this->attributes['message_amount'] = (float)$amount;
    }
}
