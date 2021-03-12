<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Customer;
use App\Loan;
use App\Repayment;
use App\Bee;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){

        // $bee_groups = DB::table('customer_bee')
        //      ->select('customer_ranking', DB::raw('count(*) as total'))
        //      ->groupBy('customer_ranking')
        //      ->pluck('total','customer_ranking')->all();
        // dd($bee_groups);
        $repaid_loans = Loan::completed()->sum('principle_disbursed');
        $total_paid_back = Repayment::has('loan')->select('amount_paid')->sum('amount_paid');
        $earning = ($repaid_loans * 0.15);

        $disbursed = Loan::unpaid()->sum('principle_disbursed');
        $accrued_int = ($disbursed * 0.15);
        
        $critical = [
            'customers' => Customer::select('id')->count(),
            'active_customers' => Customer::has('loans')->select('id')->count(),
            'loans' => Loan::unpaid()->select('id')->count(),
            'active' => Loan::active()->select('id')->count(),
            'late' => Loan::late()->select('id')->count(),
            'disbursed' => $disbursed,
            'accrued_int' => $accrued_int,
            'completed' => $repaid_loans,
            'earning' => $earning,
            'repaid' => $repaid_loans + $earning
        ];

        // $loans = [
        //     'active' => Loan::active()->count(),
        //     'completed' => Loan::completed()->count(),
        //     'defaulted' => Loan::defaulted()->count()
        // ];

        $loans = [
            'active' => Loan::active()->sum('principle_disbursed'),
            'late' => Loan::late()->sum('principle_disbursed'),
            'completed' => Loan::completed()->sum('principle_disbursed'),
            'defaulted' => Loan::late()->sum('principle_disbursed')
        ];

        //change the denominator to outstanding

        //$default_rate = (Loan::late()->sum('principle_disbursed')/Loan::sum('principle_disbursed')) * 100;
        if(Loan::late()->sum('principle_disbursed')){
            $default_rate = (Loan::late()->sum('principle_disbursed')/Loan::unpaid()->sum('principle_disbursed')) * 100;
        }else{
            $default_rate = 0;
        }
        

        //customers by ranking
        $green = Customer::green()->inactive()->select('id')->count();
        $bronze = Customer::bronze()->inactive()->select('id')->count();
        $silver = Customer::silver()->inactive()->select('id')->count();
        $gold = Customer::gold()->inactive()->select('id')->count();

        //all customers by ranking
        $a_green = Customer::green()->select('id')->count();
        $a_bronze = Customer::bronze()->select('id')->count();
        $a_silver = Customer::silver()->select('id')->count();
        $a_gold = Customer::gold()->select('id')->count();

        $customer_stats = [
            'green' => $green,
            'bronze' => $bronze,
            'silver' => $silver,
            'gold' => $gold
        ];

        //dd($customer_stats);

        $a_customers = [
            'green' => $a_green,
            'bronze' => $a_bronze,
            'silver' => $a_silver,
            'gold' => $a_gold
        ];

        $demand = [
            'green' => (Bee::green()->avg('customer_maximum_actual_limit') * $green),
            'bronze' => (Bee::bronze()->avg('customer_maximum_actual_limit') * $bronze),
            'silver' => (Bee::silver()->avg('customer_maximum_actual_limit') * $silver),
            'gold' => (Bee::gold()->avg('customer_maximum_actual_limit') * $gold)
        ];

        $total_demand = [
            'green' => (Bee::green()->avg('customer_maximum_actual_limit') * $a_green),
            'bronze' => (Bee::bronze()->avg('customer_maximum_actual_limit') * $a_bronze),
            'silver' => (Bee::silver()->avg('customer_maximum_actual_limit') * $a_silver),
            'gold' => (Bee::gold()->avg('customer_maximum_actual_limit') * $a_gold)
        ];
        //dd($demand);

        $loansController = new LoansController();
        $monthlyLoans = $loansController->monthlyLoans();
        return view('home')->with('critical', (object)$critical)
                            ->with('loans', (object)$loans)
                            ->with('default_rate', $default_rate)
                            ->with('monthlyLoans', $monthlyLoans)
                            ->with('customer_stats', (object)$customer_stats)
                            ->with('a_customers', (object)$a_customers)
                            ->with('demand', (object)$demand)
                            ->with('total_demand', (object)$total_demand);
    }


    public function critical(){

        $repaid_loans = Loan::completed()->sum('principle_disbursed');
        $total_paid_back = Repayment::has('loan')->select('amount_paid')->sum('amount_paid');
        $earning = ($repaid_loans * 0.15);
        
        $critical = [
            'customers' => Customer::select('id')->count(),
            'active_customers' => Customer::has('loans')->select('id')->count(),
            'loans' => Loan::unpaid()->select('id')->count(),
            'active' => Loan::active()->select('id')->count(),
            'late' => Loan::late()->select('id')->count(),
            'disbursed' => Loan::unpaid()->sum('principle_disbursed'),
            'completed' => $repaid_loans,
            'earning' => $earning,
            'repaid' => $repaid_loans + $earning
        ];

        dd($critical);

    }
}
