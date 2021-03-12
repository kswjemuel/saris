<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class MainController extends Controller
{
    public static function generateDateRange(Carbon $start_date, Carbon $end_date){
        $dates = [];
        for($date = $start_date; $date->gte($end_date); $date->subDay()) {
            $dates[] = $date->format('Y-m-d');

        }
        return array_reverse($dates);

    }



    public static function listMonths(Carbon $start_date, Carbon $end_date){
        $start = $start_date->startOfMonth();
	    $end   = $end_date->startOfMonth();
	    $months = [];
	    do{
	        //$months[$start->format('m-Y')] = $start->format('F Y');
	        $months[] = $start->format('m-Y');

	    } while ($start->addMonth() <= $end);

	    return $months;
	}
}
