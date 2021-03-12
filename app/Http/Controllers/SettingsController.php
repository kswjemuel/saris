<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Setting;
use Response;

class SettingsController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin']);
    }

    public function online(){
    	$offline = Setting::where('attribute', 'offline')->first();
    	$offline->value = 0;
    	$offline->save();
    	return response()->json($offline);
    }

    public function offline(){
    	$offline = Setting::where('attribute', 'offline')->first();
    	$offline->value = 1;
    	$offline->save();
    	return response()->json($offline);
    }

    public function beeRequests(){
        $path = '/var/shika/bee/history/request/15275448208586.xml';
        //dd($path);
        // if(Storage::disk('beerequets')->exists('1525081819.xml')){
        //     Storage::disk('beerequets')->download('1525081819.xml');
        // };
        $headers = array(
            'Content-Type' => 'text/xml'
        );
        $output_file='test.xml';
        //$path = storage_path('test.csv');
        return Response::download($path, $output_file, $headers);
    }
}
