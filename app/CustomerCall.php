<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerCall extends Model
{
    protected $table = 'customer_call_log';

    public function getCallDurationAttribute($value){
    	return $this->attributes['call_duration'] = (int)$value;
    }
}
