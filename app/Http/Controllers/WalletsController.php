<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Wallet;
use App\WalletTransaction;

class WalletsController extends Controller
{
	public function index(){
		try {
			//Get the wallet stats data
			//return the wallet view
			$totalIn = WalletTransaction::select('amount')->where('direction', 'in')->sum('amount');
    		$totalOut = WalletTransaction::select('amount')->where('direction', 'out')->sum('amount');
			$balance = Wallet::select('available_balance')->sum('available_balance');
			return view('wallet')
					->withBalance($balance)
					->with('totalIn', $totalIn)
    				->with('totalOut', $totalOut);
		} catch (\Exception $e) {
			abort(500);
		}
	}
    public function customerWallet(Request $request, $id){
    	try {
    		//Get the customer by $id
    		$customer = Customer::where('id', $id)->with('transactions')->first();
    		if(empty($customer->id)){
    			abort(404);
    		}
    		//Get the transactions
    		$totalIn = $customer->transactions()->where('direction', 'in')->sum('amount');
    		$totalOut = $customer->transactions()->where('direction', 'out')->sum('amount');
    		$transactionFees = $customer->transactions()->where('type', 'transaction_fee')->sum('amount');
    		//Return the wallet view
    		return view('customerwallet')->withCustomer($customer)
    				->with('totalIn', $totalIn)
    				->with('totalOut', $totalOut)
    				->with('transactionFees', $transactionFees);
    		
    	} catch (\Exception $e) {
    		return response($e->getMessage());
    		//abort(500);
    	}
    }
}
