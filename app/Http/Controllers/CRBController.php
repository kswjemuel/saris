<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Loan;
use App\Repayment;
use Response;
class CRBController extends Controller
{
    //CRB SUBMISSION
    public function listCRB(){

        $loans = Loan::where('loan_completed_on', NULL)
                    ->whereMonth('created_at', '>', 3)->get();
        //$loans = Loan::all();


        $submission_date = Carbon::today()->format('Ymd');
        $submission_name = "CRBMCE".$submission_date."001.M003.csv";
        //$submission_name = "CRBMDMF".$submission_date."001.M003.csv"; //Version 4
        //set headers
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename='.$submission_name
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];


        $callback = function() use ($loans){

        	$handle = fopen('php://output', 'w');

        fputcsv($handle, array('Surname*', 'Forename 1*', 'Forename 2', 'Forename 3', 'Salutation', 'Date of Birth*', 'Client Number', 'Account Number*', 'Gender*', 'Nationality*', 'Marital Status*', 'Primary Identification Document Type*', 'Primary Identification Document Number*', 'Secondary Identification Document Type', 'Secondary Identification Document Number', 'Other Identification Document Type', 'Other Identification Document Number', 'Mobile Telephone Number*', 'Home Telephone Number', 'Work Telephone Number', 'Postal Address 1', 'Postal Address 2', 'Postal Location Town', 'Postal Location Country', 'Postal Code', 'Physical Address1', 'Physical Address 2', 'Plot Number', 'Location Town', 'Location Country*', 'Date at Physical Address', 'PIN Number', 'Consumer work E-Mail', 'Employer Name', 'Employer Industry Type', 'Employment Date', 'Employment type', 'Salary Band', 'Lenders Registered Name*', 'Lenders Trading Name', 'Lenders Branch Name*', 'Lenders Branch Code*', 'Account Joint/Single Indicator*', 'Account Product Type*', 'Date Account Opened*', 'Instalment Due Date*', 'Original Amount*', 'Currency of Facility*', 'Amount in Kenya shillings*', 'Current Balance*', 'Overdue Balance?', 'Overdue Date?', 'Nr of Days In Arrears*', 'Nr of Instalments In Arrears?', 'Performing/ NPL Indicator*', 'Account Status*', 'Account Status Date*', 'Account Closure Reason', 'Repayment Period*', 'Deferred Payment Date', 'Deferred Payment Amount', 'Payment Frequency?', 'Disbursement Date*', 'Instalment Amount', 'Date of Latest Payment', 'Last Payment Amount', 'Type of Security*'));

        foreach($loans as $loan) {
           // $id = $loan->id;
            if($loan->customer){
                $customer = $loan->customer;

                //split the customer user name
                $names = explode(' ', trim($customer->user->name));

                $surname = isset($names[2]) ? trim($names[2]) : trim($names[1]); //A50
                $surname = !empty($surname) ? trim($surname) : trim($names[1]); //A50
                $forename1 = isset($names[0]) ? $names[0] : "U"; //A50
                $forename2 = isset($names[1]) ? $names[1] : "U"; //A50
                $forename3 = ""; //A50
                $salutation = ""; //Mr., Mrs., Miss, Ms, Dr. , Prof., Hon., Rev (optional)
                $date_of_birth = Carbon::parse($customer->date_of_birth)->format('Ymd');
                $client_number = $customer->id; //A20
                $account_number = $loan->id; //A20
                $the_gender = !empty($customer->gender) ? $customer->gender : "U";
                $the_gender = strtolower($the_gender);
                if($the_gender == 'male'){
                	$gender = "M";
                }elseif($the_gender == 'female'){
                	$gender = "F";
                }else{
                	$gender = "U";
                }

                $nationality = "KE"; //A2 - ISO Country Code for the Consumer’s Nationality
                $marital_status = "U"; //U for Unknown
                $primary_idenentification_doc_type = "001"; //001 for national id, 002 for passport
                $primary_idenentification_doc_number = $customer->identification; //customer id
                $secondary_idenentification_doc_type = ""; //optional
                $secondary_idenentification_doc_number = ""; //conditional based on above field
                $other_idenentification_doc_type = ""; //optional
                $other_idenentification_doc_number = ""; //optional based on above field
                $mobile_phone = $customer->phone_number."\r"; //A15 - CCCAAANNNNNNN e.g 254723909090
                $home_phone = "";
                $work_phone = "";
                $postal_address1 = "";
                $postal_address2 = "";
                $postal_loaction_town = "";
                $postal_location_country = "KE"; //A2 - ISO Country Code for the Consumer’s Nationality (optional)
                $post_code = "";
                $physical_address1 = "";
                $physical_address2 = "";
                $plot_number = "";
                $location_town = "";
                $location_country = "KE"; //A2 - ISO Country Code for the Consumer’s Nationality
                $date_at_physical_address = ""; //N8
                $PIN_number = "";
                $customer_work_email = isset($customer->email_address) ? $customer->email_address:""; //this could be customer email
                $employer_name = "";
                $employer_industry_type = "";
                $employment_date = "";
                $employment_type = "";
                $salary_band = "";
                $lenders_registered_name = "Alternative Circle";
                $lenders_trading_name = "Shika";
                $lenders_branch_name = "Alternative Circle"; //not sure about this
                $lenders_branch_code = "M002001"; //A7 IXXXYYY
                $account_indicator = "S"; //A1 S for single
                $account_product_type = "N"; //A1 others
                $date_account_opened = Carbon::parse($loan->created_at)->format('Ymd'); //N8 YYYYMMDD
                $instalment_due_date = Carbon::parse($loan->loan_due_on)->format('Ymd'); //N8 YYYYMMDD
                $original_amount = $loan->principle_disbursed; //C16
                $currency_the_facility = "KES"; //A3 The ISO Currency Code for the Currency
                $amount_in_kenya_shillings = $loan->principle_disbursed; //C16
                $current_balance = $loan->total_amount_due; //C16
                $overdue_balance = $loan->total_amount_due;
                $overdue_date = Carbon::parse($loan->loan_due_on)->format('Ymd');
                $days_late = (int)Carbon::parse($loan->loan_due_on)->diffInDays(Carbon::now(), false);
                $nr_of_days_in_arrears = ($days_late > 0) ? $days_late : 0;
                $nr_of_instalments_in_arrears = 1; //one for shika
                $performing_npl_indicator = ($nr_of_days_in_arrears < 91) ? "A":"B"; //A1 A = performing, B = npl
                $account_status = "F"; //A1 F = Active, A= closed, B=dormant etc
                $account_status_date = Carbon::parse($loan->created_at)->format('Ymd'); //N8 YYYYMMDD - the date status changed
                $account_closure_reason = "";
                $repayment_period = 1; //one month
                $deferred_payment_date = "";
                $deferred_payment_amount = "";
                $payment_frequency = "";
                $disbursement_date = Carbon::parse($loan->created_at)->format('Ymd'); //N8 YYYYMMDD
                $instalment_amount = $loan->principle_disbursed;
                $date_of_last_payment = ""; //get the date of last repayment (optional)
                $last_repayment_amount = ""; //get the amount of last repayment (optional)
                $repayment = Repayment::where('loan_id', '=', $loan->id)->orderBy('id', 'DESC')->first();
                if($repayment){
                    $date_of_last_payment = Carbon::parse($repayment->created_at)->format('Ymd');
                    $last_repayment_amount = $repayment->amount_paid;
                }
                $type_of_security = "U"; //A1 U = Unsecured


                fputcsv($handle, array($surname, $forename1, $forename2, $forename3, $salutation, $date_of_birth, $client_number, $account_number, $gender, $nationality, $marital_status, $primary_idenentification_doc_type, $primary_idenentification_doc_number, $secondary_idenentification_doc_type, $secondary_idenentification_doc_number, $other_idenentification_doc_type, $other_idenentification_doc_number, $mobile_phone, $home_phone, $work_phone, $postal_address1, $postal_address2, $postal_loaction_town, $postal_location_country, $post_code, $physical_address1, $physical_address2, $plot_number, $location_town, $location_country, $date_at_physical_address, $PIN_number, $customer_work_email, $employer_name, $employer_industry_type, $employment_date, $employment_type, $salary_band, $lenders_registered_name, $lenders_trading_name, $lenders_branch_name, $lenders_branch_code, $account_indicator, $account_product_type, $date_account_opened,  $instalment_due_date,  $original_amount, $currency_the_facility, $amount_in_kenya_shillings,  $current_balance, $overdue_balance, $overdue_date, $nr_of_days_in_arrears, $nr_of_instalments_in_arrears, $performing_npl_indicator, $account_status, $account_status_date, $account_closure_reason, $repayment_period, $deferred_payment_date, $deferred_payment_amount, $payment_frequency, $disbursement_date, $instalment_amount, $date_of_last_payment, $last_repayment_amount, $type_of_security));
            }
        }

        fclose($handle);

        };


        return Response::stream($callback, 200, $headers);
        
    }
}
