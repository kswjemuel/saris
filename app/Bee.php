<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bee extends Model
{
    protected $table = 'customer_bee';

    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function scopeApproved($query){
        return $query->where('rejection_rules_status', '=', 'Approve');
    }

    public function scopeDeclined($query){
        return $query->where('rejection_rules_status', '=', 'Decline');
    }

    public function scopeGold($query){
        return $query->where('customer_ranking', '=', 'GOLD');
    }

    public function scopeSilver($query){
        return $query->where('customer_ranking', '=', 'SILVER');
    }

    public function scopeBronze($query){
        return $query->where('customer_ranking', '=', 'BRONZE');
    }

    public function scopeGreen($query){
        return $query->where('customer_ranking', '=', 'GREEN');
    }
}
