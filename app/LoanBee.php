<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanBee extends Model
{
    protected $table = 'customer_bee_history';



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
