<?php
namespace App\Helpers;

use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Crypt;

class AppHelper
{
    public static function checkToken($token)
    {        
        $user = User::where('remember_token', $token)->first();                
        if (!$user){            
            return 'true';
        }        
        $timenow = Carbon::now();        
        $expiredToken = Crypt::decryptString($user->expired_token);   
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $timenow);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $expiredToken);
        if ($date1 > $date2){
            return 'true';
        }else{                              
            $updateExpiredToken = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime($timenow)));
            $user->expired_token   = Crypt::encryptString($updateExpiredToken);            
            $user->save();        
            return 'false';
        }
    }
}