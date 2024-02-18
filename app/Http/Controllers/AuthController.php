<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\AppHelper;

class AuthController extends Controller
{
    public function register(Request $request)
    {        
        //validate data
        $validator = Validator::make($request->all(), [
            'name' => 'unique:users|required',
            'email'    => 'unique:users|required',
            'password' => 'required',
            'confirm_password' => 'required',
            'date_of_birth' => 'required',
            'phone' => 'required',
            'role_id' => 'required'
        ],
            [                
                'name' => 'Name Is Required!',
                'email'    => 'Email Is Required!',
                'password' => 'Password Is Required!',
                'confirm_password' => 'Confirm Password Is Required!',
                'date_of_birth' => 'Date Of Birth Is Required!',
                'phone' => 'Phone!',
                'role_id' => 'Role Is Required!'         
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        }

        $name       = $request->name;
        $email      = $request->email;
        $password   = $request->password;
        $cpassword   = $request->confirm_password;
        $dateOfBirth   = $request->date_of_birth;
        $phone   = $request->phone;
        $timenow      = Carbon::now();               
        $expiredToken = date('Y-m-d H:i:s', strtotime('+90 minutes', strtotime($timenow)));   
        $role      = $request->input('role_id');
        if ($password != $cpassword) {            
            return response()->json([
                'success' => false,
                'message' => 'Password doesnt match!',
            ], 400);
        }
        $user       = User::create([
            'name'              => $name, 
            'email'             => $email, 
            'password'          => Crypt::encryptString($password),
            'date_of_birth'     => $dateOfBirth, 
            'phone'             => $phone, 
            'remember_token'    => Crypt::encryptString($timenow),
            'expired_token'     => Crypt::encryptString($expiredToken),            
            'role_id'           => $role
        ]);
        if (!$user) {  
            return response()->json([
                'success' => false,
                'message' => 'Failed Create Data! ',
                'data'      => (object)array()
            ], 400);
        }

        $user       = DB::table('users')
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->select('users.*', 'roles.name as role_name')
        ->where('email', $email)
        ->first();

        return response([
            'success'   => true,
            'message'   => 'Success Create Data!',
            'data'      => $user
        ], 200);
    }

    public function login(Request $request)
    {
        //validate data
        $validator = Validator::make($request->all(), [            
            'email'    => 'required',
            'password' => 'required',
        ],
            [                
                'email'    => 'Email Is Required!',
                'password' => 'Password Is Required!',      
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        }
        
        $email      = $request->email;
        $password   = $request->password;
        $user       = User::where('email', $email)->first();

        if ($user && Crypt::decryptString($user->password) == $password){
            
            $timenow                  = Carbon::now();            
            $rememberToken          = $timenow;
            $expiredToken          = date('Y-m-d H:i:s', strtotime('+90 minutes', strtotime($timenow)));
            $user->remember_token   = Crypt::encryptString($rememberToken);
            $user->expired_token   = Crypt::encryptString($expiredToken);            
            $user->save();        

            $user = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.name as role_name')
            ->where('email', $email)
            ->first();

            return response([
                'success'   => true,
                'message'   => 'Login Success!',
                'data'      => $user
            ], 200);            
        }else{
            return response([
                'success'   => false,
                'message'   => 'Login Failed!'
            ], 400);
        }         
    }

    public function logout(Request $request)
    {
        $token = $request->header('token');
        $user = User::where('remember_token', $token)->first();        
        if (!$user) {  
            return response()->json([
                'success' => false,
                'message' => 'Session Expired! ',
                'code' => '01'
            ], 400);
        }

        if ($user){
            
            $user->expired_token   = '';
            $user->remember_token   = '';
            $user->save();

            return response([
                'success'   => true,
                'message'   => 'Logout Success!'
            ], 200);            
        }else{
            return response([
                'success'   => false,
                'message'   => 'Logout Failed!'
            ], 400);
        }         
    }

    public function update($id, Request $request)
    {
        $token = $request->header('token');        
        $checkToken = AppHelper::checkToken($token);
        if ($checkToken == 'true'){
            return response()->json(['success' => false,'message' => 'Token Expired!',], 400);
        }

        //validate data
        $validator = Validator::make($request->all(), [            
            'name'      => 'required',
            'date_of_birth'      => 'required',
            'phone'      => 'required',
        ],
            [
                'name'     => 'Name Is Required!',          
                'date_of_birth'     => 'Date Of Birth Is Required!',
                'phone'     => 'Phone Is Required!',          
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {                           

            $user = User::whereId($id)->first();                              
           
            $user = $user->update([                
                'name'      => $request->input('name'),
                'date_of_birth'      => $request->input('date_of_birth'),
                'phone'      => $request->input('phone'),
            ]);                    

            if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Success Update Data!',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed Update Data!',
                ], 500);
            }

        }

    }

    public function show($id, Request $request)
    {        
        
        $user = User::where('id', $id)
        ->get();
        $seruser = $this->serializeUser($user, 'object');
        if ($seruser) {
            return response()->json([
                'success' => true,
                'message' => 'Detail user!',
                'data'    => $seruser
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Data Not Found!',
                'data'    => (object)array()
            ], 200);
        }
    }

    public static function serializeUser($users, $type)
    {
        // error_log($users);
        $data = array();        
        foreach ($users as $user){                                     
            $item =  array (
              'id'      => $user->id,
              'name'      => $user->name,
              'email'      => $user->email,
              'date_of_birth'      => $user->date_of_birth,
              'phone'      => $user->phone
            );                        
            
            if ($type == 'array'){                
                $data[] = $item;                
            }else{
                $data = $item;
            }
        }        
        return $data;
    }
}
