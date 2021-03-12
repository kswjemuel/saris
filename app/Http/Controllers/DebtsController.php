<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Loan;
use Carbon\Carbon;
use Response;
use DB;

class DebtsController extends Controller
{
    public function index(){
    	//get the loans
    	$loans = Loan::late()->whereNotIn('customer_id', [1,2,3])
    				->whereDate('date_marked_for_collection', '=', Carbon::yesterday()->toDateString())
    				->get();
        if(isset($loans)){
            foreach($loans as $loan){
                $loan->date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
                $loan->due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
                $loan->days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now());
                $loan->collection_date = Carbon::parse($loan->date_marked_for_collection)->format('d-m-Y');
            }
        }

    	return view('debt-collection')->with('loans', $loans);
    }


    public function collectorJSON(Request $request){
    	//get the loans
    	if(!empty($request['start_date']) && !empty($request['end_date'])){
    		$from_date = Carbon::parse($request['start_date'])->toDateString();
    		$to_date = Carbon::parse($request['end_date'])->toDateString();
    		//query the loans
    		$loans = Loan::whereNotIn('customer_id', [1,2,3])
    				->whereBetween(DB::raw('DATE(date_marked_for_collection)'), array($from_date, $to_date))
    				//->whereDate('date_marked_for_collection', '=', $date)
    				->get();
    	}else{
    		$date = Carbon::today()->toDateString();
    		$loans = Loan::whereNotIn('customer_id', [1,2,3])
    				->whereDate('date_marked_for_collection', '=', $date)
    				->get();
    	}
    	// $loans = Loan::late()->whereNotIn('customer_id', [1,2,3])
    	// 			->whereDate('date_marked_for_collection', '=', $date)
    	// 			->get();
    	$list = [];
        if(isset($loans)){
            foreach($loans as $key => $loan){
            	$list[$key]['name'] = $loan->customer->user->name;
            	$list[$key]['email'] = $loan->customer->email_address;
            	$list[$key]['phone'] = $loan->customer->phone_number;
            	$list[$key]['identification'] = $loan->customer->identification;
            	$list[$key]['gender'] = isset($loan->customer->gender) ? $loan->customer->gender : "";
            	$list[$key]['age'] = Carbon::parse($loan->customer->date_of_birth)->age;
            	//$list[$key]['ranking'] = $loan->customer->bee->customer_ranking;
            	$list[$key]['disbursed'] = $loan->principle_disbursed;
            	$list[$key]['due'] = $loan->total_amount_due;
            	$list[$key]['created_at'] = Carbon::parse($loan->created_at)->format('d-m-Y');
            	$list[$key]['due_at'] = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
            	$list[$key]['days_late'] = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
                $list[$key]['status'] = $loan->loan_status;
            }
        }

        return response()->json((object)$list);
    }




    public function collectorCSV(Request $request){
        if(!empty($request['start_date']) && !empty($request['end_date'])){
    		$from_date = Carbon::parse($request['start_date'])->toDateString();
    		$to_date = Carbon::parse($request['end_date'])->toDateString();
    		$date = $from_date."-to-".$to_date;
    		//query the loans
    		$loans = Loan::whereNotIn('customer_id', [1,2,3])
    				->whereBetween(DB::raw('DATE(date_marked_for_collection)'), array($from_date, $to_date))
    				//->whereDate('date_marked_for_collection', '=', $date)
    				->get();
    	}else{
    		$date = Carbon::today()->toDateString();
    		$loans = Loan::whereNotIn('customer_id', [1,2,3])
    				->whereDate('date_marked_for_collection', '=', $date)
    				->get();
    	}

        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('NAME', 'EMAIL', 'PHONE', 'ID NUMBER', 'GENDER', 'AGE', 'RANKING', 'AMOUNT', 'TOTAL DUE', 'ISSUED AT', 'DUE AT', 'DAYS LATE', 'STATUS', 'COMPLETED DATE'));

        //Populate the data
        foreach ($loans as $loan) {
            $name = $loan->customer->user->name;
            $email = $loan->customer->email_address;
            $phone = $loan->customer->phone_number."\r";
            $identification = $loan->customer->identification."\r";
            $gender = $loan->customer->gender;
            $age = Carbon::parse($loan->customer->date_of_birth)->age;
            $ranking = $loan->customer->bee->customer_ranking;
            $amount = $loan->principle_disbursed;
            $total_due = $loan->total_amount_due;
            $date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
            $due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('d-m-Y') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false); 
            }

            $status = $loan->loan_status;
            $completed_on = Carbon::parse($loan->loan_completed_on)->format('d-m-Y');
            
            fputcsv($handle, [$name, $email, $phone, $identification, $gender, $age, $ranking, $amount, $total_due, $date_issued, $due_date, $days_late, $status, $compeleted_at]);
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename='.$date.'.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }
}
