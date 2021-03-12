<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repayment;
use App\Loan;
use Carbon\Carbon;
use DB;
use Response;

class RepaymentsController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin|customer-care|finance|lender']);
    }

    public function index(){
    	return view('repayments');
    }

    public function search(Request $request){
        $query = trim($request['q']);

        if(empty($query)){
            $transactions = Repayment::orderBy('id', 'DESC')->limit(50)->get();
        }else{

            $transactions = Repayment::Where('mobile_wallet_transaction_id', 'LIKE', "%".$query."%")
                                 ->orWhere('transaction_source', 'LIKE', "%".$query."%")
                                 ->skip(0)
                                 ->take(50)
                                 //->select('id', 'email_address', 'phone_number')
                                 ->get();

        }

        if(isset($transactions)){
        	foreach ($transactions as $transaction) {
        		$transaction->transaction_date = Carbon::parse($transaction->transaction_date)->format('d-m-Y h:m:s');
                if($transaction->loan){
                    $transaction->link = route('customer', $transaction->loan->customer_id);
                }else{
                    $transaction->link = '';
                }
        	}
        }
        
        return response()->json($transactions);
    }

    public function dailyRepayments(Request $request){
    	$date = trim($request['q']);
    	$transactions = Repayment::where(DB::raw('date(created_at)'), Carbon::parse($date)->toDateString())
                        ->orderBy('id', 'DESC')->get();
        if(isset($transactions)){
        	foreach ($transactions as $transaction) {
        		$transaction->transaction_date = Carbon::parse($transaction->transaction_date)->format('d-m-Y h:m:s');
                //$loan = Loan::find($transaction->loan_id);
                if($transaction->loan){
                    $transaction->link = route('customer', $transaction->loan->customer_id);
                }else{
                    $transaction->link = '';
                }
                
        	}
            $response = [
                'transactions' => $transactions,
                'count' => number_format($transactions->count()),
                'total' => number_format($transactions->sum('amount_paid'))
            ];

        	return response()->json($response);
        }

        return response()->json([]);
    }



    public function repaymentsCSV(){
        $repayments = Repayment::has('loan')
                        ->whereDate('created_at', '>', "2018-09-31")
                        ->orderBy('id', 'DESC')->get();        

        $callback = function() use ($repayments){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('TX_CODE', 'SOURCE', 'ACCOUNT', 'LOAN ID', 'AMOUNT', 'DATE'));

        //Populate the data
        foreach ($repayments as $repayment) {
            $code = $repayment->mobile_wallet_transaction_id;
            $source = $repayment->transaction_source."\r";
            $account = $repayment->transaction_account."\r";
            $loan = $repayment->loan_id;
            $amount = $repayment->amount_paid;
            $date = Carbon::parse($repayment->created_at)->format('d-m-Y');
            
            fputcsv($handle, [$code, $source, $account, $loan, $amount, $date]);
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=repayments.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }
}
