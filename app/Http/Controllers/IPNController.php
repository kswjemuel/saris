<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use Carbon\Carbon;

class IPNController extends Controller
{
    public function index(){
    	$data  = json_decode(file_get_contents('php://input'), true);
        //Log::info('IPN Payload...'.json_encode($requestData));
        $transaction = new Transaction();
        $transaction->category = isset($data['category']) ? $data['category'] : NULL;
        $transaction->tx_code = isset($data['providerRefId']) ? $data['providerRefId'] : NULL;
        $transaction->clientAccount = isset($data['clientAccount']) ? $data['clientAccount'] : NULL;
        $transaction->source = isset($data['source']) ? $data['source'] : NULL;
        $transaction->sourceType = isset($data['sourceType']) ? $data['sourceType'] : NULL;
        $transaction->direction = isset($data['direction']) ? $data['direction'] : NULL;
        $transaction->status = isset($data['status']) ? $data['status'] : NULL;
        $transaction->amount = isset($data["value"]) ? floatval(preg_replace("/[^0-9.,]/", "", trim($data["value"]))) : NULL;
        $transaction->tx_date = isset($data['transactionDate']) ? Carbon::parse($data['transactionDate'])->toDateTimeString() : NULL;
        $transaction->save();
        dd($data);
    }
}
