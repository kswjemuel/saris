<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnclaimedTransaction extends Model
{
    protected $table = 'orphaned_repayments';
}
