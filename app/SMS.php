<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SMS extends Model
{
    protected $table = 'smss';

    protected static function boot(){
        parent::boot();

        static::addGlobalScope('deleted', function (Builder $builder) {
            $builder->whereYear('created_at', '>', "2018");
        });
    }
}
