<?php

namespace App\Http\Controllers;

use App\Image;
use App\Jobs\ProductSyncJob;
use App\Product;
use App\ProductVariant;
use App\Setting;
use App\Jobs\GoogleUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private $helper;
    private $notify;
    private $log;

    /**
     * ProductController constructor.
     */
    public function __construct()
    {

    }

    public function index()
    {
        $shop = Auth::user();
        return view('products.create')->with([
            'shop' => $shop,
        ]);
    }

    public function all(Request $request)
    {
        $shop = Auth::user();

        $productQ = Product::query();
        if($request->has('search')){
            $productQ->where('title','LIKE','%'.$request->input('search').'%')->orWhereHas('hasVariants', function($q) use ($request) {
                $q->where('sku', 'LIKE', '%' . $request->input('search') . '%');
            });
        }


        return view('welcome')->with([
            'products' => $productQ->orderBy('created_at','DESC')->paginate(20),
            'search' =>$request->input('search'),
            'shop' => $shop,
        ]);
    }

    public function view($id)
    {
        $product = Product::with(['has_images', 'hasVariants'])->find($id);
        return view('products.product')->with([
            'product' => $product
        ]);
    }



    public function Edit($id)
    {
        $product = Product::with(['has_images', 'hasVariants'])->find($id);
        $shop = Auth::user();


        return view('products.edit')->with([
            'product' => $product,
            'shop' => $shop,
        ]);
    }



    public function productAddImages(Request $request, $id) {

        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }

        $product = Product::find($id);
        $woocommerce =$this->helper->getWooCommerceAdminShop();
        if($product != null) {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $destinationPath = 'images/';
                    $filename = now()->format('YmdHi') . str_replace([' ','(',')'], '-', $image->getClientOriginalName());
                    $image->move($destinationPath, $filename);
                    $image = new Image();
                    $image->isV = 0;
                    $image->product_id = $product->id;
                    $image->image = $filename;
                    $image->position = count($product->has_images) + $index+1;
                    $image->save();
                }


                $images_array = [];
                $product = Product::find($id);
                foreach ($product->has_images as $index => $image) {
                    if ($image->isV == 0) {
                        $src = asset('images') . '/' . $image->image;
                    }
                    else {
                        $src = asset('images/variants') . '/' . $image->image;
                    }

                    array_push($images_array, [
                        'alt' => $product->title . '_' . $index,
                        'name' => $product->title . '_' . $index,
                        'src' => $src,
                    ]);
                }

                $productdata = [
                    "images" => $images_array,
                ];

                /*Updating Product On Woocommerce*/
                $response = $woocommerce->put('products/'.$product->woocommerce_id, $productdata);

                $woocommerce_images = $response->images;

                if (count($woocommerce_images) == count($product->has_images)) {
                    foreach ($product->has_images as $index => $image) {
                        $image->woocommerce_id = $woocommerce_images[$index]->id;
                        $image->save();
                    }
                }

            }
            $product->save();
            $this->log->store(0, 'Product', $product->id, $product->title,'Product Image Added');
            return redirect()->back()->with('success', 'Product Updated Successfully');
        }
    }

    public function updateProductStatus(Request $request, $id) {

        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $product = Product::find($id);
        $shop =$this->helper->getShop();

        $this->product_status_change($request, $product);
        $this->log->store(0, 'Product', $product->id, $product->title,'Product Status Updated');

    }


    public function UpdateGlobal(Request $request,$id)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==true)
        {
            $this->update($request,$id);
        }
        if ($setting->googleUpdate==true)
        {
            $google=new GoogleController();
            $product = Product::find($id);
            $google->updateProduct($product,$request);
        }
        return back()->with('success','Product Updated');
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $shop = Auth::user();
        if ($product != null) {
            foreach($request->type as $type) {
                if ($type == 'basic-info') {
                    $product->title = $request->title;
                    $product->description = $request->description;
                    $product->save();
                    $productdata = [
                        "product" => [
                            "title" => $request->title,
                            "body_html" => $request->description,
                        ]
                    ];

                    $resp =  $shop->api()->rest('PUT', '/admin/products/'.$product->shopify_id.'.json',$productdata);
                }

                else if ($type == 'pricing') {
                    $product->price = $request->price;
                    $product->compare_price = $request->compare_price;
                    $product->cost = $request->cost;
                    $product->quantity = $request->quantity;
                    $product->weight = $request->weight;
                    $product->sku = $request->sku;
                    $product->barcode = $request->barcode;
                    $product->save();


                    if (count($product->hasVariants) == 0) {
                        $response = $shop->api()->rest('GET', '/admin/products/' . $product->shopify_id .'.json');
                        if(!$response->errors){
                            $shopifyVariants = $response->body->product->variants;
                            $variant_id = $shopifyVariants[0]->id;
                            $i = [
                                'variant' => [
                                    'price' =>$product->price,
                                    'sku' =>  $product->sku,
                                    'grams' => $product->weight * 1000,
                                    'weight' => $product->weight,
                                    'weight_unit' => 'kg',
                                    'barcode' => $product->barcode,
                                ]
                            ];

                            $shop->api()->rest('PUT', '/admin/variants/' . $variant_id .'.json', $i);
                        }

                    }

                }

                else if ($type == 'pricing-for-variant') {
                    $product->price = $request->price;
                    $product->quantity = $request->quantity;
                    $product->weight = $request->weight;
                    $product->save();


                    if (count($product->hasVariants) == 0) {
                        $response = $shop->api()->rest('GET', '/admin/products/' . $product->shopify_id .'.json');
                        if(!$response->errors){
                            $shopifyVariants = $response->body->product->variants;
                            $variant_id = $shopifyVariants[0]->id;
                            $i = [
                                'variant' => [
                                    'price' =>$product->price,
                                    'sku' =>  $product->sku,
                                    'grams' => $product->weight * 1000,
                                    'weight' => $product->weight,
                                    'weight_unit' => 'kg',
                                    'barcode' => $product->barcode,

                                ]
                            ];
                            $shop->api()->rest('PUT', '/admin/variants/' . $variant_id .'.json', $i);
                        }

                    }

                }

                else if ($type == 'single-variant-update') {
                    foreach ($request->variant_id as $id) {
                        $variant = ProductVariant::find($id);
                        $variant->title = $request->input('option1-'.$id) . '/' . $request->input('option2-'.$id) . '/' . $request->input('option3-'.$id);
                        $variant->option1 = $request->input('option1-'.$id);
                        $variant->option2 = $request->input('option2-'.$id);
                        $variant->option3 = $request->input('option3-'.$id);
                        $variant->price = $request->input('single-var-price-'.$id);
                        //$variant->cost = $request->input('single-var-cost-'.$id);
                        //$variant->compare_price = $request->input('compare_price');
                        $variant->quantity = $request->input('single-var-quantity-'.$id);
                        $variant->sku = $request->input('single-var-sku-'.$id);
                        $variant->barcode = $request->input('single-var-barcode-'.$id);

                        $variant->product_id = $product->id;
                        $variant->save();

                        $productdata = [
                            "variant" => [
                                'title' => $variant->title,
                                'sku' => $variant->sku,
                                'option1' => $variant->option1,
                                'option2' => $variant->option2,
                                'option3' => $variant->option3,
                                'grams' => $product->weight * 1000,
                                'weight' => $product->weight,
                                'weight_unit' => 'kg',
                                'barcode' => $variant->barcode,
                                'price' => $variant->price,
                                'cost' => $variant->cost,
                            ]
                        ];
                        $resp =  $shop->api()->rest('PUT', '/admin/products/'.$product->shopify_id.'/variants/'.$variant->shopify_id.'.json',$productdata);
                    }
                }




                else if ($type == 'organization') {
                    $product->type = $request->product_type;
                    $product->vendor = $request->vendor;
                    $product->tags = $request->tags;
                    $product->save();

                    $productdata = [
                        "product" => [
                            "vendor" => $request->vendor,
                            "product_type" => $request->product_type,
                            "tags" => $product->tags,
                        ]
                    ];
                    $resp =  $shop->api()->rest('PUT', '/admin/products/'.$product->shopify_id.'.json',$productdata);
                }
            }
        }

        return redirect()->back()->with('success', 'Product Updated Successfully');
    }

    public function updateExistingProductNewVariants(Request $request, $id) {

        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }

        $product = Product::find($id);
        $shop = Auth::user();
        if($product != null) {
            if ($request->type == 'existing-product-new-variants') {
                    if ($request->variants) {
                        $product->variants = $request->variants;
                    }
                    $product->save();
                    $this->ProductVariants($request, $product->id);
                    $variants_array =  $this->variants_template_array($product);

                    $productdata = [
                        "product" => [
                            "options" => $this->options_update_template_array($product),
                            "variants" => $variants_array,
                        ]
                    ];
                    $resp =  $shop->api()->rest('PUT', '/admin/products/'.$product->shopify_id.'.json',$productdata);
                    $shopifyVariants = $resp->body->product->variants;
                    foreach ($product->hasVariants as $index => $v){
                        $v->shopify_id = $shopifyVariants[$index]->id;
                        //$v->inventory_item_id = $shopifyVariants[$index]->inventory_item_id;
                        $v->save();
                    }
                    return redirect()->route('product.edit', $product->id)->with('success', 'Product Variants Updated Successfully');

                }
            return redirect()->route('product.edit', $product->id)->with('error', 'Something went wrong');

        }
        return redirect()->route('product.edit', $product->id)->with('error', 'Something went wrong');

    }

    public function updateExistingProductOldVariants(Request $request, $id) {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $product = Product::find($id);
        $shop = Auth::user();
        if($product != null) {
            if($request->type == 'existing-product-update-variants') {

                    $product->variants = 1;
                    $product->save();
                    $variants_array = $this->ProductVariantsUpdate($request, $product->id, $product);

                    sleep(3);

                    $options_array = [];

                    $option1_array = [];
                    foreach ($variants_array as $index => $v) {
                        array_push($option1_array, $v['option1']);
                    }

                    $option1_array_unique = array_unique($option1_array);

                    if($option1_array_unique[0] != '') {
                        $temp = [];
                        foreach ($option1_array_unique as $a) {
                            array_push($temp, $a);
                        }
                        array_push($options_array, [
                            'name' => 'Option1',
                            'position' => '1',
                            'values' => $temp,
                        ]);

                    }


                    $option2_array = [];
                    foreach ($variants_array as $index => $v) {
                        array_push($option2_array, $v['option2']);
                    }

                    $option2_array_unique = array_unique($option2_array);

                    if($option2_array_unique[0] != '') {
                        $temp = [];
                        foreach ($option2_array_unique as $a) {
                            array_push($temp, $a);
                        }

                        array_push($options_array, [
                            'name' => 'Option2',
                            'position' => '2',
                            'values' => $temp,
                        ]);
                    }


                    $option3_array = [];
                    foreach ($variants_array as $index => $v) {
                        array_push($option3_array, $v['option3']);
                    }

                    $option3_array_unique = array_unique($option3_array);

                    if($option3_array_unique[0] != '') {
                        $temp = [];
                        foreach ($option3_array_unique as $a) {
                            array_push($temp, $a);
                        }

                        array_push($options_array, [
                            'name' => 'Option3',
                            'position' => '3',
                            'values' => $temp,
                        ]);
                    }

                    $productdata = [
                        "product" => [
                            "options" => $options_array,
                            "variants" => $variants_array,
                        ]
                    ];


                    $resp =  $shop->api()->rest('PUT', '/admin/products/'.$product->shopify_id.'.json',$productdata);
                    $shopifyVariants = $resp->body->product->variants;

                    $product = Product::find($id);
                    foreach ($product->hasVariants as $index => $v){
                        $v->shopify_id = $shopifyVariants[$index]->id;
                        $v->inventory_item_id = $shopifyVariants[$index]->inventory_item_id;
                        $v->save();
                    }
                    return redirect()->route('product.edit', $product->id)->with('success', 'Product Variants Updated Successfully');

            }
            return redirect()->route('product.edit', $product->id)->with('error', 'Something went wrong');
        }
        return redirect()->route('product.edit', $product->id)->with('error', 'Something went wrong');

    }


    public function deleteExistingProductImage(Request $request, $id) {

        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $product = Product::find($id);
        $shop =$this->helper->getShop();

        if($product != null) {
            $image =  Image::find($request->input('file'));
            $shop->api()->rest('DELETE', '/admin/products/' . $product->shopify_id . '/images/'.$image->shopify_id.'.json');
            $image->delete();

            return response()->json([
                'success' => 'ok'
            ]);
        }

    }


    public function save(Request $request)
    {

        if (Product::where('title', $request->title)->exists()) {
            $product = Product::where('title', $request->title)->first();
        } else {
            $product = new Product();
        }
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->type = $request->product_type;
        $product->vendor = $request->vendor;
        $product->tags = $request->tags;
        $product->quantity = $request->quantity;
        $product->weight = $request->weight;
        $product->sku = $request->sku;
        $product->barcode = $request->barcode;
        $product->status =  $request->input('status');
        $product->shop_id = Auth::user()->id;

        if ($request->variants) {
            $product->variants = $request->variants;
        }
        $product->save();

        if ($request->variants) {
            $this->ProductVariants($request, $product->id);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $destinationPath = 'images/';
                $filename = now()->format('YmdHi') . str_replace([' ','(',')'], '-', $image->getClientOriginalName());
                $image->move($destinationPath, $filename);

                $image = new Image();
                $image->isV = 0;
                $image->product_id = $product->id;
                $image->image = $filename;
                $image->save();
            }

        }

        $product->save();
        return redirect()->route('import_to_shopify',$product->id);
    }


    public function ProductVariants($data, $id)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }

        for ($i = 0; $i < count($data->variant_title); $i++) {
            $options = explode('/', $data->variant_title[$i]);
            $variants = new  ProductVariant();
            if (!empty($options[0])) {
                $variants->option1 = $options[0];
            }
            if (!empty($options[1])) {
                $variants->option2 = $options[1];
            }
            if (!empty($options[2])) {
                $variants->option3 = $options[2];
            }
            $variants->title = $data->variant_title[$i];
            $variants->price = $data->variant_price[$i];
            $variants->compare_price = $data->variant_comparePrice[$i];
            $variants->quantity = $data->variant_quantity[$i];
            $variants->sku = $data->variant_sku[$i];
            $variants->barcode = $data->variant_barcode[$i];
            $variants->product_id = $id;
            $variants->save();
        }
    }

    public function ProductVariantsUpdate($data, $id, $product)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $woocommerce = $this->helper->getAdminShop();

        $product = Product::find($id);
        foreach ($product->hasVariants as $v){
            $res = $woocommerce->delete('products/'.$product->woocommerce_id.'/variations/'.$v->woocommerce_id, ['force' => true]);
            $v->delete();
        }

        $product = Product::find($id);
        $product->hasVariants()->delete();

        for ($i = 0; $i < count($data->variant_title); $i++) {

            $variants = new ProductVariant();
            $options = explode('/', $data->variant_title[$i]);

            if (!empty($options[0])) {
                $variants->option1 = $options[0];
            }
            if (!empty($options[1])) {
                $variants->option2 = $options[1];
            }
            if (!empty($options[2])) {
                $variants->option3 = $options[2];
            }
            $variants->title = $data->variant_title[$i];
            $variants->price = $data->variant_price[$i];
            $variants->compare_price = $data->variant_comparePrice[$i];
            $variants->quantity = $data->variant_quantity[$i];
            if($data->variant_cost[$i] == null) {
                $variants->cost = null;
            }
            else {
                $variants->cost = $data->variant_cost[$i];
            }
            $variants->sku = $data->variant_sku[$i];
            $variants->barcode = $data->variant_barcode[$i];
            $variants->product_id = $id;
            $variants->save();

        }
    }


    public function delete($id)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==true)
        {
            $product = Product::find($id);
            $shop = Auth::user();
            $shop->api()->rest('DELETE', '/admin/products/'.$product->shopify_id.'.json');
            $variants = ProductVariant::where('product_id', $id)->get();
            foreach ($variants as $variant) {
                $variant->delete();
            }
            foreach ($product->has_images as $image){
                $image->delete();
            }

            $product->delete();
        }
        if ($setting->googleUpdate==true)
        {
            $google=new GoogleController();
            $google->deleteProduct($product);
        }
    }

    public function add_existing_product_new_variants(Request $request)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $product = Product::find($request->id);
        if ($product->varaints == 0) {
            return view('products.add_existing_product_new_variants')->with([
                'product' => $product
            ]);
        } else {
            return redirect('/products');
        }
    }

    public function update_existing_product_new_variants(Request $request)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $product = Product::find($request->id);
        if ($product->varaints !== 0) {
            return view('products.update_existing_product_new_variants')->with([
                'product' => $product
            ]);
        } else {
            return redirect('/products');
        }
    }

    public function import_to_shopify(Request $request)
    {
        $product = Product::find($request->id);
        if ($product != null) {
            $variants_array = [];
            $options_array = [];
            $images_array = [];
            //converting variants into shopify api format
            $variants_array =  $this->variants_template_array($product,$variants_array);
            /*Product Options*/
            $options_array = $this->options_template_array($product,$options_array);
            /*Product Images*/

            foreach ($product->has_images as $index => $image) {
                if ($image->isV == 0) {
                    $src = asset('images') . '/' . $image->image;
                } else {
                    $src = asset('images/variants') . '/' . $image->image;
                }
                array_push($images_array, [
                    'alt' => $product->title . '_' . $index,
                    'position' => $index + 1,
                    'src' => $src,
                ]);
            }
            $shop = Auth::user();
            /*Categories and Subcategories*/
            $tags = $product->tags;


            if($product->status == 1){
                $published = true;
            }
            else{
                $published = false;
            }

            if($product->type != null){
                $product_type = $product->type;
            }
            else{
                $product_type = 'None';
            }


            $productdata = [
                "product" => [
                    "title" => $product->title,
                    "body_html" => $product->description,
                    "vendor" => $product->vendor,
                    "tags" => $tags,
                    "product_type" => $product_type,
                    "variants" => $variants_array,
                    "options" => $options_array,
                    "images" => $images_array,
                    "status"=>  $published
                ]
            ];



            $response = $shop->api()->rest('POST', '/admin/products.json', $productdata);
            $response = json_decode(json_encode($response,1));
            $product_shopify_id =  $response->body->product->id;
            $product->shopify_id = $product_shopify_id;
            $price = $product->price;
            $product->save();

            $shopifyImages = $response->body->product->images;
            $shopifyVariants = $response->body->product->variants;

            if(count($product->hasVariants) == 0){

                $variant_id = $shopifyVariants[0]->id;

                $product->save();
                $i = [
                    'variant' => [
                        'price' =>$price,
                        'sku' =>  $product->sku,
                        'grams' => $product->weight * 1000,
                        'weight' => $product->weight,
                        'weight_unit' => 'kg',
                        'barcode' => $product->barcode,
                    ]
                ];
                $shop->api()->rest('PUT', '/admin/variants/' . $variant_id .'.json', $i);

                // $data = [
                //     "inventory_item" => [
                //         'id' => $product->inventory_item_id,
                //         "tracked" => true
                //     ]

                // ];
                // $resp = $shop->api()->rest('PUT', '/admin/api/2020-07/inventory_items/' . $product->inventory_item_id . '.json', $data);
                // /*Connect to Wefullfill*/
                // $data = [
                //     'location_id' => 46023344261,
                //     'inventory_item_id' => $product->inventory_item_id,
                //     'relocate_if_necessary' => true
                // ];
                // $res = $shop->api()->rest('POST', '/admin/api/2020-07/inventory_levels/connect.json', $data);
                // /*Set Quantity*/

                // $data = [
                //     'location_id' => 46023344261,
                //     'inventory_item_id' => $product->inventory_item_id,
                //     'available' => $product->quantity,

                // ];

                // $res = $shop->api()->rest('POST', '/admin/api/2020-07/inventory_levels/set.json', $data);
            }
            foreach ($product->hasVariants as $index => $v){
                $v->shopify_id = $shopifyVariants[$index]->id;
                // $v->inventory_item_id =$shopifyVariants[$index]->inventory_item_id;
                $v->save();
            }

            if(count($shopifyImages) == count($product->has_images)){
                foreach ($product->has_images as $index => $image){
                    $image->shopify_id = $shopifyImages[$index]->id;
                    $image->save();
                }
            }

            foreach ($product->hasVariants as $index => $v){
                if($v->has_image != null){
                    $i = [
                        'image' => [
                            'id' => $v->has_image->shopify_id,
                            'variant_ids' => [$v->shopify_id]
                        ]
                    ];
                    $imagesResponse = $shop->api()->rest('PUT', '/admin/products/' . $product_shopify_id . '/images/' . $v->has_image->shopify_id . '.json', $i);
                }
            }
            return redirect()->route('product.view',$product->id)->with('success','Product Generated and Push to Store Successfully!');
        }
    }

    public function delete_three_options_variants(Request $request, $product)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $deleted_variants = $product->hasVariants()->whereIn('option1', $request->input('delete_option1'))
            ->whereIn('option2', $request->input('delete_option2'))
            ->whereIn('option3', $request->input('delete_option3'))->get();
        $this->delete_variants($deleted_variants);
        return $deleted_variants;
    }

    public function delete_two_options_variants(Request $request, $product)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $deleted_variants = $product->hasVariants()->whereIn('option1', $request->input('delete_option1'))
            ->whereIn('option2', $request->input('delete_option2'))->get();
        $this->delete_variants($deleted_variants);
        return $deleted_variants;
    }

    public function delete_variants($variants){
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        foreach ($variants as $variant){
            $variant->delete();
        }
    }

    public function variants_template_array($product){
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $variants_array = [];
        foreach ($product->hasVariants as $index => $varaint) {
            array_push($variants_array, [
                'title' => $varaint->title,
                'sku' => $varaint->sku,
                'option1' => $varaint->option1,
                'option2' => $varaint->option2,
                'option3' => $varaint->option3,
                'inventory_quantity' => $varaint->quantity,
                'grams' => $product->weight * 1000,
                'weight' => $product->weight,
                'weight_unit' => 'kg',
                'barcode' => $varaint->barcode,
                'price' => $varaint->price,
                'cost' => $varaint->cost,
            ]);
        }
        return $variants_array;
    }

    public function options_template_array($product){

        $options_array = [];
        if (count($product->option1($product)) > 0) {
            $temp = [];
            foreach ($product->option1($product) as $a) {
                array_push($temp, $a);
            }
            array_push($options_array, [
                'name' => 'Option1',
                'position' => '1',
                'values' => json_encode($temp),
            ]);
        }
        if (count($product->option2($product)) > 0) {
            $temp = [];
            foreach ($product->option2($product) as $a) {
                array_push($temp, $a);
            }
            array_push($options_array, [
                'name' => 'Option2',
                'position' => '2',
                'values' => json_encode($temp),
            ]);
        }
        if (count($product->option3($product)) > 0) {
            $temp = [];
            foreach ($product->option3($product) as $a) {
                array_push($temp, $a);
            }
            array_push($options_array, [
                'name' => 'Option3',
                'position' => '3',
                'values' => json_encode($temp),
            ]);
        }
        return $options_array;
    }

    public function options_update_template_array($product){

        $options_array = [];
        if (count($product->option1($product)) > 0) {
            $temp = [];
            foreach ($product->option1($product) as $a) {
                array_push($temp, $a);
            }
            array_push($options_array, [
                'name' => 'Option1',
                'position' => '1',
                'values' => $temp,
            ]);
        }
        if (count($product->option2($product)) > 0) {
            $temp = [];
            foreach ($product->option2($product) as $a) {
                array_push($temp, $a);
            }
            array_push($options_array, [
                'name' => 'Option2',
                'position' => '2',
                'values' => $temp,
            ]);
        }
        if (count($product->option3($product)) > 0) {
            $temp = [];
            foreach ($product->option3($product) as $a) {
                array_push($temp, $a);
            }
            array_push($options_array, [
                'name' => 'Option3',
                'position' => '3',
                'values' => $temp,
            ]);
        }
        return $options_array;
    }


    /**
     * @param Request $request
     * @param $product
     * @param $shop
     */
    public function product_status_change(Request $request, $product)
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $product->status = $request->input('status');
        $product->save();
        if($product->status == 1)
            $published = true;
        else
            $published = false;


        $productdata = [
            'product' => [
                "published"=>  $published,
            ]
        ];

        $shop = Auth::user();
        $shop->api()->rest('admin/products/'.$product->shopify_id . '.json', $productdata);

    }

    public function change_image($id,$image_id,Request $request){

        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        if($request->input('type') == 'product'){
            $shop = Auth::user();
            $variant = ProductVariant::find($id);
            if($variant->linked_product != null) {
                if ($variant->linked_product->shopify_id != null) {
                    $image = Image::find($image_id);
                    return $this->shopify_image_selection($image_id, $image, $shop, $variant);
                }
                else{
                    return response()->json([
                        'message' => 'false'
                    ]);
                }
            }
            else{
                return response()->json([
                    'message' => 'false'
                ]);
            }
        }

    }

    public function shopify_image_selection($image_id, $image, $shop, $variant)
    {
        $variant_ids = [];
        foreach ($image->has_variants as $v) {
            array_push($variant_ids, $v->shopify_id);
        }
        array_push($variant_ids,$variant->shopify_id);
        $i = [
            'image' => [
                'id' => $image->shopify_id,
                'variant_ids' => $variant_ids
            ]
        ];
        $imagesResponse = $shop->api()->rest('PUT', '/admin/products/' . $variant->linked_product->shopify_id . '/images/' . $image->shopify_id . '.json', $i);
        $imagesResponse = json_decode(json_encode($imagesResponse, 1));
        if (!$imagesResponse->errors) {
            $variant->image = $image_id;
            $variant->save();
            return response()->json([
                'message' => 'success'
            ]);
        } else {
            return response()->json([
                'message' => 'false'
            ]);
        }
    }

    public function update_image_position(Request $request){

        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if ($setting->shopifyUpdate==false)
        {
            return back()->with('error','Enable Settings for Shopify');
        }
        $positions = $request->input('positions');
        $product = $request->input('product');
        $images_array = [];
        $shop = $this->helper->getShop();
        foreach ($positions as $index => $position){
            $image = Image::where('product_id',$product)
                ->where('id',$position)->first();
            array_push($images_array, [
                'id' => $image->shopify_id,
                'position' => $index + 1,
            ]);
        }

        $related_product = Product::find($product);
        if($related_product != null){
            $data = [
                'product' => [
                    'images' => $images_array
                ]
            ];
            $imagesResponse = $shop->api()->rest('PUT', '/admin/products/' . $related_product->shopify_id .'.json', $data);
            $imagesResponse = json_decode(json_encode($imagesResponse, 1));
            if(!$imagesResponse->errors){
                foreach ($positions as $index => $position){
                    $image = Image::where('product_id',$product)
                        ->where('id',$position)->first();
                    $image->position = $index + 1;
                    $image->save();
                }
                return response()->json([
                    'message' => 'success',
                ]);
            }else{
                return response()->json([
                    'message' => 'error'
                ]);
            }

        }
        else{
            return response()->json([
                'message' => 'error'
            ]);
        }
    }

    public function storeProducts($next = null)
    {
        $shop = Auth::user();
        $products = $shop->api()->rest('GET', '/admin/products.json', [
            'limit' => 250,
            'page_info' => $next
        ]);

        $products = json_decode(json_encode($products, 1));

        if(!$products->errors){
            foreach ($products->body->products as $product) {
                ProductSyncJob::dispatch($product,$shop);
            }

            if (isset($products->link->next)) {
                $this->storeProducts($products->link->next);
            }
        }

        return redirect()->back()->with('success', 'Products Synced Successfully');
    }

    public function createProduct($product,$shop=null)
    {
        if (Product::where('shopify_id', $product->id)->exists()) {
            $p = Product::where('shopify_id', $product->id)->first();
        } else {
            $p = new Product();
        }

        $p->title = $product->title;
        if($shop===null)
        {
            $p->shop_id = Auth::user()->id;
        }else
        {
            $p->shop_id = $shop->id;
        }
        $p->shopify_id = $product->id;
        if($product->handle)
        {
            $p->handle=$product->handle;
        }
        if(isset($product->image->src))
        {
            $p->image=$product->image->src;
        }
        $p->description = $product->body_html;
        $p->price = $product->variants[0]->price;
        $p->compare_price = $product->variants[0]->compare_at_price;
        $p->type = $product->product_type;
        $p->vendor = $product->vendor;
        $p->tags = $product->tags;
        //$p->quantity = $product->quantity;
        $p->weight = $product->variants[0]->weight;
        $p->sku = $product->variants[0]->sku;
        $p->barcode = $product->variants[0]->barcode;
        $p->status =  $product->published_at == null ? 0 : 1;

        if (count($product->variants) > 0) {
            $p->variants = 1;
        }
        $p->save();

        $count_product_images = count($product->images);


        if (count($product->images) > 0) {
            foreach ($product->images as $index => $img) {
                $image = file_get_contents($img->src);
                $filename = now()->format('YmdHi') . $p->id . rand(12321, 456546464) . '.jpg';
                file_put_contents(public_path('images/' . $filename), $image);
                $image = new Image();
                $image->isV = 0;
                $image->position = $index + 1 + $count_product_images;
                $image->product_id = $p->id;
                $image->shopify_id = $img->id;
                $image->image = $filename;
                $image->save();
            }
        }


        if ($product->variants) {
            foreach($product->variants as $i => $v) {
                $variants = new  ProductVariant();
                $variants->option1 = $v->option1;
                $variants->option2 = $v->option2;
                $variants->option3 = $v->option3;
                $variants->title = $v->title;
                $variants->price = $v->price;
                $variants->compare_price = $v->compare_at_price;
                $variants->quantity = $v->inventory_quantity;
                $variants->sku = $v->sku;
                $variants->barcode = $v->barcode;
                $variants->product_id = $p->id;
                $variants->image = $v->image_id;
                $variants->shopify_id = $v->id;
                $variants->save();

                if(count($product->variants) > 0) {
                    if ($product->variants[$i]->image_id != null) {
                        $image_linked = $p->has_images()->where('shopify_id', $product->variants[$i]->image_id)->first();
                        if($image_linked != null) {
                            $variants->image = $image_linked->id;
                            $variants->save();
                        }
                    }
                }
            }
        }


        $p->quantity = $p->hasVariants->sum('quantity');
        $p->save();

        $google=new GoogleController();
        $google->createProduct($p);
    }

    public function bulkEdit(Request $request)
    {
        $ids=$request->input('products');
        $ids=explode(',',$ids);
        $products=Product::whereIn('id',$ids)->cursor();
        return view('products.bulkEdit',compact('products'));
    }

    public function bulkUpdate(Request $request)
    {
        $google=new GoogleController();
        $titles=$request->input('title');
        $types=$request->input('type');
        $description=$request->input('description');
        $productids=array_keys($request->input('title'));
        foreach ($productids as $productid)
        {
            $product=Product::find($productid);
            $product->type=$types[$productid];
            $product->title=$titles[$productid];
            $product->description=$description[$productid];
            if($product->save())
            {
                GoogleUpdate::dispatch($product,$request);
                $google->updateProduct($product,$request);
            }
        }
        return redirect()->route('home')->with('mgs','Products Updated on Google Merchant Center!');
    }

    public function updateProductAll($id,Request $request)
    {
        $product=Product::find($id);
        $shop=Auth::user();
        $setting = Setting::where('shop', $shop->name)->first();
        if ($setting->googleUpdate==true)
        {
            $google=new GoogleController();
            $google->updateProduct($product,$request);
        }
        if ($setting->shopifyUpdate==true)
        {

        }
        return redirect()->back();
    }

}
