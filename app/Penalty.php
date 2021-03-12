<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Penalty extends Model
{
    protected $table = 'customer_loan_penalties';

    protected static function boot(){
        parent::boot();

        static::addGlobalScope('deleted', function (Builder $builder) {
            //$builder->whereDate('created_at', '>', Carbon::parse("2019-01-01")->toDateString());
            $builder->whereYear('created_at', '>', "2018");
        });
    }

    public function loan(){
    	return $this->belongsTo('App\Loan');
    }
}
