<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use App\Loan;
use App\Customer;
use App\CustomerApp;
use App\CustomerContact;
use App\BeeHistory;
use App\Threatmark;
use App\Bee;
use Carbon\Carbon;
use DB;

class DataController extends Controller
{
        public function loans(){
        $loans = Loan::whereNotIn('customer_id', [1,2,3])->distinct('customer_id')->orderBy('id', 'desc')->get();
        if(!isset($loans)){
            return response()->json([]);
        }      

        $callback = function() use ($loans){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('CUSTOMER_ID', 'GENDER', 'AGE', 'LOANS', 'RANKING', 'AMOUNT', 'STATUS', 'ISSUED AT', 'DUE AT', 'COMPLETED AT', 'DAYS LATE'));

        //Populate the data
        foreach ($loans as $loan) {
        	/////////////////////////check if customer isset///////////
        	if(!empty($loan->customer)){
            $id = $loan->customer_id;
            $gender = $loan->customer->gender;
            $age = Carbon::parse($loan->customer->date_of_birth)->age;
            $loans = $loan->customer->loans->count();
            //$ranking = $loan->customer->bee->customer_ranking;
            $amount = $loan->principle_disbursed;
            $status = $loan->loan_status;
            $date_issued = Carbon::parse($loan->created_at)->format('Y-m-d');
            $due_date = Carbon::parse($loan->loan_due_on)->format('Y-m-d');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('Y-m-d') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
            }

            //get the bee request path
            if($loan->customer_bee_history_id){
                $bee_history = BeeHistory::find($loan->customer_bee_history_id);
                $bee_request = isset($bee_history->bee_request_file_name) ? $bee_history->bee_request_file_name : "";
                $bee_file = isset($bee_history->bee_request_file_name) ? $bee_history->bee_request_file_name : "";
                $ranking = isset($bee_history->customer_ranking) ? $bee_history->customer_ranking : "";
            }else{
                //check if customer has bee history
                $bee_history = isset($loan->customer->bee) ? $loan->customer->bee->bee_request_file_name : "";
                $bee_history = BeeHistory::where('customer_id', $loan->customer->id)
                                ->orderBy('id', 'DESC')
                                ->first();
                $bee_request = isset($loan->customer->bee) ? $loan->customer->bee->bee_request_file_name : "";
                $bee_file = "NONE";
                $ranking = "UNKNOWN";
            }
            
            fputcsv($handle, [$id, $gender, $age, $loans, $ranking, $amount, $status, $date_issued, $due_date, $compeleted_at, $days_late]);
        }
            ////////////////////////////
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


    public function apps(){
    	$apps = CustomerApp::limit(200)->get();
    	dd($apps->toArray());
    }

    public function contacts(){
    	//$data = CustomerContact::whereNotIn('customer_id', [1,2,3])->limit(200)->get();
        //$data = CustomerContact::whereNotIn('customer_id', [1,2,3])->chunk(200)->get();
        $data = DB::table('customer_phone_contacts')->select('id')->count();
        dd($data);
        $data = DB::table('customer_phone_contacts')->orderBy('id')->chunk(1000, function ($contacts) {
            foreach ($contacts as $contact) {
                //
            }
        });
    	dd($data->count());
    }



    public function threatmark(){
        $results = Threatmark::all();
        if(!isset($results)){
            return response()->json([]);
        }      

        $callback = function() use ($results){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('CUSTOMER_ID', 'TYPE', 'VALUE', 'DATE'));

        //Populate the data
        foreach ($results as $result) {            
            fputcsv($handle, [
                $result->customer_id,
                $result->cookie_type,
                $result->cookie_value,
                $result->created_at
            ]);
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=threatmark-cookies.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }
}
