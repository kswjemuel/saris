<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lender;
use App\Loan;
use App\Repayment;
use App\Customer;
use App\SMS;
use App\Disbursement;
use App\Orphan;
use App\User;
use Auth;


class LendersController extends Controller
{
    public function index(){
    	$lenders = Lender::all();
    	return view('lenders')->with('lenders', $lenders);
    }

    public function single($id){
    	$lender = Lender::find($id);
    	if(!$lender){ abort(404); }

        $total_disbursed = Loan::select('principle_disbursed')->sum('principle_disbursed');
        $repaid_loans = Loan::completed()->sum('principle_disbursed');
        $total_paid_back = Repayment::has('loan')->select('amount_paid')->sum('amount_paid');
        $earning = ($repaid_loans * 0.15);

        $outstanding = Loan::unpaid()->sum('principle_disbursed');
        $accrued_int = ($outstanding * 0.15);

        $cash_at_hand = ($lender->invested_amount + $earning) - $outstanding;
        
        $critical = [
            'cash_in' => $lender->invested_amount,
            'total_disbursed' => $total_disbursed,
            'outstanding' => $outstanding,
            'total_paid_back' => $total_paid_back,
            'earning' => $earning,
            'cash_at_hand' => $cash_at_hand
        ];

        $orphaned_repayment_fees = Orphan::select('provider_transaction_fee')->sum('provider_transaction_fee');
        $sms_fees = SMS::select('sms_cost')->sum('sms_cost');
        $gateway_disbursement_fees = Disbursement::select('loan_disbursement_merchant_fee')
                            ->sum('loan_disbursement_merchant_fee');
        $mpesa_disbursement_fees = Disbursement::select('loan_disbursement_engine_transaction_fee')
                            ->sum('loan_disbursement_engine_transaction_fee');
        $repayment_fees = Repayment::select('provider_transaction_fee')->sum('provider_transaction_fee');

        $total_expenses = ($orphaned_repayment_fees + $sms_fees + $gateway_disbursement_fees + $mpesa_disbursement_fees + $repayment_fees);

        //Expenses
        $expenses = [
            'sms' => $sms_fees,
            'mpesa' => $mpesa_disbursement_fees,
            'gateway' => ($repayment_fees + $gateway_disbursement_fees + $orphaned_repayment_fees),
            'total' => $total_expenses,
            'roi' => (($earning - $total_expenses) / $lender->invested_amount) * 100
        ];
        // $days[] = Carbon::parse($dt)->format('d/m');
        // $disbursement[] = Disbursement::whereDate('created_at', $dt)
        //             ->sum('loan_disbursement_engine_transaction_fee');
        // $at[] = Disbursement::whereDate('created_at', $dt)
        //             ->sum('loan_disbursement_merchant_fee');
        // //combine repayments and orphaned
        // $repayments_fees = Repayment::whereDate('created_at', $dt)->sum('provider_transaction_fee');
        // $orphaned_fees = Orphan::whereDate('created_at', $dt)->sum('provider_transaction_fee');
        // $repayment[] = number_format(($repayments_fees + $orphaned_fees), 2);
        // $sms[] = number_format(SMS::whereDate('created_at', $dt)->sum('sms_cost'), 2);

    	return view('lender')->with('lender', $lender)
                ->with('critical', (object)$critical)
                ->with('expenses', (object)$expenses);
    }

    public function update(Request $request){
    	$lender = Lender::find($request['lender_id']);
    	if($lender){
    		$lender->invested_amount = $request['invested_amount'];
    		$lender->save();
    	}

    	return redirect()->back();
    }
}
