<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Loan extends Model
{
    protected $table = 'customer_loans';

    protected static function boot(){
        parent::boot();

        static::addGlobalScope('deleted', function (Builder $builder) {
            $builder->where('loan_status', '!=', 'Deleted')
                    ->whereYear('created_at', '>', "2018");
        });
    }

    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function bee(){
        return $this->belongsTo('App\LoanBee', 'customer_bee_history_id');
    }

    public function disbursement(){
        return $this->hasOne('App\Disbursement');
    }

    public function repayments(){
    	return $this->hasMany('App\Repayment');
    }


    public function commitments(){
        return $this->hasMany('App\CustomerCommitment');
    }

    public function penalities(){
        return $this->hasMany('App\Penalty');
    }

    public function scopeUnpaid($query){
        return $query->where('loan_completed_on', NULL);
    }

    public function scopeActive($query){
        //return $query->where('loan_completed_on', NULL);
        return $query
            ->whereDate( 'loan_due_on', '>=', Carbon::today('Africa/Nairobi')->toDateString())
            ->where('loan_completed_on', NULL);
    }

    // public function scopeCompleted($query){
    //     return $query->where('loan_completed_on', '!=', NULL)
    //                     ->whereRaw('loan_completed_on <= loan_due_on');;
    // }

    public function scopeCompleted($query){
        return $query->where('loan_completed_on', '!=', NULL);
    }

    public function scopeDelayed($query){
        return $query->where('loan_completed_on', '!=', NULL)
            ->whereRaw('loan_completed_on > loan_due_on');
    }

    public function scopeLate($query){
        return $query
            ->whereDate('loan_due_on', '<', Carbon::today('Africa/Nairobi')->toDateString())
            ->where('loan_completed_on', NULL);
    }

    public function scopeDefaulted($query){
        return $query
            ->whereBetween('loan_due_on', [Carbon::today('Africa/Nairobi')->subDays(90)->toDateString(), Carbon::today('Africa/Nairobi')->subDays(31)->toDateString()])
            ->where('loan_completed_on', NULL);
    }


    public function scopeLost($query){
        return $query
            ->whereBetween('loan_due_on', [Carbon::today('Africa/Nairobi')->subDays(365)->toDateString(), Carbon::today('Africa/Nairobi')->subDays(91)->toDateString()])
            ->where('loan_completed_on', NULL);
    }


    //loans by ranking
    public function scopeGreen($query){
        return $query->where('customer_bee_ranking', '=', 'GREEN');
    }
    public function scopeBronze($query){
        return $query->where('customer_bee_ranking', '=', 'BRONZE');
    }

    public function scopeSilver($query){
        return $query->where('customer_bee_ranking', '=', 'SILVER');
    }

    public function scopeGold($query){
        return $query->where('customer_bee_ranking', '=', 'GOLD');
    }
}
