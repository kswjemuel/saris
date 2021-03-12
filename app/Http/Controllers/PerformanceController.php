<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Loan;
use App\Repayment;
use App\Penalty;
use App\Customer;
use App\CreditHistory;
use App\CustomerSMS;
use DB;
use Response;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
	public function __construct(){
        $this->middleware(['role:admin|finance|lender|customer-care']);
    }

    public function monthly(Request $request){

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

        $month_repayments = Repayment::with('loan')->whereMonth('created_at', $month)->whereHas('loan', function($q) use($month) {
            $q->whereMonth('created_at', $month);
        })->sum('amount_paid');

        $closed_contracts = Loan::whereMonth('created_at', $month)->whereMonth('loan_completed_on', $month)->sum('principle_disbursed');

        //dd($closed_contracts);

        $disbursed = $loans->sum('principle_disbursed');
        //$investment = $disbursed - ($total_repayments - $paid_back);

        $investment = $total_repayments - $month_repayments;
        
        

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
            'investment' => $investment,
            'month_repayments' => $month_repayments
        ];
        return view('performance')->with('report', (object)$report);
    }
}
