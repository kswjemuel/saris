<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use App\Penalty;

class PenaltiesController extends Controller
{
    //

    public function penaltiesCSV(Request $request){
    	$penalties = Penalty::all();
    	if(!$penalties->count()){
    		return response()->json([]);
    	}


    	$callback = function() use ($penalties){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['LOAN', 'AMOUNT PENALIZED ON', 'PERCENTAGE', 'PENALTY AMOUNT', 'CREATED AT']);
            //Populate the data
            foreach ($penalties as $penalty) {
                    $row = [
                    $penalty->loan_id,
                    $penalty->amount_penalized_on,
                    $penalty->penalty_percentage_of_amount_penalized,
                    $penalty->penalty_amount,
                    $penalty->created_at
                ];
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=penalties.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }
}
