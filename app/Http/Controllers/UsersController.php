<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Lender;
use App\Permission;
use Auth;
use Validator;
use App\Events\UserCreated;

class UsersController extends Controller
{
    public function __construct(){
        $this->middleware(['role:admin']);
    }

    public function index(){

			$users = User::all();
            $roles = Role::all();
	    	return view('users')->with('users', $users)->with('roles', $roles);
    }


    public function create(Request $request){
        //validate the data
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:saris_users',
            'role' => 'required|numeric|max:10'
        ]);

        
        //if all is well, create a new user
        $user = new User();
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->password = bcrypt($request['password']);
        $user->save();
        $user->attachRole($request['role']);
        //event(new UserCreated($user));

        //create a lender if the user has lender role
        if($user->hasRole('lender')){
            $lender = new Lender();
            $lender->user_id = $user->id;
            $lender->save();
        }
        return redirect()->back();
    }

    public function delete(Request $request){
        $id = $request->input('id');
        $user = User::find($id);

        //prevent a user from deleting themselves
        if(Auth::user()->id == $id){
            return response()->json('Oops! something went wrong');
        }

        $user->delete();
        return response()->json('User deleted sucessfully');
    }

    public function createRole(Request $request){
        //validate the data
        $this->validate($request, [
            'name' => 'required'
        ]);

        $name = strtolower(trim($request['name']));
        $name = str_replace(' ', '-', $name);
        //if all is well, create a new user
        $role = new Role();
        $role->name         = $name;
        $role->display_name = $request['name'];
        $role->description  = '';
        $role->save();
        return redirect()->back();
    }

    public function search(Request $request){
        //dd($request->get('q'));
        $users = User::where('name', 'LIKE', $request->get('q'))->get();
        return response()->json($users);
        //return User::search("zack", null, true)->get();
    }
}
