<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\CustomerSMS;
use App\CustomerCall;
use App\CustomerContact;
use App\Bee;
use Carbon\Carbon;
use DB;
use Response;
use App\Loan;

class AnalysisController extends Controller
{
    public function loans(){
        //$loans = Loan::all();
        //$loans = Loan::late()->whereNotIn('customer_id', [1,2,3])->get();
        $loans = Loan::whereNotIn('customer_id', [1,2,3])->orderBy('id', 'desc')->get();
        //$loans = Loan::whereNotIn('customer_id', [1,2,3])->whereMonth('created_at', '=', 6)->get();
        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('AGE', 'AMOUNT', 'DAYS LATE', 'PAYBILL', 'AIRTIME', 'P2P IN', 'P2P OUT', 'STATUS'));

        //Populate the data
        foreach ($loans as $loan) {

        	//get the late days
        	$date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
            $due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('d-m-Y') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
            }

            $status = ($days_late < 0) ? "good" : "bad";

            $payill = $loan->customer->smss()
            		->select('message_amount')
            		->where('message_transaction_type', 'paybill')
            		->orderBy('id', 'DESC')->take(10)->get()->median('message_amount');
            $airtime = $loan->customer->smss()
            		->select('message_amount')
            		->where('message_transaction_type', 'airtime')
            		->orderBy('id', 'DESC')->take(10)->get()->median('message_amount');
            $p2pin = $loan->customer->smss()
            		->select('message_amount')
            		->where('message_transaction_type', 'P2P')
            		->where('message_flow_type', 'in')
            		->orderBy('id', 'DESC')->take(10)->get()->median('message_amount');
           	$p2pout = $loan->customer->smss()
           			->select('message_amount')
            		->where('message_transaction_type', 'P2P')
            		->where('message_flow_type', 'out')
            		->orderBy('id', 'DESC')->take(10)->get()->median('message_amount');
        	/////////////////////////////////
        	$row = [
        		Carbon::parse($loan->customer->date_of_birth)->age,
        		(int)$loan->principle_disbursed,
        		(int)$days_late,
        		(int)$payill,
        		(int)$airtime,
        		(int)$p2pin,
        		(int)$p2pout,
                $status
        	];
            
            
            fputcsv($handle, $row);
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=loans-data.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }


    public function loansMonthlyData($month){
        try {
            // $loans = DB::table('customer_loans')
            //         ->whereMonth('created_at', $month)
            //         ->select('customer_id', 'principle_disbursed', 'total_amount_due')
            //         ->group('customer_id')
            //         ->get();
            $loans = Loan::select('customer_id', DB::raw('COUNT(customer_id) as count'))
                    ->whereMonth('created_at', $month)
                    ->groupBy('customer_id')
                    //->orderBy('count', 'desc')
                    ->get();

            dd($loans->toArray());
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function loansData(){
        try {
            //$loans = Loan::all();
        //$loans = Loan::late()->whereNotIn('customer_id', [1,2,3])->get();
        $loans = Loan::whereNotIn('customer_id', [1,2,3])
                ->whereDate('loan_due_on', '<', Carbon::today())
                ->orderBy('id', 'desc')->get();
        //$loans = Loan::whereNotIn('customer_id', [1,2,3])->whereMonth('created_at', '=', 6)->get();
        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('AGE', 'GENDER', 'ID', 'PHONE', 'LOANS', 'STATUS'));

        //Populate the data
        foreach ($loans as $loan) {
            if(!empty($loan->customer)){
            //get the late days
            $date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
            $due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('d-m-Y') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
            }

            $status = ($days_late < 0) ? 1 : 0; //One is good, 0 is bad

            $customer = $loan->customer;
            //gender
            $gender_data = isset($customer->gender) ? strtolower($customer->gender) : 0;
            if($gender_data == 'male'){
                $gender = 1; //Male
            }elseif ($gender_data == 'female') {
                $gender = 2; //Female
            }else{
                $gender = 0; //Unknown
            }
            //dd($customer);

            // $id1 = substr($customer->identification, 0, 2);
            // $id2 = substr($customer->identification, 2, 2);

            //Remove 2547 from the phone number
            $phone_number = substr($customer->phone_number, 4);

            // $phone1 = substr($phone_number, 0, 2);
            // $phone2 = substr($phone_number, 2, 2);

            $loans_count = Loan::where('customer_id', $loan->customer_id)
                ->where('id', '<', $loan->id)->select('created_at')->count();

            //Populate the data
            $row = [
                Carbon::parse(
                $loan->customer->date_of_birth)->age,
                $gender,
                $customer->identification,
                (int)$phone_number,
                (int)$loans_count,
                $status
            ];
            
            
            fputcsv($handle, $row);
            }//end if customer
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=loans-data.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
            
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
        }
    }

    public function test(){
    	// Create a collection of sample values.
		$values = collect([
		   100, 200, 300, 400, 120, 300, 245, 250, 254, 245, 267, 200, 125, 178, 145, 110, 100, 100
		]);
		// Calculate the median value.
		$valuesMedian = $values->median();
		dd($valuesMedian);
    }


    public function median(){
	    $key = null;
    }
	
}
