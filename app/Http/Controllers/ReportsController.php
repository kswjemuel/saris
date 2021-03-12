<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Loan;
use App\Repayment;
use App\Penalty;
use App\Customer;
use App\CreditHistory;
use App\CustomerSMS;
use DB;
use Response;
use App\Http\Controllers\MainController;

class ReportsController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin|finance|lender|customer-care']);
    }

    public function index(){
    	return view('reports');
    }


    public function customerCI(){
        
        //$list = CustomerSMS::select('customer_id', 'message_flow_type', 'message_amount', 'message_addressee_type', 'message_transaction_type')->get()->toArray();
        // DB::table('customer_smss')->select('customer_id', 'message_flow_type', 'message_amount', 'message_addressee_type', 'message_transaction_type')
        //     ->orderBy('id', 'ASC')
        //     ->chunk(100, function($smss){
        //     dd($smss->toArray());
        // });

        //dd($page);
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=CUSTOMER_SMSs.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];

        //$list = Customer::select('id', 'date_of_birth', 'gender')->get()->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));
        $callback = function() use ($list){
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) { 
                fputcsv($FH, $row);
            }
            fclose($FH);
        };
        return Response::stream($callback, 200, $headers);
    }

    //BETTER CSV
    public function collectorCSV(){
        $loans = Loan::where('date_marked_for_collection', '!=', NULL)->get();

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=late_loans.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];



        $callback = function() use ($loans){
            $FH = fopen('php://output', 'w');
            fputcsv($FH, ['NAME', 'EMAIL', 'PHONE', 'AMOUNT', 'DATE ISSUED', 'DUE DATE', 'DAYS LATE', 'COLLECTION DATE']);
            foreach ($loans as $loan) {
                $row = [
                    'name' => $loan->customer->user->name,
                    'email' => $loan->customer->email_address,
                    'phone' => '@'.$loan->customer->phone_number,
                    'amount' => $loan->total_amount_due,
                    'created_at' => Carbon::parse($loan->created_at)->format('d-m-Y'),
                    'due_at' => Carbon::parse($loan->loan_due_on)->format('d-m-Y'),
                    'days_late' => Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::today()),
                    'collection_date' => Carbon::parse($loan->date_marked_for_collection)->format('d-m-Y')
                ];
                fputcsv($FH, $row);
            }
            fclose($FH);
        };
        return Response::stream($callback, 200, $headers);
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

        $repayments = $this->monthlyRepayments();
        $diff = (count($months) - count($repayments['amounts']));
        if($diff > 0){
        	for($i=0; $i<=$diff;$i++){
        		array_unshift($repayments['amounts'], 0);
        	}
        }

        //dd($repayments['amounts']);

        $monthly_data = [
            'months' => $months,
            'counts' => $loans_count,
            'totals' => $loans_total,
            'averages' => $loans_avgs,
            'repayments' => $repayments['amounts']
        ];

        return json_encode($monthly_data);
        //dd($monthly_data);
    }

    public function monthlyCustomers(){
        $customers = Customer::select('id', 'created_at')
        ->orderBy('created_at', 'asc')
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
        });

        $months = [];
        $counts = [];
        
        if(isset($customers)){
        	foreach ($customers as $key => $customer) {
	            $months[] = date("F", mktime(0, 0, 0, $key, 10));
	            $counts[] = $customer->count();
	        }
        }

        $response = [
        	'months' => $months,
        	'customers' => $counts
        ];

        return response()->json($response);
    }

    public function monthlyRepayments(){
        $repayments = Repayment::select('id', 'amount_paid', 'created_at')
        ->orderBy('created_at', 'asc')
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
        });

        $months = [];
        $counts = [];
        $amounts = [];
        
        if(isset($repayments)){
        	foreach ($repayments as $key => $repayment) {
	            $months[] = date("F", mktime(0, 0, 0, $key, 10));
	            $counts[] = $repayment->count();
	            $amounts[] = $repayment->sum('amount_paid');
	        }
        }

        $response = [
        	'months' => $months,
        	'amounts' => $amounts
        ];

        return $response;
    }

    public function dateRangeData(Request $request){
            if(!isset($request['start_date']) || empty($request['start_date'])){
                $from = Carbon::yesterday()->subDays(30);
                $to = Carbon::yesterday();
            }else{
                $from = Carbon::parse($request['start_date']);
                $to = Carbon::parse($request['end_date']);
            }
            //$from = Carbon::createFromFormat('d/m/Y', "10/4/2018");
            
            //$to = Carbon::createFromFormat('d/m/Y', "10/5/2018");
            //dd($to);
            $dates = MainController::generateDateRange($to, $from);
            
            $days = [];
            $repayments = [];
            $loans = [];
            $penalties = [];
            $customers = [];
            foreach ($dates as $key => $dt) {
                $days[] = Carbon::parse($dt)->format('d/m');
                $loans[] = Loan::whereDate('created_at', $dt)->sum('principle_disbursed');
                $penalties[] = Penalty::whereDate('created_at', $dt)->sum('penalty_amount');
                $repayments[] = Repayment::has('loan')->whereDate('created_at', $dt)->sum('amount_paid');
                $customers[] = Customer::whereDate('created_at', $dt)->count();
            }

            //get totalnumber
            $totals = [
                'loans' => number_format(array_sum($loans)),
                'repayments' => number_format(array_sum($repayments)),
                'customers' => number_format(array_sum($customers)),
                'penalties' => number_format(array_sum($penalties))
            ];

            return response()->json([
                'days' => $days,
                'loans' => $loans,
                'repayments' => $repayments,
                'penalties' => $penalties,
                'customers' => $customers,
                'total' => $totals
            ]);
    }



    //Monthly graph data
    public function monthlyData(){
        $from = Carbon::today()->subDays(150);
        $to = Carbon::today();
        $months = MainController::listMonths($from, $to);
        //print_r($months);
        $loans = [];
        $repayments = [];
        $dates = [];
        foreach ($months as $key => $month) {
            $month_array = explode('-', $month);
            //fetch data based on year and month
            $dates[] = $month;
            $loans[] = (int)Loan::whereMonth('created_at', $month_array[0])->whereYear('created_at', $month_array[1])->sum('principle_disbursed');
            $repayments[] = (int)Repayment::whereMonth('created_at', $month_array[0])->whereYear('created_at', $month_array[1])->sum('amount_paid');
        }

        return response()->json([
            'months' => $dates,
            'loans' => $loans,
            'repayments' => $repayments 
        ]);
    }


    public function portfolioMonitoring(Request $request){

        $year = $request['y'];
        //$month = $request['m'];
        if(!$request->exists('m') || empty($request['m'])){
            $month = Carbon::today()->format('m');
        }else{
            $month = $request['m'];
        }

        //$month_name = Carbon::parse($month)->format('M');
        $month_name = date("F", mktime(0, 0, 0, $month, 10));
        //dd($month_name);
        //$report_date = "2018-".$month."-30";
        //$report_date = Carbon::today()->subDays(45)->format('Y-m-d');
        $report_date = Carbon::today('Africa/Nairobi')->format('Y-m-d');

        $newloans = Loan::whereMonth('created_at', $month)->whereHas('customer', function ($query) {
            $query->has('loans', '=', 1);
        })->get();

        $repeatloans = Loan::whereMonth('created_at', $month)->whereHas('customer', function ($query) {
            $query->has('loans', '>', 1);
        })->get();

        //dd($loans);

        $loans = Loan::whereMonth('created_at', $month)->with('repayments')->get();
        $paid_back = 0;
        foreach ($loans as $key => $loan) {
            $repaid = $loan->repayments->sum('amount_paid');
            $loan->paid_back = $repaid;
            $paid_back = $paid_back + $repaid;
        }

        //dd($paid_back);

        $loans = Loan::whereMonth('created_at', $month)->get();
        $customers = Customer::whereMonth('created_at', $month)->count();
        $silvers = Customer::silver()->whereMonth('created_at', $month)->count();

        $approved = Customer::approved()->whereMonth('created_at', $month)->count();
        $tz = 'Africa/Nairobi';
        $dpd1 = Loan::unpaid()->late()
        ->whereMonth('created_at', $month)
        ->where('loan_due_on' , '>', Carbon::today('Africa/Nairobi')->subdays(31)->toDateString())
        ->get();

        $dpd2 = Loan::unpaid()->late()
        ->whereMonth('created_at', $month)
        ->whereDate('loan_due_on' , '<', Carbon::today('Africa/Nairobi')->subdays(31)->toDateString())
        ->get();
        //dd($dpd2);

        // foreach ($dpd1 as $key => $loan) {
        //     $dt1 = Carbon::createFromFormat('Y-m-d', $report_date, $tz);
        //     $date_diff = $dt1->diffInDays(Carbon::parse($loan->loan_due_on));
        //     echo Carbon::parse($loan->loan_due_on)->format('Y-m-d').' : '.$date_diff.'<br>';
        // }
        // dd($dpd2);
        $late_unpaid = Loan::late()
        ->whereMonth('created_at', $month)->select('id')->count();
        //dd($late_all);
        $late_paid = Loan::delayed()
            ->whereMonth('created_at', $month)->select('id')->count();

        //dd($late_paid);
        if($late_unpaid > 0){
            $collection_rate = ($late_paid / ($late_paid + $late_unpaid)) * 100;
        }else{
            $collection_rate = 0;
        }

        $all_loans =  $loans->count();
        $new_loans = $newloans->count();
        $repeat_loans = $repeatloans->count();
        
        if($all_loans > 0 && $repeat_loans > 0){
            $repeat_percentage = ($repeat_loans/$all_loans) * 100;
        }else{
            $repeat_percentage = 0;
        }

        if($dpd1->count() > 1){
           $dpd_0_30_percentage = ($dpd1->count()/$all_loans) * 100;
           $dpd1_amount = $dpd1->sum('principle_disbursed');
           $dpd1_percent = ($dpd1->sum('principle_disbursed')/$loans->sum('principle_disbursed'))*100;
        }else{
            $dpd_0_30_percentage = 0;
        }


        if($dpd2->count() > 1){
           $dpd_30plus_percentage = ($dpd2->count()/$all_loans) * 100;
           $dpd2_amount = $dpd2->sum('principle_disbursed');
           $dpd2_percent = ($dpd2->sum('principle_disbursed')/$loans->sum('principle_disbursed'))*100;
        }else{
            $dpd_30plus_percentage = 0;
        }

        $total_repayments = Repayment::whereMonth('created_at', $month)->sum('amount_paid');
        $disbursed = $loans->sum('principle_disbursed');
        $investment = $disbursed - ($total_repayments - $paid_back);

        //$investment = $disbursed - $paid_back;
        
        

        $report = [
            'date_captured' => $month_name,
            'customers' => $customers,
            'acceptance_rate' => ($customers > 0) ? number_format((($approved/$customers) * 100), 2) : 0,
            'disbursed' => $loans->sum('principle_disbursed'),
            'loans_count' =>$all_loans,
            'newloans' => $new_loans,
            'repeatloans' => $repeat_loans,
            'repeat_percentage' => $repeat_percentage,
            'avg_amount' => $loans->avg('principle_disbursed'),
            'dpd_0_30' => $dpd1->count(),
            'dpd1_amount' => isset($dpd1_amount) ? $dpd1_amount : 0,
            'dpd1_percent' => isset($dpd1_percent) ? $dpd1_percent : 0,
            'dpd2_amount' => isset($dpd2_amount) ? $dpd2_amount : 0,
            'dpd2_percent' => isset($dpd2_percent) ? $dpd2_percent : 0,
            'dpd_0_30_percentage' => $dpd_0_30_percentage,
            'dpd_30_plus' => $dpd2->count(),
            'dpd_30plus_percentage' => $dpd_30plus_percentage,
            'late_unpaid' => $late_unpaid,
            'late_paid' => $late_paid,
            'collection_rate' => $collection_rate,
            'paid_back' => $paid_back,
            'income' => ($paid_back - $disbursed),
            'investment' => $investment
        ];
        return view('portfolio')->with('report', (object)$report);
    }

    

    
}
