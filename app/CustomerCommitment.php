<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerCommitment extends Model
{
    protected $table = 'customer_commitments';

    public function loan(){
        return $this->belongsTo('App\Loan');
    }

    public function customer(){
        return $this->belongsTo('App\Customer');
    }
}
