<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Loan;
use App\Customer;
use App\CustomerCommitment;
use Carbon\Carbon;
use Response;
use DB;
use Auth;
use Validator;
use Exception;

class InternalDebtCollectionController extends Controller
{
    public function index(){
        //$date = "28-06-2019";
        //dd(Carbon::parse($date));
        //get the loans
        return view('internal-debt-collection');
    }

    //get the json list
    public function collectorJSON(Request $request){
    	//get the loans
    	if(!empty($request['start_date']) && !empty($request['end_date'])){
    		$from_date = Carbon::parse($request['start_date'])->toDateString();
    		$to_date = Carbon::parse($request['end_date'])->toDateString();
    		//query the loans
    		
    	}else{
            $from_date = Carbon::today('Africa/Nairobi')->toDateString();
            $to_date = Carbon::today('Africa/Nairobi')->toDateString();
    	}
    	
        $loans = Loan::whereNotIn('customer_id', [1,2,3])
                    ->unpaid()
                    ->whereBetween(DB::raw('DATE(loan_due_on)'), array($from_date, $to_date))
                    //->whereDate('date_marked_for_collection', '=', $date)
                    ->get();

    	$list = [];
        if(isset($loans)){
            foreach($loans as $key => $loan){
            if(isset($loan->customer)){
                $commitment = $loan->commitments()->orderBy('id', 'DESC')->first();
                $list[$key]['id'] = isset($loan->id) ? $loan->id : 0;
                $list[$key]['customer_id'] = isset($loan->customer->id) ? $loan->customer->id : 0;
            	$list[$key]['name'] = isset($loan->customer->user->name) ? $loan->customer->user->name : "";
            	//$list[$key]['email'] = isset($loan->customer->email_address) ? $loan->customer->email_address : "";
            	$list[$key]['phone'] = isset($loan->customer->phone_number) ? $loan->customer->phone_number : "";
            	//$list[$key]['identification'] = isset($loan->customer->identification) ? $loan->customer->identification : "";
            	$list[$key]['gender'] = isset($loan->customer->gender) ? $loan->customer->gender : "";
            	$list[$key]['age'] = isset($loan->customer->date_of_birth) ? Carbon::parse($loan->customer->date_of_birth)->age : 0;
            	//$list[$key]['ranking'] = $loan->customer->bee->customer_ranking;
            	$list[$key]['disbursed'] = $loan->principle_disbursed;
            	$list[$key]['due'] = $loan->total_amount_due;
            	$list[$key]['created_at'] = Carbon::parse($loan->created_at)->format('d-m-Y');
            	$list[$key]['due_at'] = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
                $list[$key]['commitment_date'] = isset($commitment->commitment_date) ? Carbon::parse($commitment->commitment_date)->format('d-m-Y') : 'None';
            	$list[$key]['days_late'] = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
                $list[$key]['status'] = $loan->loan_status;
            }
            }
        }

        return response()->json((object)$list);
    }



