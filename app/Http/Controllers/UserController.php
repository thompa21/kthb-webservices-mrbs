<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\tadmin;

class UserController extends Controller
{

    public function authenticate(Request $request)
    
    {

        $this->validate($request, [
            'Epost' => 'required',
            'Password' => 'required'
        ]);

        $user = tadmin::where('Epost', $request->input('Epost'))->first();

        if($request->input('Password') == $user->password){
            $apikey = base64_encode(str_random(40));
            //Users::where('email', $request->input('email'))->update(['api_key' => "$apikey"]);;
            return response()->json(['status' => 'success','api_key' => $apikey]);
        } else {
            return response()->json(['status' => 'fail'],401);
        }

    }

    public function index(Request $request)
    {
        $users = tadmin::all();
        return response()->json($users); 
        //return "test";    
    }

    public function getUser($id)
    {
        
        if (is_numeric($id))
        {
            $user = tadmin::find($id);
        }
        else
        {
            $column = 'Name';
            $user = tadmin::where($column , '=', $id)->first();
        }
        
        return response()->json($user);
    }

    public function createUser(Request $request)
    {
        $user = tadmin::create($request->all());
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $user = tadmin::find($id);
        $user->name = $request->input('name');
        $user->level = $request->input('level');
        $user->email = $request->input('email');
        $user->save();
        return response()->json($user);   
    }

    public function deleteUser($id){
        $user = tadmin::find($id);
        $user->delete();
        return response()->json('deleted');
    }
   
}
?>