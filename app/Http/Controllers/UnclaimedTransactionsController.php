<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UnclaimedTransaction;
use Carbon\Carbon;
use Response;
class UnclaimedTransactionsController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin|customer-care|finance']);
    }

    public function index(){
        $unclaimed = UnclaimedTransaction::select('id', 'amount_paid')->get();
        //dd($unclaimed->sum('amount_paid'));
    	return view('unclaimed')->with('unclaimed', $unclaimed);
    }

    public function search(Request $request){
        $query = trim($request['q']);

        if(empty($query)){
            $transactions = UnclaimedTransaction::orderBy('id', 'DESC')->limit(50)->get();
        }else{

            $transactions = UnclaimedTransaction::Where('mobile_wallet_transaction_id', 'LIKE', "%".$query."%")
                                 ->orWhere('transaction_source', 'LIKE', "%".$query."%")
                                 ->skip(0)
                                 ->take(50)
                                 //->select('id', 'email_address', 'phone_number')
                                 ->get();

        }

        if(isset($transactions)){
        	foreach ($transactions as $transaction) {
        		$transaction->transaction_date = Carbon::parse($transaction->transaction_date)->format('d-m-Y h:m:s');
        	}
        }

        return response()->json($transactions);
    }


    public function unclaimedCSV(){
        $transactions = UnclaimedTransaction::whereYear('created_at', "2019")
                        ->orderBy('id', 'DESC')->get();        

        $callback = function() use ($transactions){
        //$report_date = "2018-07-29";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array('TX_CODE', 'SOURCE', 'ACCOUNT', 'AMOUNT', 'DATE'));

        //Populate the data
        foreach ($transactions as $unclaimed) {
            $code = $unclaimed->mobile_wallet_transaction_id;
            $source = $unclaimed->transaction_source."\r";
            $account = $unclaimed->transaction_account."\r";
            $amount = $unclaimed->amount_paid;
            $date = Carbon::parse($unclaimed->created_at)->format('d-m-Y');
            
            fputcsv($handle, [$code, $source, $account, $amount, $date]);
        }
        fclose($handle);
    };
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=unclaimed.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
    }
}
