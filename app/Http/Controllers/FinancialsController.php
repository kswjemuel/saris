<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Loan;
use App\Repayment;
use App\Statement;
use App\Expense;
use App\Disbursement;
use App\Orphan;

class FinancialsController extends Controller
{
    public function index(){
    	return view('financials');
    }



    public function income(){
    	$start_date = Carbon::today()->subDays(30);
    	$end_date = Carbon::today();

    	$loans = Loan::whereBetween('created_at', [$start_date, $end_date])->select('customer_id', 'principle_disbursed', 'created_at')->orderBy('created_at', 'DESC')->get();
    	//Penalties
    	$penalties = Statement::whereBetween('created_at', [$start_date, $end_date])
    		//->select('loan_id', 'amount', 'created_at')
    		->where('transaction_type', 'Loan Penalty')
    		->orderBy('created_at', 'DESC')->get();

    	// $repayments = Statement::whereBetween('created_at', [$start_date, $end_date])
    	// 	//->select('loan_id', 'amount', 'created_at')
    	// 	->where('transaction_type', 'Loan Repayment')
    	// 	->orderBy('created_at', 'DESC')->get();

    	$repayments = Repayment::whereBetween('created_at', [$start_date, $end_date])
    		->select('id', 'amount_paid', 'created_at')
    		//->where('transaction_type', 'Loan Repayment')
    		->orderBy('created_at', 'DESC')->get();

    	// $statements = Statement::whereBetween('created_at', [$start_date, $end_date])
    	// 	//->select('loan_id', 'amount', 'created_at')
    	// 	->where('accounting_class', 'CR')
    	// 	->orderBy('created_at', 'DESC')->get();
    	//dd($repayments->toArray());

    	//calculate the income repaid
    	$total_repaid = $repayments->sum('amount_paid');
    	$principle_repaid = $total_repaid * (100 / 115);
    	$actual_income = ($total_repaid - $principle_repaid);
    	//dd($principle_repaid);
    	
    	$income = [
    		'accrued' => ($loans->sum('principle_disbursed') * 0.15),
    		'total_repaid' => $total_repaid,
    		'principle_repaid' => $principle_repaid,
    		'actual_income' => $actual_income,
    		'penalties' => $penalties->sum('amount')
    	];
    	dd($income);
    }

    public function expenses(){
    	$start_date = Carbon::today()->subDays(30);
    	$end_date = Carbon::today();

    	//Expenses
    	$smss = Expense::whereBetween('created_at', [$start_date, $end_date])
    		->select('id', 'amount', 'created_at')
    		->where('account', 'SMS')
    		->orderBy('created_at', 'DESC')->get();

    	$disbursements = Disbursement::whereBetween('created_at', [$start_date, $end_date])
    		->select('id', 'loan_disbursement_merchant_fee', 'loan_disbursement_engine_transaction_fee', 'created_at')
    		//->where('loan_disbursement_engine_transaction_fee', '22')
    		->orderBy('created_at', 'DESC')->get();

    	//Repayment notifications fee
    	$repayments = Repayment::whereBetween('created_at', [$start_date, $end_date])
    		->select('id', 'amount_paid', 'provider_transaction_fee', 'provider_merchant_fee', 'created_at')
    		//->where('transaction_type', 'Loan Repayment')
    		->orderBy('created_at', 'DESC')->get();

    	//Unclaimed notifications fee
    	$unclaimed = Orphan::whereBetween('created_at', [$start_date, $end_date])
    		->select('id', 'amount_paid', 'provider_transaction_fee', 'provider_merchant_fee', 'created_at')
    		//->where('transaction_type', 'Loan Repayment')
    		->orderBy('created_at', 'DESC')->get();
    	//dd($unclaimed->toArray());
    	
    	$expenses = [
    		'sms' => $smss->sum('amount'),
    		'disbursement_safcom' => $disbursements->sum('loan_disbursement_engine_transaction_fee'),
    		'disbursement_at' => $disbursements->sum('loan_disbursement_merchant_fee'),
    		'repayments_notifications' => $repayments->sum('provider_transaction_fee'),
    		'unclaimed_notifications' => $unclaimed->sum('provider_transaction_fee')
    	];
    	dd($expenses);
    }
}
