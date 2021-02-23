<?php

namespace App\Http\Controllers;

use App\Product;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use MOIREI\GoogleMerchantApi\Facades\ProductApi;
use MOIREI\GoogleMerchantApi\Facades\OrderApi;

class GoogleController extends Controller
{
    public function createProduct($Product)
    {

        $shop = Auth::user();
//        $Product=Product::find($id);
        $setting = Setting::where('shop', $shop->name)->first();
        if ($setting->mtnSku==true)
        {
            if(isset($Product->hasVariants[0]->sku))
            {
                $Product->mtn=$Product->hasVariants[0]->sku;
                $Product->save();
            }
        }
        $sizes = $Product->hasVariants->pluck('option1')->toArray();
        $sizes = collect($sizes)->unique()->toArray();

//        $prod=ProductApi::merchant([
//            'app_name' => $setting->storeName,
//            'merchant_id' => $setting->merchantId,
//            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
//        ])->delete(function ($product)use ($Product, $sizes){
//            $product->offerId($Product->shopify_id)
//                ->title($Product->title)
//                ->link('https://googlemerchant.myshopify.com/products/test-product')
//                ->imageLink('https://cdn.shopify.com/s/files/1/0526/9104/2478/products/product-03_1024x1024@2x.jpg')
//                ->channel('online')
//                ->targetCountry('US')
//                ->contentLanguage('en')
//                ->description('Good Products')
//                ->price(floatval($Product->hasVariants[0]->price))
//                ->availability('in stock');
//        })->catch(function ($e) {
//
//        });

        $matches = array('"{\*?\\.+(;})|\s?\\[A-Za-z0-9]+|\s?{\s?\\[A-Za-z0-9]+\s?|\s?}\s?"');
        $description = preg_replace($matches, '', $Product->body_html, -1, $count);
        $pro = ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
        ])->insert(function ($product) use ($Product, $description, $shop) {
            $product->offerId($Product->shopify_id)
                ->title($Product->title)
                ->link($shop->domain . '/products/' . $Product->handle)
                ->imageLink($Product->image)
                ->channel('online')
                ->targetCountry('US')
                ->contentLanguage('en')
                ->mpn($Product->mpn)
                ->description($description)
                ->googleProductCategory($Product->type)
                ->price(floatval($Product->hasVariants[0]->price))
                ->availability('in stock');
        })->then(function ($response) use ($Product) {
            $Product->googleProduct = json_encode($response);
            $Product->save();
        })->otherwise(function ($response) {
//            dd($response);
        })->catch(function ($e) {
//            dd($e);
        });

    }

    public function updateProduct($Product, $request,$type=null)
    {
        $shop = Auth::user();
        $setting = Setting::where('shop', $shop->name)->first();

        $sizes = $Product->hasVariants->pluck('option1')->toArray();

        //Delete the Previous One
        $matches = array('"{\*?\\.+(;})|\s?\\[A-Za-z0-9]+|\s?{\s?\\[A-Za-z0-9]+\s?|\s?}\s?"');
        $description = preg_replace($matches, '', $request->body_html, -1, $count);

        //Create Product Again
        $pro = ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
        ])->insert(function ($product) use ($Product, $request, $description,$type) {
            $product->offerId($Product->shopify_id)
                ->title($Product->title)
                ->link($Product->link)
                ->imageLink($Product->image)
                ->channel('online')
                ->targetCountry('US')
                ->contentLanguage('en')
                ->mpn($Product->mpn)
                ->description($description)
                ->googleProductCategory(($type!==null)?$type:$Product->type)
                ->price(floatval($Product->hasVariants[0]->price))
                ->availability('in stock');
        })->then(function ($response) use ($Product) {
            $Product->googleProduct = json_encode($response);
            $Product->save();
        })->otherwise(function ($response) {
        })->catch(function ($e) {
        });
    }

    public function deleteProduct($Product)
    {
        $shop = Auth::user();
        $setting = Setting::where('shop', $shop->name)->first();
        $matches = array('"{\*?\\.+(;})|\s?\\[A-Za-z0-9]+|\s?{\s?\\[A-Za-z0-9]+\s?|\s?}\s?"');
        $description = preg_replace($matches, '', $Product->body_html, -1, $count);
        $pro = ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
        ])->delete(function ($product) use ($Product, $description) {
            $product->offerId($Product->shopify_id)
                ->title($Product->title)
                ->link($Product->link)
                ->imageLink($Product->image)
                ->channel('online')
                ->targetCountry('US')
                ->contentLanguage('en')
                ->mpn($Product->mpn)
                ->description($description)
                ->googleProductCategory($Product->type)
                ->price(floatval($Product->hasVariants[0]->price))
                ->availability('in stock');
        })->then(function ($response) {
        })->otherwise(function ($response) {
        })->catch(function ($e) {
        });
    }

    public function BulkUpdate(Request $request)
    {
        $productids=$request->input('products');
        $titles=$request->input('titles');
        $description=$request->input('description');
        $type=$request->input('type');
        $mtn=$request->input('mtn');

        foreach (array_keys($productids) as $ID)
        {
            $product=Product::find($ID);
            $product->title=$titles[$ID];
            $product->description=$description[$ID];
            $product->type=$type[$ID];
            $product->mtn=$mtn[$ID];
            $product->save();
            $this->updateProduct($product,$request,$product->type);
        }
    }
}
