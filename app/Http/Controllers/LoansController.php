<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use App\Loan;
use App\Customer;
use App\BeeHistory;
use App\Bee;
use Carbon\Carbon;
use DB;
use Storage;

class LoansController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin|finance|customer-care|lender']);
    }

    public function index(){
    	$loans = Loan::where(DB::raw('date(created_at)'), Carbon::today())->orderBy('id', 'DESC')->get();
    	$critical = [
            'today' => $loans->count(),
            'disbursed' => $loans->sum('principle_disbursed')
        ];

    	if(isset($loans)){
    		foreach ($loans as $key => $loan) {
    			$loan->disbursed_on = Carbon::parse($loan->loan_disbursed_on)->format('d-m-Y');
    			$loan->due_on = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
    		}
    	}
    	return view('loans')->with('loans', $loans)->with('critical', (object)$critical);
    }

    public function daily(Request $request){
        //get the loans
        if(!empty($request['start_date']) && !empty($request['end_date'])){
            $from_date = Carbon::parse($request['start_date'])->toDateString();
            $to_date = Carbon::parse($request['end_date'])->toDateString();
            
        }else{
            $from_date = Carbon::today()->toDateString();
            $to_date = Carbon::today()->toDateString(); 
        }

        $loans = Loan::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))
                    ->orderBy('id', 'DESC')
                    ->get();
        $late = Loan::late()
                        ->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))
                        ->select('principle_disbursed')
                        ->orderBy('id', 'DESC')->get();
        if(isset($loans)){
            
            foreach($loans as $loan){
                $loan->code = isset($loan->disbursement->loan_disbursement_merchant_code) ? $loan->disbursement->loan_disbursement_merchant_code : "";
                $loan->customer_name = isset($loan->customer->user->name) ? $loan->customer->user->name : "";
                $loan->phone = isset($loan->customer->phone_number) ? $loan->customer->phone_number : "";
                $loan->amount = $loan->principle_disbursed;
                $loan->disbursed_on = Carbon::parse($loan->loan_disbursed_on)->format('d-m-Y H:m:s');
                $loan->link = route('customer', $loan->customer_id);
            }

            $response = [
                'loans' => $loans,
                'critical' => [
                    'count' => number_format($loans->count()),
                    'disbursed' => number_format($loans->sum('principle_disbursed')),
                    'late_amount' => number_format($late->sum('principle_disbursed')),
                    'late_loans' => number_format($late->count())
                ]
            ];
            return response()->json($response);
        }

        return response()->json([]);
    }

    public function monthlyLoans(){
        $loans = Loan::select('*')
        ->orderBy('created_at', 'asc')
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
        });

        $months = array();
        $loans_count = array();
        $loans_total = array();
        $loans_avgs = array();

        foreach ($loans as $key => $loan) {
            $months[] = date("F", mktime(0, 0, 0, $key, 10));
            $loans_count[] = $loan->count();
            $loans_total[] = (int)$loan->sum('principle_disbursed');
            $loans_avgs[] = (int)$loan->average('principle_disbursed');
        }

        $monthly_data = [
            'months' => $months,
            'counts' => $loans_count,
            'totals' => $loans_total,
            'averages' => $loans_avgs
        ];

        return json_encode($monthly_data);
        //dd($monthly_data);
    }

    public function lateLoans(){
        $loans = Loan::late()->whereNotIn('customer_id', [1,2,3])->orderBy('id', 'desc')->get();
        if(isset($loans)){
            foreach($loans as $loan){
                $loan->date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
                $loan->due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
                $loan->days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::today('Africa/Nairobi'));
            }
        }

        return view('late_loans')->with('loans', $loans);
    }

    public function latePaidLoans(){
        $loans = Loan::delayed()->whereNotIn('customer_id', [1,2,3])->get();
        if(isset($loans)){
            foreach($loans as $loan){
                $loan->date_issued = Carbon::parse($loan->created_at)->format('d-m-Y');
                $loan->due_date = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
                $loan->days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::today('Africa/Nairobi'));
            }
        }

        return view('late_loans')->with('loans', $loans);
    }

    

    // public function beeAnalysis(){
    //     $loans = Loan::whereNotIn('customer_id', [1,2,3])->get();
    //     foreach ($loans as $key => $loan) {
    //         //get the bee request path
    //         if($loan->customer_bee_history_id){
    //             $bee_history = BeeHistory::find($loan->customer_bee_history_id);
    //             if(!empty($bee_history)){
    //                 //copy the file to analysis folder
    //                 $bee_file = $bee_history->bee_request_file_name;
    //                 $xml = Storage::disk('bee')->exists('/history/request/'.$bee_file);
    //                 if($xml){
    //                     //Storage::disk('bee')->copy('/history/request/'.$bee_file, 'analysis/'.$bee_file);
    //                 }
    //                 ////////////////////////////////////////////////////
    //             }
                
    //         }else{
    //             //check if customer has bee history
    //             //$bee_history = isset($loan->customer->bee) ? $loan->customer->bee->bee_request_file_name : "";
    //             $bee_history = Bee::where('customer_id', $loan->customer->id)
    //                             ->orderBy('id', 'DESC')
    //                             ->first();

    //             if(!empty($bee_history)){
    //                 //copy the file to analysis folder
    //                 $bee_file = $bee_history->bee_request_file_name;
    //                 $xml = Storage::disk('bee')->exists('/request/'.$bee_file);
    //                 if($xml){
    //                     $duplicate = Storage::disk('bee')->exists('/analysis/'.$bee_file);
    //                     if(!$duplicate){
    //                        //Storage::disk('bee')->copy('/request/'.$bee_file, 'analysis/'.$bee_file); 
    //                     }
                        
    //                 }
    //                 ////////////////////////////////////////////////////
    //             }
    //         }
    //     }
    // }



    public function beeAnalysis(){
        $customers = Customer::bronze()->whereNotIn('id', [1,2,3])->orderBy('id', 'DESC')->skip(4000)->take(1000)->get();
        foreach ($customers as $key => $customer) {
            //check if customer has bee history
                //$bee_history = isset($loan->customer->bee) ? $loan->customer->bee->bee_request_file_name : "";
                $bee_history = Bee::where('customer_id', $customer->id)
                                ->orderBy('id', 'DESC')
                                ->first();

                if(!empty($bee_history)){
                    //copy the file to analysis folder
                    $bee_file = $bee_history->bee_request_file_name;
                    $xml = Storage::disk('bee')->exists('/request/'.$bee_file);
                    if($xml){
                        $duplicate = Storage::disk('bee')->exists('/analysis/'.$bee_file);
                        if(!$duplicate){
                           Storage::disk('bee')->copy('/request/'.$bee_file, 'analysis/'.$bee_file); 
                        }
                        
                    }
                    ////////////////////////////////////////////////////
                }
        }
    }

    public function allLoans(){
        $loans = Loan::whereNotIn('customer_id', [1,2,3])->distinct('customer_id')->orderBy('id', 'desc')->get();
        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('CUSTOMER_ID', 'NAME', 'EMAIL', 'PHONE', 'ID NUMBER', 'GENDER', 'AGE', 'LOANS', 'RANKING', 'AMOUNT', 'TOTAL DUE', 'STATUS', 'ISSUED AT', 'DUE AT', 'COMPLETED AT', 'DAYS LATE'));

        //Populate the data
        foreach ($loans as $loan) {
            $id = $loan->customer_id;
            $name = $loan->customer->user->name;
            $email = $loan->customer->email_address;
            $phone = $loan->customer->phone_number."\r";
            $identification = $loan->customer->identification."\r";
            $gender = $loan->customer->gender;
            $age = Carbon::parse($loan->customer->date_of_birth)->age;
            $loans = $loan->customer->loans->count();
            //$ranking = $loan->customer->bee->customer_ranking;
            $amount = $loan->principle_disbursed;
            $total_due = $loan->total_amount_due;
            $status = $loan->loan_status;
            $date_issued = Carbon::parse($loan->created_at)->format('Y-m-d');
            $due_date = Carbon::parse($loan->loan_due_on)->format('Y-m-d');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('Y-m-d') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
               // $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::createFromFormat('Y-m-d', $report_date), false);
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
            
            fputcsv($handle, [$id, $name, $email, $phone, $identification, $gender, $age, $loans, $ranking, $amount, $total_due, $status, $date_issued, $due_date, $compeleted_at, $days_late]);
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



    public function loansCSV(){
        //$loans = Loan::late()->whereNotIn('customer_id', [1,2,3])->distinct('customer_id')->orderBy('id', 'desc')->get();
        $loans = Loan::whereNotIn('customer_id', [1,2,3])
                ->whereMonth('created_at', '>', 2)
                ->distinct('customer_id')
                ->orderBy('id', 'asc')
                ->get();

        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('CUSTOMER_ID', 'PHONE', 'ID NUMBER', 'GENDER', 'AGE', 'LOANS', 'RANKING', 'AMOUNT', 'TOTAL DUE', 'STATUS', 'ISSUED AT', 'DUE AT', 'COMPLETED AT', 'DAYS LATE', 'LAT', 'LON'));

        //Populate the data
        foreach ($loans as $loan) {
            $loans_count = Loan::where('customer_id', $loan->customer_id)
                ->where('id', '<', $loan->id)->select('created_at')->count();
            //->whereDate('created_at', '<=', Carbon::parse($loan->created_at)->toDateString())->select('created_at')->count();
            $id = $loan->customer_id;
            $name = $loan->customer->user->name;
            $email = $loan->customer->email_address;
            $phone = $loan->customer->phone_number."\r";
            $identification = $loan->customer->identification."\r";
            $gender = $loan->customer->gender;
            $age = Carbon::parse($loan->customer->date_of_birth)->age;
            $loans = $loans_count;
            //$ranking = $loan->customer->bee->customer_ranking;
            $amount = $loan->principle_disbursed;
            $total_due = $loan->total_amount_due;
            $status = $loan->loan_status;
            $date_issued = Carbon::parse($loan->created_at)->format('Y-m-d');
            $due_date = Carbon::parse($loan->loan_due_on)->format('Y-m-d');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('Y-m-d') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::today('Africa/Nairobi'), false) + 1;
               // $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::createFromFormat('Y-m-d', $report_date), false);
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
            
            fputcsv($handle, [$id, $phone, $identification, $gender, $age, $loans, $ranking, $amount, $total_due, $status, $date_issued, $due_date, $compeleted_at, $days_late, $loan->lat, $loan->lon]);
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





    public function questCSV(){
        //$loans = Loan::all();
        $dt = Carbon::now()->toDateString();
        //dd($dt);
        $loans = Loan::late()->whereNotIn('customer_id', [1,2,3])
                ->whereDate('date_marked_for_collection', '=', $dt)
                ->get();
        //$loans = Loan::whereNotIn('customer_id', [1,2,3])->whereMonth('created_at', '=', 6)->get();
        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('NAME', 'EMAIL', 'PHONE', 'ID NUMBER', 'GENDER', 'AGE', 'RANKING', 'AMOUNT', 'TOTAL DUE', 'ISSUED AT', 'DUE AT', 'DAYS LATE'));

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
            
            fputcsv($handle, [$name, $email, $phone, $identification, $gender, $age, $ranking, $amount, $total_due, $date_issued, $due_date, $days_late]);
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename='.$dt.'.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }



    //download loans by date range
    public function dateRangeLoansCSV(Request $request){
        //get the loans
        if(!empty($request['start_date']) && !empty($request['end_date'])){
            $from_date = Carbon::parse($request['start_date'])->toDateString();
            $to_date = Carbon::parse($request['end_date'])->toDateString();
            //query the loans
            
            $loans = Loan::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))
                ->distinct('customer_id')
                ->orderBy('id', 'asc')
                ->get();
        }else{
            $from_date = Carbon::today()->toDateString();
            $to_date = Carbon::today()->toDateString();
            $loans = Loan::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))
                ->distinct('customer_id')
                ->orderBy('id', 'asc')
                ->get();
        }
        

        if(!isset($loans)){
            return response()->json([]);
        }        

        $callback = function() use ($loans){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('CUSTOMER_ID', 'NAME', 'PHONE', 'ID NUMBER', 'GENDER', 'AGE', 'LOANS', 'RANKING', 'AMOUNT', 'TOTAL DUE', 'REPAID', 'STATUS', 'ISSUED AT', 'DUE AT', 'COMPLETED AT', 'DAYS LATE', 'LAT', 'LON'));

        //Populate the data
        foreach ($loans as $loan) {
            $loans_count = Loan::where('customer_id', $loan->customer_id)
                ->where('id', '<', $loan->id)->select('created_at')->count();
            //->whereDate('created_at', '<=', Carbon::parse($loan->created_at)->toDateString())->select('created_at')->count();
            $id = $loan->customer_id;
            $name = isset($loan->customer->user->name) ? $loan->customer->user->name : "";
            $email = isset($loan->customer->email_address) ? $loan->customer->email_address : "";
            $phone = isset($loan->customer->phone_number) ? $loan->customer->phone_number."\r" : "";
            $identification = isset($loan->customer->identification) ? $loan->customer->identification."\r" : "";
            $gender = isset($loan->customer->gender) ? $loan->customer->gender : "";
            $age = isset($loan->customer->date_of_birth) ? Carbon::parse($loan->customer->date_of_birth)->age : "";
            $loans = $loans_count;
            //$ranking = $loan->customer->bee->customer_ranking;
            $amount = $loan->principle_disbursed;
            $total_due = $loan->total_amount_due;
            $repaid = $loan->repayments->sum('amount_paid');
            $status = $loan->loan_status;
            $date_issued = Carbon::parse($loan->created_at)->format('Y-m-d');
            $due_date = Carbon::parse($loan->loan_due_on)->format('Y-m-d');
            $compeleted_at = ($loan->loan_completed_on != NULL) ? Carbon::parse($loan->loan_completed_on)->format('Y-m-d') : "";
            if($loan->loan_completed_on != NULL){
                $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::parse($loan->loan_completed_on), false);
            }else{
               $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::today('Africa/Nairobi'), false) + 1;
               // $days_late = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::createFromFormat('Y-m-d', $report_date), false);
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
            
            fputcsv($handle, [$id, $name, $phone, $identification, $gender, $age, $loans, $ranking, $amount, $total_due, $repaid, $status, $date_issued, $due_date, $compeleted_at, $days_late, $loan->lat, $loan->lon]);
        }
        fclose($handle);
    };
        $output_file_name = 'loans-'.Carbon::parse($from_date)->format('dmY').'-'.Carbon::parse($to_date)->format('dmY').'.csv';
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename='.$output_file_name
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }
}
