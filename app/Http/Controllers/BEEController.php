<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Storage;
use DB;
use Carbon\Carbon;
use App\Loan;
use App\BeeHistory;

class BEEController extends Controller
{
    public function loans(){
        //$loans = Loan::all();
        //$loans = Loan::late()->whereNotIn('customer_id', [1,2,3])->get();
        $loans = Loan::whereNotIn('customer_id', [1,2,3])
        		->where('customer_bee_history_id', '>', 0)
        		->orderBy('id', 'desc')->get();
        //$loans = Loan::whereNotIn('customer_id', [1,2,3])->whereMonth('created_at', '=', 6)->get();
        if(!count($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('CUSTOMER_ID', 'NAME', 'EMAIL', 'PHONE', 'ID NUMBER', 'GENDER', 'AGE', 'RANKING', 'AMOUNT', 'TOTAL DUE', 'STATUS', 'ISSUED AT', 'DUE AT', 'COMPLETED AT', 'DAYS LATE', 'BEE FILE', 'BEE REQUEST'));

        //Populate the data
        foreach ($loans as $loan) {
        	//check if the request file exists
        	// $xml = Storage::disk('bee')->exists('/history/request/'.$bee_file);
        	// if($xml){
        	// }
        	
        	if(BeeHistory::where('id', $loan->customer_bee_history_id)->exists()){
        		$bee_history = BeeHistory::find($loan->customer_bee_history_id);
        		$xml = Storage::disk('bee')->exists('/history/request/'.$bee_history->bee_request_file_name);
        		if($xml){
        			//////////////////////////////////////////////////////////////
        			$id = $loan->customer_id;
		            $name = $loan->customer->user->name;
		            $email = $loan->customer->email_address;
		            $phone = $loan->customer->phone_number."\r";
		            $identification = $loan->customer->identification."\r";
		            $gender = $loan->customer->gender;
		            $age = Carbon::parse($loan->customer->date_of_birth)->age;
		            $ranking = $loan->customer->bee->customer_ranking;
		            $amount = $loan->principle_disbursed;
		            $total_due = $loan->total_amount_due;
		            $status = $loan->loan_status;
		            $date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
		            $due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
		            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('d-m-Y') : "";
		            if($loan->loan_completed_on != NULL){
		                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
		            }else{
		               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
		            }

		            $bee_request = isset($bee_history->bee_request_file_name) ? $bee_history->bee_request_file_name : "";
                	$bee_file = isset($bee_history->bee_request_file_name) ? $bee_history->bee_request_file_name : "";

                	fputcsv($handle, [$id, $name, $email, $phone, $identification, $gender, $age, $ranking, $amount, $total_due, $status, $date_issued, $due_date, $compeleted_at, $days_late, $bee_file, $bee_request]);

        			//////////////////////////////////////////////////////////////////////////
        		}//end if xml
        		
        	}//end if BeeHistory exists
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=loans.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }











    public function beeRequests(){
        //$loans = Loan::all();
        //$loans = Loan::late()->whereNotIn('customer_id', [1,2,3])->get();
        $loans = Loan::whereNotIn('customer_id', [1,2,3])
        		->where('customer_bee_history_id', '>', 0)
        		->orderBy('id', 'desc')->get();
        //$loans = Loan::whereNotIn('customer_id', [1,2,3])->whereMonth('created_at', '=', 6)->get();
        if(!count($loans)){
            return response()->json([]);
        }

        //move the files
        foreach ($loans as $loan) {
        	
        	if(BeeHistory::where('id', $loan->customer_bee_history_id)->exists()){
        		$bee_history = BeeHistory::find($loan->customer_bee_history_id);
        		$xml = Storage::disk('bee')->exists('/history/request/'.$bee_history->bee_request_file_name);

        		if($xml){
        			$bee_file = $bee_history->bee_request_file_name;
                    $duplicate = Storage::disk('bee')->exists('/analysis/'.$bee_file);
                    if(!$duplicate){
                       Storage::disk('bee')->copy('/history/request/'.$bee_file, 'analysis/'.$bee_file); 
                    }
                    
                }//end if xml
        		
        	}//end if BeeHistory exists
        }//end foreach
    }

    public function slipcodes(){
        $slipcodes = DB::table('customer_bee')
            ->select('customer_failed_rule')
            ->groupBy('customer_failed_rule')
            ->get();
        return response()->json($slipcodes);
    }


}
