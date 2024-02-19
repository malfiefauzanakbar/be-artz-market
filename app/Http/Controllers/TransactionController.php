<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use App\User;
use App\Transaction;
use App\DetailTransaction;
use App\Cart;
use App\Product;
use Carbon\Carbon;
use Config;

class TransactionController extends Controller
{
    public function index($userId, Request $request)
    {                               
        $transactions = DB::table('transactions')        
        ->join('users', 'transactions.user_id', '=', 'users.id')
        ->select('transactions.*', 'users.name');

        if($userId != 0){
            $transactions = $transactions->where('user_id', $userId)->where('status', 1);
        }
    
        $transactions = $transactions->get();
        
        $serTransactions = $this->serializeTransaction($transactions, 'array');
        if ($serTransactions) {
            return response([
                'success'   => true,
                'message'   => 'List Transaction',
                'data'      => $serTransactions
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
            'user_id'      => 'required',
            'sub_total'      => 'required',
            'tax'      => 'required',
            'service'      => 'required',
            'total'      => 'required',
        ],
            [                
                'user_id'     => 'User ID Is Required!',
                'sub_total'      => 'Sub Total Is Required!',
                'tax'      => 'Tax Is Required!',
                'service'      => 'Service Is Required!',
                'total'      => 'Total Is Required!',
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {             
            
            foreach ($request->input('products') as $product){
                $checkProduct = Product::where('id', $product['id'])->first();
                if($checkProduct->stock == 0){
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock Habis!',
                    ], 400);
                }elseif($checkProduct->stock < $product['qty']){
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock Yang Tersedia Hanya Ada '.$checkProduct->stock.'!',
                    ], 400);
                }
            }            

            $transaction = Transaction::create([                                                                
                'user_id'           => $request->input('user_id'),
                'sub_total'         => $request->input('sub_total'),
                'tax'               => $request->input('tax'),
                'service'           => $request->input('service'),
                'total'             => $request->input('total'),
                'status'            => 1,
            ]);

            if ($transaction) {
                if($request->input('products') && is_array($request->input('products'))){
                    foreach ($request->input('products') as $product){
                        $getProduct = Product::where('id', $product['id'])->first();
                        $detailTransaction = DetailTransaction::create([                                                                
                            'transaction_id'     => $transaction->id,
                            'product_id'         => $getProduct->id,
                            'price'              => $getProduct->price,
                            'qty'                => $product['qty'],                    
                        ]);

                        
                        $getProduct->update([                                                                                            
                            'stock' => ($getProduct->stock - $product['qty']),
                        ]);
                    }       

                    $delCart = Cart::where('user_id', $request->input('user_id'))->delete();
                }                         
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

    public function show($userId, $id, Request $request)
    {                               
        $transactions = DB::table('detail_transactions')        
        ->join('transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
        ->join('users', 'transactions.user_id', '=', 'users.id')
        ->join('products', 'detail_transactions.product_id', '=', 'products.id')
        ->select('detail_transactions.*', 'products.name', 'products.price')
        ->where('user_id', $userId)->where('transaction_id', $id)->where('status', 1)->get();
        
        $serTransactions = $this->serializeDetailTransaction($transactions, 'array');
        if ($serTransactions) {
            return response([
                'success'   => true,
                'message'   => 'List Detail Transaction',
                'data'      => $serTransactions
            ], 200);
        }else{
            return response([
                'success'   => true,
                'message'   => 'Data Not Found!',
                'data'      => []
            ], 200);
        }
    }

    public static function serializeTransaction($transactions, $type)
    {
        // error_log($transactions);
        $data = array();
        foreach ($transactions as $transaction){                        
            
            $code = 'TRX';
            if(strlen($transaction->id) == 1){
                $code = $code.'000';
            }elseif(strlen($transaction->id) == 2){
                $code = $code.'00';
            }elseif(strlen($transaction->id) == 3){
                $code = $code.'0';
            }

            $item =  array (
              'id'      => $transaction->id,
              'code' => $code.$transaction->id,
              'name'      => $transaction->name,
              'total'      => $transaction->total,              
              'tax'      => $transaction->tax,
              'service'      => $transaction->service,              
              'sub_total'      => $transaction->sub_total,
              'created_at'      => $transaction->created_at,
            );                        

            if ($type == 'array'){
                $data[] = $item;
            }else{
                $data = $item;
            }
        }
        return $data;
    }

    public static function serializeDetailTransaction($transactions, $type)
    {
        // error_log($transactions);
        $data = array();
        foreach ($transactions as $transaction){                        
            
            $data_image = array();
            $images = DB::table('product_images')
            ->where('product_id', $transaction->product_id)
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
              'id'      => $transaction->id,
              'name'      => $transaction->name,              
              'price'      => $transaction->price,
              'qty'      => $transaction->qty,     
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
