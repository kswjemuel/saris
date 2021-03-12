<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin']);
    }

    public function create(Request $request){
    	
    }
}
