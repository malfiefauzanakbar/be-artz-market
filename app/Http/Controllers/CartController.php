<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use App\User;
use App\Cart;
use App\Product;
use Carbon\Carbon;
use Config;

class CartController extends Controller
{
    public function index($userId, Request $request)
    {                               
        $carts = DB::table('carts')
        ->join('products', 'carts.product_id', '=', 'products.id')
        ->join('users', 'carts.user_id', '=', 'users.id')
        ->select('carts.*', 'products.id as product_id', 'products.name as product_name', 'products.price', 'users.name')
        ->where('user_id', $userId)->where('status', 1)->get();
        
        $serCarts = $this->serializeCart($carts, 'array');
        if ($serCarts) {
            return response([
                'success'   => true,
                'message'   => 'List Cart',
                'data'      => $serCarts
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
            'product_id'      => 'required',
            'user_id'      => 'required',
        ],
            [
                'product_id'     => 'Product ID Is Required!',
                'user_id'     => 'User ID Is Required!',
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {                                      

            $checkProduct = Product::where('id', $request->input('product_id'))->first();
            if($checkProduct->stock == 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Stock Habis!',
                ], 400);
            }
            
            $checkCart = Cart::where('product_id', $request->input('product_id'))->where('user_id', $request->input('user_id'))->where('status', 1)->first();
            if ($checkCart){
                $cart = $checkCart->update([                
                    'qty'      => ($checkCart->qty + 1),
                ]);

                if ($cart) {
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
            
            $cart = Cart::create([                                                
                'product_id'      => $request->input('product_id'),
                'user_id'      => $request->input('user_id'),
                'qty'      => 1,
                'status'      => 1,
            ]);

            if ($cart) {
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

    public function update($id, Request $request)
    {
        $token = $request->header('token');        
        $checkToken = AppHelper::checkToken($token);
        if ($checkToken == 'true'){
            return response()->json(['success' => false,'message' => 'Token Expired!', 'is_token_expired' => true], 400);
        }

        //validate data
        $validator = Validator::make($request->all(), [            
            'qty'      => 'required',
        ],
            [
                'qty'     => 'Qty Is Required!',          
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {                           

            $cart = Cart::whereId($id)->first();

            if ($request->input('qty') == 0){
                $cart->delete();

                if ($cart) {            
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
            
            $checkProduct = Product::where('id', $cart->product_id)->first();
            if($checkProduct->stock == 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Stock Habis!',
                ], 400);
            }elseif($checkProduct->stock < $request->input('qty')){
                return response()->json([
                    'success' => false,
                    'message' => 'Stock Yang Tersedia Hanya Ada '.$checkProduct->stock.'!',
                ], 400);
            }
           
            $cart = $cart->update([                
                'qty'      => $request->input('qty'),
            ]);                    

            if ($cart) {
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

        $cart = Cart::findOrFail($id);
        $cart->delete();        

        if ($cart) {            
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

    public function countCart($userId, Request $request)
    {                               
        $carts = Cart::where('user_id', $userId)->where('status', 1)->get();
        $cartsCount = $carts->count();
                
        if ($carts) {
            return response([
                'success'   => true,
                'message'   => 'Count Cart',
                'data'      => $cartsCount
            ], 200);
        }else{
            return response([
                'success'   => true,
                'message'   => 'Data Not Found!',
                'data'      => 0
            ], 200);
        }
    }

    public static function serializeCart($carts, $type)
    {
        // error_log($carts);
        $data = array();
        foreach ($carts as $cart){            
            $data_image = array();
            $images = DB::table('product_images')
            ->where('product_id', $cart->product_id)
            ->get();
            foreach ($images as $image){
                $item_image =  array (
                  'id'      => $image->id,
                  'image'   => config('environment.app_url')
                  .config('environment.dir_product').$image->image           
                );

                $data_image[] = $item_image;
            }
            
            $item =  array (
              'id'      => $cart->id,
              'name'      => $cart->name,
              'product_id'      => $cart->product_id,
              'product_name'      => $cart->product_name,
              'price'      => $cart->price,
              'qty'      => $cart->qty, 
              'images' => $data_image,
            );                        

            if ($data_image){
                if ($type == 'array'){                
                    $data[] = $item;                
                }else{
                    $data = $item;
                }
            }
        }
        return $data;
    }
}
