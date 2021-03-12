<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Orphan extends Model
{
    protected $table = 'orphaned_repayments';

    protected static function boot(){
        parent::boot();

        static::addGlobalScope('deleted', function (Builder $builder) {
            $builder->whereYear('created_at', '>', "2018");
        });
    }
}
