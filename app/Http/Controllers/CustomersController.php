<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\CustomerSMS;
use App\CustomerApp;
use App\CustomerCall;
use App\CustomerContact;
use App\CustomerDevice;
use App\CreditHistory;
use App\Bee;
use App\Statement;
use Carbon\Carbon;
use DB;
use Response;
use App\Loan;
use App\Penalty;
use Auth;

class CustomersController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin|customer-care|finance|lender']);
    }

    public function index(){
        //$customers = Customer::where('last_seen', NULL)->select('id', 'last_seen', 'created_at')->get();
        //dd($bees);       
        //get the customers stats
        $registrations = Customer::today()->select('created_at')->count();
        $nobee = Customer::today()->nobee()->select('id')->count();
        $declined = Customer::today()->declined()->select('id')->count();
        if($registrations && $declined){
            $rejection_rate = $declined / ($registrations - $nobee) * 100;
        }else{
            $rejection_rate = 0;
        }

        $critical = [
            'new' => $registrations,
            'green' => Customer::today()->green()->select('id')->count(),
            'bronze' => Customer::today()->bronze()->select('id')->count(),
            'silver' => Customer::today()->silver()->select('id')->count(),
            'gold' => Customer::today()->gold()->select('id')->count(),
            'nobee' => $nobee,
            'declined' => $declined,
            'rejection_rate' => $rejection_rate
        ];
    	return view('customers')->with('critical', (object)$critical);
        //return view('customers-list');

    }

    public function updateRanking(){
        $bees = Bee::select('id', 'customer_id', 'customer_approval_status', 'customer_ranking', 'customer_maximum_actual_limit', 'customer_rejection_reason')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($bees as $key => $bee) {
            Customer::where('id', $bee->customer_id)->update([
                'customer_ranking' => $bee->customer_ranking,
                'customer_maximum_actual_limit' => $bee->customer_maximum_actual_limit,
                'customer_approval_status' => $bee->customer_approval_status,
                'customer_rejection_reason' => $bee->customer_rejection_reason
            ]);
        }
        dd($bees);
    }


    public function dailyStatistics(Request $request){
        $date = $request->input('date');

        //all customers created today
        $new = Customer::where(DB::raw('date(created_at)'), Carbon::parse($date)->toDateString())
                        ->count();

        //silver customers created today
        $silver = Customer::silver()->where(DB::raw('date(created_at)'), Carbon::parse($date)->toDateString())
                        ->count();

        //approved customers created today
        $approved_customers = Customer::approved()
                        ->where(DB::raw('date(created_at)'), Carbon::parse($date)->toDateString())
                        ->get();

        $limits = [];
        foreach ($approved_customers as $key => $customer) {
            $limits[] = (float)$customer->bee->customer_maximum_actual_limit;
        }


        $approved = $approved_customers->count();
        $declined = Customer::declined()
                        ->where(DB::raw('date(created_at)'), Carbon::parse($date)->toDateString())
                        ->count();

        $rejection_rate = ($declined / ($approved + $declined)) * 100;

        $critical = [
            'new' => $new,
            'silver' => $silver,
            'approved' => $approved,
            'declined' => $declined,
            'nobee' => $new - ($approved + $declined),
            'demand' => array_sum($limits),
            'average_amount_approved' => array_sum($limits)/count($limits),
            'rejection_rate' => $rejection_rate
        ];
        return response()->json($critical);
        // return view('customers')->with('critical', (object)$critical);
    }

    public function search(Request $request){
        $query = trim($request['q']);

        if(empty($query)){
            $customers = Customer::orderBy('id', 'DESC')->limit(40)->get();
        }elseif($request['q'] == 'blocked'){
            //get blocked customers
            $customers = Customer::blocked()->get();
            //dd($customers->toArray());
        }else{
            $customers = Customer::Where('email_address', 'LIKE', "%".$query."%")
                                 ->orWhere('phone_number', 'LIKE', "%".$query."%")
                                 ->orWhere('identification', 'LIKE', "%".$query."%")
                                 ->skip(0)
                                 ->take(20)
                                 //->select('id', 'email_address', 'phone_number')
                                 ->get();

        }
        


        if($customers){
            foreach ($customers as $key => $customer) {
                $customer_bee = $customer->bee;
                $customer->name = isset($customer->user->name) ? $customer->user->name : "";
                $customer->bee_status = isset($customer_bee) ? $customer_bee->rejection_rules_status : "No BEE";
                $customer->bee_limit = isset($customer_bee) ? $customer_bee->customer_maximum_actual_limit : "0";
                $customer->status = $customer->customer_status();
                $customer->verified = $customer->user->email_confirmed == 1 ? '':'text-danger';
                $customer->blocked = $customer->user->user_account_block_status == 1 ? 'blocked':'';
                $customer->join_date = Carbon::parse($customer->created_at)->format('d-m-Y H:m:s');
                $customer->link = route('customer', $customer->id);
                //$customers[$key]->customer_status = $customer->customer_status();
            }
        }

        return response()->json($customers);
    }


    //single customer
    public function single($id){
        $customer = Customer::find($id);

        if(empty($customer)){
            abort(404);
        }
        $customer_bee = $customer->bee;
        $statements = Statement::where('customer_id', $id)->get();
        //dd($statements);
        $last_loan = Loan::where('customer_id', $id)->orderBy('id', 'desc')->first();

        $smss = CustomerSMS::where('customer_id', $customer->id)->select('customer_id')->count();
        $contacts = CustomerContact::where('customer_id', $customer->id)->select('customer_id')->count();
        $calls = CustomerCall::where('customer_id', $customer->id)->select('customer_id')->count();
        $apps = CustomerApp::where('customer_id', $customer->id)->select('customer_id')->count();

        return view('customer')->with('customer', $customer)
                            ->with('bee', $customer_bee)
                            ->with('last_loan', $last_loan)
                            ->with('smss', $smss)
                            ->with('contacts', $contacts)
                            ->with('calls', $calls)
                            ->with('apps', $apps)
                            ->with('statements', $statements);
    }

    public function penalties($id){
        $loans = Loan::where('customer_id', $id)->select('id')->get();
        $penalties = Penalty::whereIn('loan_id', $loans)->get();
        return response()->json($penalties);
    }

    public function customerSMSs($id){
        $customer = Customer::find($id);
        $smss = CustomerSMS::where('customer_id', $id)
                //->where('message_transaction_type', 'paybill')
                ->where('message_amount', '>', 0)
                ->orderBy('created_at', 'DESC')
                //->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->select('id', 'customer_id', 'message_body', 'sender_address', 'message_amount', 'message_flow_type', 'date_sent', 'created_at')
                //->take(30)
                ->get();
        echo '<pre>';
        print_r($smss->count());
        echo '<br>';
        print_r($smss->sum('message_amount'));
        echo '<br>';
        print_r($smss->avg('message_amount'));
        echo '<br>';
        print_r($smss->median('message_amount'));

        dd($smss->toArray());
        //return response()->json($smss->toArray());
    }

    public function customerDevices($id){
        $customer = Customer::find($id);
        $devices = CustomerDevice::where('customer_id', $id)
                    ->select('id', 'customer_id', 'fire_base_id', 'device_id', 'manufacturer', 'device_model', 'screen_size', 'fingerprint', 'primary_imei_number', 'serial_number', 'created_at')
                    ->get();
        dd($devices->toArray());
    }

    public function phonebookFrequency($id){
        $customer = Customer::find($id);
        //count the number of times a customer number appears in contacts of others
        $phonebook_frequency = DB::table('customer_phone_contacts')
                ->where('phone_number', $customer->phone_number)
                ->where('customer_id', '!=', $customer->id)
                //->select('id', 'customer_id')
                ->get();
        dd($phonebook_frequency->toArray());
    }


    public function customerSMSDownload($id){
        $customer = Customer::find($id);
        $smss = CustomerSMS::where('customer_id', $id)
                //->where('message_transaction_type', 'paybill')
                ->where('message_amount', '>', 0)
                ->orderBy('created_at', 'DESC')
                //->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->select('id', 'customer_id', 'message_body', 'sender_address', 'message_amount', 'message_flow_type', 'date_sent', 'created_at')
                //->take(30)
                ->get();
                $callback = function() use ($smss){
                    $handle = fopen('php://output', 'w');
                    //add the header
                    fputcsv($handle, ['SENDER', 'MESSAGE']);
                    //Populate the data
                    foreach ($smss as $sms) {
                            $row = [
                            'sender' => $sms->sender_address,
                            'message' => $sms->message_body,
                            'created_at' => $sms->created_at
                        ];
                        fputcsv($handle, $row);
                    }
                    fclose($handle);
                };

                $headers = [
                        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
                    ,   'Content-type'        => 'text/csv'
                    ,   'Content-Disposition' => 'attachment; filename='.$id.'_smss.csv'
                    ,   'Expires'             => '0'
                    ,   'Pragma'              => 'public'
                ];
                return Response::stream($callback, 200, $headers);
    }

    public function customerCalls($id){
        $date = Carbon::createFromTimestampMs(1524052158459)->toDateTimeString();
        $calls = CustomerCall::where('customer_id', '>', 20)->skip(0)->take(1000000)->get();
        ///////////////////////////////////////////
        $callback = function() use ($calls){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['CUSTOMER ID', 'PHONE NUMBER', 'DIRECTION', 'DURATION', 'DATE']);
            //Populate the data
            foreach ($calls as $call) {
                $row = [
                    'customer' => empty($call->customer_id) ? "0" : $call->customer_id,
                    'number_called' => empty($call->number_called) ? "" : $call->number_called."\r",
                    'call_direction' => empty($call->call_direction) ? "" : $call->call_direction,
                    'call_duration' => empty($call->call_duration) ? 0 : $call->call_duration,
                    'call_date' => empty($call->call_date) ? "" : Carbon::createFromTimestampMs($call->call_date)->toDateTimeString()
                ];
                fputcsv($handle, $row);
            }
            fclose($handle);
        };
        /////////////////////////////////////////////////////////////
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=call-log.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);
        ///////////////////////////////////////////////////////////////////
    }

    public function customerContacts($id){
        $contacts = CustomerContact::where('customer_id', $id)
                    ->select('display_name', 'phone_number')
                    ->get();
        dd($contacts->toArray());
    }

    public function referred(){
        $customers = Customer::where('referred_by', '!=', '')->orderBy('id', 'DESC')->get();
        if(count($customers)){
            foreach ($customers as $key => $customer) {
                $ref = Customer::where('referral_code', '=', $customer->referred_by)->first();
                //dd($refferrer->user->name);
                $customer->referrer_name = isset($ref->user->name) ? $ref->user->name : "";
                $customer->referrer_email = isset($ref->email_address) ? $ref->email_address : "";
            }
        }
        //dd($customers);
        return view('customers-list')->with('customers', $customers);
    }



    public function customersCSV(){
        //$customers = Customer::has('loans')->where('id', '>', 200)->get();
        if(!Auth::user()->hasRole('admin')){
            abort(404);
        }

        $customers = Customer::whereNotIn('id', [1,2,3])->get();
        //$customers = Customer::bronze()->whereNotIn('id', [1,2,3])->orderBy('id', 'DESC')->skip(0)->take(5000)->get();

        if(!isset($customers)){
            return response()->json([]);
        }

        //dd($customers->count());

        $callback = function() use ($customers){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['ID', 'GENDER', 'AGE', 'RANKING', 'LIMIT', 'REJECTION REASON', 'LOANS', 'LAST SEEN', 'CREATED AT']);
            //Populate the data
            foreach ($customers as $customer) {
                    $row = [
                    'customer_id' => $customer->id,
                    'gender' => $customer->gender,
                    'age' => Carbon::parse($customer->date_of_birth)->age,
                    'ranking' => isset($customer->customer_ranking) ? $customer->customer_ranking:"unknown",
                    'limit' => isset($customer->customer_maximum_actual_limit) ? $customer->customer_maximum_actual_limit:"0",
                    'rejection_reason' => isset($customer->customer_rejection_reason) ? $customer->customer_rejection_reason:"NULL",
                    'loans' => $customer->loans->count(),
                    'last_seen' => $customer->last_seen,
                    'created_at' => $customer->created_at
                ];
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=customers.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);

        

        
        ////////////////////////////////////////////////////////////////////
    }




    public function customersWithLoansCSV(){
        //$customers = Customer::has('loans')->where('id', '>', 200)->get();

        $customers = Customer::whereNotIn('id', [1,2,3])->get();
        //$customers = Customer::bronze()->whereNotIn('id', [1,2,3])->orderBy('id', 'DESC')->skip(0)->take(5000)->get();

        if(!isset($customers)){
            return response()->json([]);
        }

        //dd($customers->count());

        $callback = function() use ($customers){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['CUSTOMER_ID', 'NAME','GENDER', 'AGE', 'PHONE', 'ID NUMBER', 'EMAIL', 'RANKING', 'LIMIT', 'REJECTION REASON', 'LOANS', 'ACTIVE', 'COMPLETED', 'LATE PAID', 'LATE UNPAID']);
            //Populate the data
            foreach ($customers as $customer) {
                    $row = [
                    'customer_id' => $customer->id,
                    'name' => $customer->user->name,
                    'gender' => $customer->gender,
                    'age' => Carbon::parse($customer->date_of_birth)->age,
                    'phone' => $customer->phone_number."\r",
                    'id_number' => $customer->identification."\r",
                    'email' => $customer->email_address,
                    'ranking' => isset($customer->customer_ranking) ? $customer->customer_ranking:"unknown",
                    'limit' => isset($customer->customer_maximum_actual_limit) ? $customer->customer_maximum_actual_limit:"0",
                    'rejection_reason' => isset($customer->customer_rejection_reason) ? $customer->customer_rejection_reason:"NULL",
                    'loans' => $customer->loans->count(),
                    'active' => Loan::active()->where('customer_id', $customer->id)->count(),
                    'completed' => Loan::completed()->where('customer_id', $customer->id)->count(),
                    'delayed' => Loan::delayed()->where('customer_id', $customer->id)->count(),
                    'late' => Loan::late()->where('customer_id', $customer->id)->count()
                ];
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=customers.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);

        

        
        ////////////////////////////////////////////////////////////////////
    }


    public function declined(){
        $customers = Customer::declined()->get();
        if(!isset($customers)){
            return response()->json([]);
        }

        $callback = function() use ($customers){
            $handle = fopen('php://output', 'w');
            //add the header
            fputcsv($handle, ['CUSTOMER_ID', 'NAME','GENDER', 'AGE', 'PHONE', 'EMAIL', 'RANKING', 'REASON', 'BEE REQUEST']);
            //Populate the data
            foreach ($customers as $customer) {
                $bee_history = Bee::where('customer_id', $customer->id)
                                ->orderBy('id', 'DESC')
                                ->first();
                if(!empty($customer) && count($bee_history)){
                    $row = [
                    'customer_id' => $customer->id,
                    'name' => $customer->user->name,
                    'gender' => $customer->gender,
                    'age' => Carbon::parse($customer->date_of_birth)->age,
                    'phone' => $customer->phone_number."\r",
                    'email' => $customer->email_address,
                    'ranking' => isset($customer->bee) ? $customer->bee->customer_ranking:"unknown",
                    'reason' => isset($customer->bee) ? $customer->bee->customer_rejection_reason:"unknown",
                    'bee_req' => $bee_history->bee_request_file_name
                ];
                fputcsv($handle, $row);
                }
            }
            fclose($handle);
        };

        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=rejected-customers.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        return Response::stream($callback, 200, $headers);

        ////////////////////////////////////
    }


    public function test(Request $request){
        $coordinates = [
            'latitude' => '-1.1730546',
            'longitude' => '36.842352'
        ];
        $lat = '-1.1730546';
        $lng = '36.842352';

        $radius = 1000;
        $max_distance = 50;

        $loans = DB::table("customer_loans")

        ->select("customer_loans.id"

            ,DB::raw("6371 * acos(cos(radians(" . $lat . ")) 

            * cos(radians(customer_loans.lat)) 

            * cos(radians(customer_loans.lon) - radians(" . $lng . ")) 

            + sin(radians(" .$lat. ")) 

            * sin(radians(customer_loans.lat))) AS distance"))
            //->whereRaw('customer_loans.distance', '<', $max_distance)
            ->orderBy('customer_loans.id', 'DESC')
            ->limit(10)
            ->get();

        // $haversine = "(6371 * acos(cos(radians(" . $coordinates['latitude'] . ")) 
        //             * cos(radians('latitude')) 
        //             * cos(radians('longitude') 
        //             - radians(" . $coordinates['longitude'] . ")) 
        //             + sin(radians(" . $coordinates['latitude'] . ")) 
        //             * sin(radians('latitude'))))";

        // $loans = Loan::selectRaw("{$haversine} AS distance")
        //     //->whereRaw("{$haversine} < ?", [$radius])
        //     ->orderBy('created_at', 'DESC')
        //     ->limit(10)
        //     ->get();

        dd($loans);

     //    $loans = Loan::whereNotIn('id', $ids)
     //   ->where('status', 1)
     //   ->whereHas('user_location', function($q) use ($radius, $coordinates) { 
     //        $q->whereRaw("111.045*haversine(latitude, longitude, '{$coordinates['latitude']}', '{$coordinates['longitude']}') <= " . $radius]);
     // })->select('id', 'firstname')
     //   ->get();
    }



    public function blockAccount(Request $request){
        $id = $request['id'];
        $user = Customer::find($id)->user;
        if(!$user->user_account_block_status){
            $user->user_account_block_status = true;
            $user->save();
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Account Blocked'
        ]);
    }


    public function unblockAccount($id){
        if(Auth::user()->email != 'kimkiuna@gmail.com'){
            abort(404);
        }

        $user = Customer::find($id)->user;
        if($user->user_account_block_status){
            $user->user_account_block_status = false;
            $user->save();
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Account Unblocked'
        ]);
    }
}
