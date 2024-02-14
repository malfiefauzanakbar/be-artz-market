<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use App\User;
use App\Tax;
use Carbon\Carbon;
use Config;

class TaxController extends Controller
{
    public function index(Request $request)
    {                

        $Taxs = Tax::get();
        $serTaxs = $this->serializeTax($Taxs, 'object');
        if ($serTaxs) {
            return response([
                'success'   => true,
                'message'   => 'List Tax',
                'data'      => $serTaxs
            ], 200);
        }else{
            return response([
                'success'   => true,
                'message'   => 'Data Not Found!',
                'data'      => []
            ], 200);
        }
    }

    public function store(Request $request)
    {        
        $token = $request->header('token');                
        $checkToken = AppHelper::checkToken($token);
        if ($checkToken == 'true'){
            return response()->json(['success' => false,'message' => 'Token Expired!',], 400);
        }
        
        //validate data
        $validator = Validator::make($request->all(), [            
            'tax'      => 'required',
            'service'      => 'required',  
        ],
            [
                'tax'     => 'Tax Is Required!',
                'service'     => 'Service Is Required!',             
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {                                      

            $Tax = Tax::create([                                                
                'tax'      => $request->input('tax'),
                'service'      => $request->input('service'),
            ]);

            if ($Tax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Success Create Data!',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed Create Data!',
                ], 400);
            }            
        }
    }

    public function show($id, Request $request)
    {        

        $Tax = Tax::whereId($id)->get();
        $serCompany = $this->serializeTax($Tax, 'object');
        if ($serCompany) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Tax!',
                'data'    => $serCompany
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Data Not Found!',
                'data'    => (object)array()
            ], 200);
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
            'tax'      => 'required',
            'service'      => 'required',  
        ],
            [
                'tax'     => 'Tax Is Required!',
                'service'     => 'Service Is Required!',             
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {            
                         
            $Tax = Tax::whereId($id)->first();            
           
            $Tax = $Tax->update([                
                'tax'      => $request->input('tax'),
                'service'      => $request->input('service'),
            ]);                    

            if ($Tax) {
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

    public function destroy($id, Request $request)
    {
        $token = $request->header('token');        
        $checkToken = AppHelper::checkToken($token);
        if ($checkToken == 'true'){
            return response()->json(['success' => false,'message' => 'Token Expired!',], 400);
        }

        $Tax = Tax::findOrFail($id);
        $Tax->delete();        

        if ($Tax) {            
            return response()->json([
                'success' => true,
                'message' => 'Success Delete Data!',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed Delete Data!',
            ], 500);
        }

    }

    public static function serializeTax($Taxs, $type)
    {
        // error_log($Taxs);
        $data = array();
        foreach ($Taxs as $Tax){            
            $item =  array (
              'id'    => $Tax->id,             
              'tax'    => $Tax->tax,
              'service'    => $Tax->service,
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
