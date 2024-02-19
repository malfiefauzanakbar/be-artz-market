<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Config;
use App\Product;
use App\CategoryProduct;
use App\ProductImage;
use App\User;
use App\Helpers\AppHelper;

class ProductController extends Controller
{
    public function index(Request $request)
    {                        
        if ($request->input('category') != ''){
            $category = 'category_id = '.$request->input('category');
        }else {
            $category = 'category_id is not null';
        }            
        
        $products = DB::table('products')
        ->join('category_products', 'products.category_id', '=', 'category_products.id')
        ->select('products.*', 'category_products.name as category')
        ->whereRaw($category);        
        
        $type = "array";
        if ($request->input('slider') != ''){
            $products = $products->limit(1);
            $type = "object";
        }

        $products = $products->get();                

        if (!$products) {
            return response([
                'success'   => true,
                'message'   => 'Data Not Found!',
                'data'      => []
            ], 200);
        }

        $serProducts = $this->serializeProduct($products, $type);
        if ($serProducts) {
            return response([
                'success'   => true,
                'message'   => 'List Product',
                'data'      => $serProducts
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
            'name'     => 'required',
            'stock'     => 'required',
            'price'     => 'required',
            'expired_date'     => 'required',            
            'category_id'     => 'required',
            'images'     => 'required',
            'images.*' => 'image|mimes:jpeg,jpg,png|file|max:128000',            
            'description'     => 'required',
        ],
            [
                'name.required'    => 'Name Is Required!',           
                'stock.required'     => 'Stock Is Required!',
                'price.required'     => 'Price Is Required!',
                'expired_date.required'     => 'Expired Date Is Required!',     
                'category_id.required'    => 'Category Is Required!',
                'images.required'    => 'Image Is Required!',
                'images.max'    => 'Maximum Image Size Is 128MB!',
                'description.required'    => 'Description Is Required!',
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {                            

            $checkName = Product::where(DB::raw("LOWER(name)"), strtolower($request->input('name')))->first();

            if ($checkName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Name Already Exist!',
                ], 400);
            }

            //insert product
            $product = Product::create([
                'name'     => $request->input('name'),
                'stock'     => $request->input('stock'),
                'price'     => $request->input('price'),
                'expired_date'     => $request->input('expired_date'),
                'category_id'     => $request->input('category_id'),
                'description'     => $request->input('description')              
            ]);                            

            if ($product) {

                //upload image                        
                $images = $request->file('images');
                
                foreach ($images as $image) {
                    $timenow = Carbon::now();                    
                    $convtime = Carbon::createFromFormat('Y-m-d H:i:s', $timenow)->format('YmdHis');
                    $extension = $image->extension();          
                    $image_name = $convtime.Str::random(5).".".$extension;                    
                    $image->storeAs('public/product/', $image_name);

                    $cimage = ProductImage::create([
                        'image'     => $image_name,
                        'product_id'     => $product->id
                    ]);

                    if (!$cimage) {                    
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed Create Data Image!',
                        ], 400);
                    } 
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


    public function show($id, Request $request)
    {        
        
        $product = DB::table('products')
        ->join('category_products', 'products.category_id', '=', 'category_products.id')
        ->select('products.*', 'category_products.id as category_id', 'category_products.name as category')
        ->where('products.id', $id)
        ->get();
        $serProduct = $this->serializeProduct($product, 'object');
        if ($serProduct) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Product!',
                'data'    => $serProduct
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
            'name'     => 'required',       
            'stock'     => 'required',
            'price'     => 'required',
            'expired_date'     => 'required',       
            'category_id'     => 'required',
            'description'     => 'required',
        ],
            [
                'name.required'    => 'Name Is Required!',                
                'stock.required'     => 'Stock Is Required!',
                'price.required'     => 'Price Is Required!',
                'expired_date.required'     => 'Expired Date Is Required!',     
                'category_id.required'    => 'Category Is Required!',
                'description.required'    => 'Description Is Required!',
            ]
        );

        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please Fill The Required Fields!',
                'data'    => $validator->errors()
            ],400);

        } else {        
            
            $checkName = Product::where(DB::raw("LOWER(name)"), strtolower($request->input('name')))->where('id', '!=', $id)->first();

            if ($checkName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Name Already Exist!',
                ], 400);
            }

            $uproduct = Product::whereId($id)->update([
                'name'     => $request->input('name'),
                'stock'     => $request->input('stock'),
                'price'     => $request->input('price'),
                'expired_date'     => $request->input('expired_date'),
                'category_id'     => $request->input('category_id'),
                'description'     => $request->input('description')
            ]);                    

            if ($uproduct) {
                //upload image                        
                $images = $request->file('images');
                if ($images){
                    $imageProducts = ProductImage::where('product_id', $id)->get();                
                    foreach ($imageProducts as $imageProduct) {                    
                        Storage::disk('local')->delete('/public/product/'.$imageProduct->image);
                    }                
                    $delImage = ProductImage::where('product_id', $id)->delete();                                          
                
                    foreach ($images as $image) {
                        $timenow = Carbon::now();                    
                        $convtime = Carbon::createFromFormat('Y-m-d H:i:s', $timenow)->format('YmdHis');
                        $extension = $image->extension();          
                        $image_name = $convtime.Str::random(5).".".$extension;                    
                        $image->storeAs('public/product/', $image_name);
    
                        $cimage = ProductImage::create([
                            'image'     => $image_name,
                            'product_id'     => $id
                        ]);
    
                        if (!$cimage) {                    
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed Create Data Image!',
                            ], 400);
                        } 
                    }
                }                                                

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

        $product = Product::findOrFail($id);        
        $product->delete();

        $imageProducts = ProductImage::where('product_id', $id)->get();                
        foreach ($imageProducts as $imageProduct) {                    
            Storage::disk('local')->delete('/public/product/'.$imageProduct->image);
        }                
        $delImage = ProductImage::where('product_id', $id)->delete();        

        if ($product) {            
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

    public static function serializeProduct($products, $type)
    {
        // error_log($products);
        $data = array();        
        foreach ($products as $product){                         
            $data_image = array();
            $images = DB::table('product_images')
            ->where('product_id', $product->id)
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
              'id'      => $product->id,
              'name'      => $product->name,
              'stock'      => $product->stock,
              'price'      => $product->price,
              'expired_date'      => $product->expired_date,
              'category_id'      => $product->category_id,
              'category'   => $product->category,
              'images' => $data_image,
              'description' => $product->description
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