    public function createCommitment(Request $request){
        try {
            //Validate the data
            $validator = Validator::make($request->all(), [
                'loan_id' => 'required|max:20',
                'customer_id' => 'required|max:20',
                'comment' => 'required|max:255',
                'date' => 'nullable|date|max:255',
            ]);
            

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'msg' => $validator->errors()
                ]);
            }

            //check if the customer exists
            $customer = Customer::where('id', $request->input('customer_id'))->first();
            if(empty($customer)){
                return response()->json([
                    'status' => false,
                    'msg' => 'Unknown customer'
                ]);
            }

            //check if the customer exists
            $loan = Loan::where('id', $request->input('loan_id'))->first();
            if(empty($loan)){
                return response()->json([
                    'status' => false,
                    'msg' => 'Unknown loan'
                ]);
            }

            $user = Auth::user();

            //Create the commitment
            $commitment = new CustomerCommitment();
            $commitment->loan_id = $loan->id;
            $commitment->user_id = $user->id;
            $commitment->user_name = $user->name;
            $commitment->customer_id = $loan->customer_id;
            $commitment->outstanding_amount = $loan->total_amount_due;
            $commitment->customer_comment = $request->input('comment');
            $commitment->commitment_date = Carbon::parse($request->input('date'));
            if($commitment->save()){
                return response()->json([
                    'status' => true,
                    'msg' => 'Commitment sucessfully added'
                ]);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Oops, there was an error saving the commitment'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => $e
            ]);
        }
    }


    public function commitmentsView(){
        return view('commitments');
    }




    //Get commitments by date
    //get the json list
    public function commitmentJSON(Request $request){
        //get the loans
        if(!empty($request['start_date']) && !empty($request['end_date'])){
            $from_date = Carbon::parse($request['start_date'])->toDateString();
            $to_date = Carbon::parse($request['end_date'])->toDateString();
            //query the loans
            
        }else{
            $from_date = Carbon::today('Africa/Nairobi')->toDateString();
            $to_date = Carbon::today('Africa/Nairobi')->toDateString();
        }
        

        $commitments = CustomerCommitment::whereBetween(DB::raw('DATE(commitment_date)'), array($from_date, $to_date))
                    //->whereDate('date_marked_for_collection', '=', $date)
                    ->get();

        $list = [];
        if(isset($commitments)){
            foreach($commitments as $key => $commitment){
            $loan = $commitment->loan;
            if(isset($loan->customer)){
                $list[$key]['id'] = isset($loan->id) ? $loan->id : 0;
                $list[$key]['customer_id'] = isset($loan->customer->id) ? $loan->customer->id : 0;
                $list[$key]['name'] = isset($loan->customer->user->name) ? $loan->customer->user->name : "";
                $list[$key]['phone'] = isset($loan->customer->phone_number) ? $loan->customer->phone_number : "";
                $list[$key]['gender'] = isset($loan->customer->gender) ? $loan->customer->gender : "";
                $list[$key]['age'] = isset($loan->customer->date_of_birth) ? Carbon::parse($loan->customer->date_of_birth)->age : 0;
                $list[$key]['outstanding'] = $commitment->outstanding_amount;
                $list[$key]['due'] = $loan->total_amount_due;
                $list[$key]['created_at'] = Carbon::parse($loan->created_at)->format('d-m-Y');
                $list[$key]['due_at'] = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
                $list[$key]['days_late'] = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
                $list[$key]['status'] = $loan->loan_status;
                $list[$key]['comment'] = $commitment->customer_comment;
                $list[$key]['date'] = $commitment->commitment_date;
            }
            }
        }

        return response()->json((object)$list);
    }

    public function collectionCallsView(){
        return view('collection-calls');
    }


    public function collectionCallsJSON(Request $request){
        //get the loans
        if(!empty($request['start_date']) && !empty($request['end_date'])){
            $from_date = Carbon::parse($request['start_date'])->toDateString();
            $to_date = Carbon::parse($request['end_date'])->toDateString();
            //query the loans
            
        }else{
            $from_date = Carbon::today('Africa/Nairobi')->toDateString();
            $to_date = Carbon::today('Africa/Nairobi')->toDateString();
        }
        

        $commitments = CustomerCommitment::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))
                    //->whereDate('date_marked_for_collection', '=', $date)
                    ->get();

        $list = [];
        if(isset($commitments)){
            foreach($commitments as $key => $commitment){
            $loan = $commitment->loan;
            if(isset($loan->customer)){
                $list[$key]['id'] = isset($loan->id) ? $loan->id : 0;
                $list[$key]['customer_id'] = isset($loan->customer->id) ? $loan->customer->id : 0;
                $list[$key]['name'] = isset($loan->customer->user->name) ? $loan->customer->user->name : "";
                $list[$key]['phone'] = isset($loan->customer->phone_number) ? $loan->customer->phone_number : "";
                $list[$key]['gender'] = isset($loan->customer->gender) ? $loan->customer->gender : "";
                $list[$key]['age'] = isset($loan->customer->date_of_birth) ? Carbon::parse($loan->customer->date_of_birth)->age : 0;
                $list[$key]['outstanding'] = $commitment->outstanding_amount;
                $list[$key]['due'] = $loan->total_amount_due;
                $list[$key]['created_at'] = Carbon::parse($loan->created_at)->format('d-m-Y');
                $list[$key]['due_at'] = Carbon::parse($loan->loan_due_on)->format('d-m-Y');
                $list[$key]['days_late'] = Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
                $list[$key]['status'] = $loan->loan_status;
                $list[$key]['comment'] = $commitment->customer_comment;
                $list[$key]['date'] = $commitment->commitment_date;
            }
            }
        }

        return response()->json((object)$list);
    }
}
