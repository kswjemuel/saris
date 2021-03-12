<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Customer extends Model
{
    protected static function boot(){
        parent::boot();

        static::addGlobalScope('deleted', function (Builder $builder) {
            $builder->whereYear('last_seen', '>', '2018');
        });
    }

    public function user(){
        return $this->belongsTo('App\ShikaUser');
    }

    //customer bee realtionship
    public function bee(){
    	return $this->hasOne('App\Bee');
    }

    public function bees(){
        return $this->hasMany('App\BeeHistory', 'customer_id')
                    ->where('customer_bee_history.rejection_rules_status', '=', 'Approve');
    }


    public function loans(){
    	return $this->hasMany('App\Loan');
    }

    public function penalties(){
        return $this->hasManyThrough('App\Penalty', 'App\Loan');
    }

    public function smss(){
        return $this->hasMany('App\CustomerSMS', 'customer_id');
    }

    public function contacts(){
        return $this->hasMany('App\CustomerContact', 'customer_id');
    }

    public function repayments(){
    	return $this->hasManyThrough('App\Repayment', 'App\Loan');
    }

    public function overpayments(){
        return $this->hasManyThrough('App\Overpayment', 'App\Loan');
    }

    public function statements(){
    	return $this->hasMany('App\Statement')->orderBy('id', 'DESC');
    }

    //customer status
    public function customer_status(){
    	$active_loans = $this->loans()->where('loan_completed_on', NULL)->count();
    	if($active_loans){
    		return 'active';
    	}

    	return 'inactive';
    }

    public function scopeRecent($query){
        return $query->whereYear('last_seen', '>', '2018');
    }

    public function scopeActive($query){
        return $query->has( 'loans' );
            //->whereDate( 'created_at', '>', Carbon::today()->subDays(60));
            //->where('completed_at', NULL);
    }

    public function scopeInactive($query){
        return $query->doesnthave( 'loans' );
            //->whereDate( 'created_at', '>', Carbon::today()->subDays(60));
            //->where('completed_at', NULL);
    }

    public function scopeNobee($query){
        // return $query->doesnthave('bee');
        return $query->where('customer_ranking', NULL)->orWhere('customer_ranking', '');
    }

    public function scopeApproved($query){
        // return $query->whereHas('bee', function($bee){
        //     $bee->approved();
        // });
        return $query->where('customer_approval_status', 'Approve');
    }

    public function scopeDeclined($query){
        // return $query->whereHas('bee', function($bee){
        //     $bee->declined();
        // });
        return $query->where('customer_approval_status', 'Decline');
    }

    public function scopeToday($query){
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeGreen($query){
        // return $query->whereHas('bee', function($bee){
        //     $bee->green();
        // });
        return $query->where('customer_ranking', 'GREEN');
    }

    public function scopeBronze($query){
        // return $query->whereHas('bee', function($bee){
        //     $bee->bronze();
        // });
        return $query->where('customer_ranking', 'BRONZE');
    }

    public function scopeSilver($query){
        // return $query->whereHas('bee', function($bee){
        //     $bee->silver();
        // });
        return $query->where('customer_ranking', 'SILVER');
    }

    public function scopeGold($query){
        return $query->whereHas('bee', function($bee){
            $bee->gold();
        });
    }

    public function scopeBlocked($query){
        //return $query->user()->where('user_account_block_status', TRUE);
        return $query->whereHas('user', function($user){
            $user->where('user_account_block_status', TRUE);
        });
    }



    // WALLET
    public function wallet(){
        return $this->hasOne('App\Wallet');
    }
    public function transactions(){
        return $this->hasMany('App\WalletTransaction')->orderBy('id', 'DESC');
    }
}
