<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use App\User;
use App\CategoryProduct;
use Carbon\Carbon;
use Config;

class CategoryProductController extends Controller
{
    public function index(Request $request)
    {                

        $categoryproducts = CategoryProduct::get();
        $serCategoryProducts = $this->serializeCategoryProduct($categoryproducts, 'array');
        if ($serCategoryProducts) {
            return response([
                'success'   => true,
                'message'   => 'List CategoryProduct',
                'data'      => $serCategoryProducts
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
            return response()->json(['success' => false,'message' => 'Token Expired!', 'is_token_expired' => true], 400);
        }
        
        //validate data
        $validator = Validator::make($request->all(), [            
            'name'      => 'required',  
        ],
            [
                'name'     => 'Name Is Required!',             
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {                          
            $checkName = CategoryProduct::where(DB::raw("LOWER(name)"), strtolower($request->input('name')))->first();

            if ($checkName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Name Already Exist!',
                ], 400);
            }

            $categoryproduct = CategoryProduct::create([                                                
                'name'      => $request->input('name'),
            ]);

            if ($categoryproduct) {
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

        $categoryproduct = CategoryProduct::whereId($id)->get();
        $serCompany = $this->serializeCategoryProduct($categoryproduct, 'object');
        if ($serCompany) {
            return response()->json([
                'success' => true,
                'message' => 'Detail CategoryProduct!',
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
            return response()->json(['success' => false,'message' => 'Token Expired!', 'is_token_expired' => true], 400);
        }

        //validate data
        $validator = Validator::make($request->all(), [            
            'name'      => 'required',  
        ],
            [
                'name'     => 'Name Is Required!',             
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {

            $checkName = CategoryProduct::where(DB::raw("LOWER(name)"), strtolower($request->input('name')))->first();

            if ($checkName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Name Already Exist!',
                ], 400);
            }
            
            //upload image            
            $categoryproduct = CategoryProduct::whereId($id)->first();            
           
            $categoryproduct = $categoryproduct->update([                
                'name'      => $request->input('name'),
            ]);                    

            if ($categoryproduct) {
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
            return response()->json(['success' => false,'message' => 'Token Expired!', 'is_token_expired' => true], 400);
        }

        $categoryproduct = CategoryProduct::findOrFail($id);
        $categoryproduct->delete();        

        if ($categoryproduct) {            
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

    public static function serializeCategoryProduct($categoryproducts, $type)
    {
        // error_log($categoryproducts);
        $data = array();
        foreach ($categoryproducts as $categoryproduct){            
            $item =  array (
              'id'    => $categoryproduct->id,             
              'name'    => $categoryproduct->name,            
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
