<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Expense;
use App\Disbursement;
use App\Orphan;
use App\Repayment;
use App\Customer;
use App\Loan;
use App\SMS;
use DB;
use Storage;
use Response;
use Illuminate\Http\File;
use Carbon\Carbon;
use App\Http\Controllers\MainController;

class ExpensesController extends Controller
{
    public function index(){
    	// $expenses = Expense::groupBy('transaction_type')
    	// 			->select('transaction_type', DB::raw('count(*) as total'))
    	// 			->pluck('transaction_type','total')->all();
    	// //dd($expenses);

    	// $saf_disbursement_fees = Disbursement::sum('loan_disbursement_engine_transaction_fee');
    	// $at_disbursement_fees = Disbursement::sum('loan_disbursement_merchant_fee');
    	// $repayments_fees = Repayment::sum('provider_transaction_fee');
    	// $orphans_fees = Orphan::sum('provider_transaction_fee');
    	// $sms_fees = SMS::sum('sms_cost');

    	// return response()->json([
    	// 	'saf_disbursement_fees' => $saf_disbursement_fees,
    	// 	'at_disbursement_fees' => $at_disbursement_fees,
    	// 	'repayments_fees' => $repayments_fees,
    	// 	'orphans_fees' => $orphans_fees,
    	// 	'sms_fees' => $sms_fees
    	// ]);
        return view('expenses');
		
    }

    public function expensesCSV(Request $request){
        $expenses = Expense::all();
        if(!$expenses->count()){
            return response()->json([]);
        }


        $callback = function() use ($expenses){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['SUPPLIER', 'REASON','ACCOUNT', 'AMOUNT', 'CREATED AT']);
            //Populate the data
            foreach ($expenses as $expense) {
                    $row = [
                    $expense->supplier,
                    $expense->transaction_type,
                    $expense->account,
                    $expense->amount,
                    $expense->created_at
                ];
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=expenses.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }


    public function disbursementsCSV(Request $request){
        $disbursements = Disbursement::all();
        if(!$disbursements->count()){
            return response()->json([]);
        }


        $callback = function() use ($disbursements){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['SUPPLIER', 'TXCODE', 'AMOUNT', 'SUPPLIER FEE', 'NOTIFICATION FEE', 'CREATED AT']);
            //Populate the data
            foreach ($disbursements as $disbursement) {
                    $row = [
                    $disbursement->loan_disbursement_engine_provider,
                    $disbursement->loan_disbursement_merchant_code,
                    $disbursement->loan_disbursement_engine_amount_sent,
                    $disbursement->loan_disbursement_engine_transaction_fee,
                    $disbursement->loan_disbursement_merchant_fee,
                    $disbursement->created_at
                ];
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=disbursements.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
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
            $disbursement = [];
            $at = [];
            $sms = [];
            $repayment = [];

            foreach ($dates as $key => $dt) {
                $days[] = Carbon::parse($dt)->format('d/m');
                $disbursement[] = Disbursement::whereDate('created_at', $dt)
                            ->sum('loan_disbursement_engine_transaction_fee');
                $at[] = Disbursement::whereDate('created_at', $dt)
                            ->sum('loan_disbursement_merchant_fee');
                //combine repayments and orphaned
                $repayments_fees = Repayment::whereDate('created_at', $dt)->sum('provider_transaction_fee');
                $orphaned_fees = Orphan::whereDate('created_at', $dt)->sum('provider_transaction_fee');
                $repayment[] = number_format(($repayments_fees + $orphaned_fees), 2);
                $sms[] = number_format(SMS::whereDate('created_at', $dt)->sum('sms_cost'), 2);
            }

            //get totalnumber
            $total = [
                'disbursement' => number_format(array_sum($disbursement), 2),
                'repayment' => number_format(array_sum($repayment), 2),
                'at' => number_format(array_sum($at), 2),
                'sms' => number_format(array_sum($sms), 2)
            ];

            return response()->json([
                'days' => $days,
                'disbursement' => $disbursement,
                'at' => $at,
                'repayment' => $repayment,
                'sms' => $sms,
                'total' => $total
            ]);
    }


    public function beeFiles(){
        $filename = "15311092591451.xml";
        $xml = Storage::disk('bee')->exists('/history/request/'.$filename);
        Storage::disk('bee')->copy('/history/request/'.$filename, 'analysis/'.$filename);
        //dd($xml);
    }
}
