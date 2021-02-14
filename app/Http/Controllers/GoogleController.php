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
        $description = preg_replace($matches,'',$Product->body_html, -1, $count);
        $pro = ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
        ])->insert(function ($product) use ($Product,$description,$shop) {
            $product->offerId($Product->shopify_id)
                ->title($Product->title)
                ->link($shop->domain.'/products/'.$Product->handle)
                ->imageLink($product->image)
                ->channel('online')
                ->targetCountry('US')
                ->contentLanguage('en')
                ->description($description)
                ->price(floatval($Product->hasVariants[0]->price))
                ->availability('in stock');
        })->then(function ($response) {
            dd($response);
        })->otherwise(function ($response) {
            dd($response);
        })->catch(function ($e) {
            dd($e);
        });

    }

    public function updateProduct($Product,$request)
    {
        $shop = Auth::user();
        $setting = Setting::where('shop', $shop->name)->first();
        $sizes = $Product->hasVariants->pluck('option1')->toArray();

        //Delete the Previous One
        $matches = array('"{\*?\\.+(;})|\s?\\[A-Za-z0-9]+|\s?{\s?\\[A-Za-z0-9]+\s?|\s?}\s?"');
        $description = preg_replace($matches,'',$request->body_html, -1, $count);
//        $prod = ProductApi::merchant([
//            'app_name' => $setting->storeName,
//            'merchant_id' => $setting->merchantId,
//            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
//        ])->delete(function ($product) use ($Product, $sizes,$description) {
//            $product->offerId($Product->shopify_id)
//                ->title($Product->title)
//                ->link('https://googlemerchant.myshopify.com/products/test-product')
//                ->imageLink('https://cdn.shopify.com/s/files/1/0526/9104/2478/products/product-03_1024x1024@2x.jpg')
//                ->channel('online')
//                ->targetCountry('US')
//                ->contentLanguage('en')
//                ->description($description)
//                ->price(floatval($Product->hasVariants[0]->price))
//                ->availability('in stock');
//        })->catch(function ($e) {
//
//        });

        //Create Product Again
        $pro = ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
        ])->insert(function ($product) use ($Product,$request,$description) {
            $product->offerId($Product->shopify_id)
                ->title($request->title)
                ->link($request->link)
                ->imageLink('https://cdn.shopify.com/s/files/1/0073/9065/8618/products/Men-Shorts-7_-Liner-HIFLEX___Pastel-Blue_P5_Centric-Aug2020-429_540x.png?v=1599268809')
                ->channel('online')
                ->targetCountry('US')
                ->contentLanguage('en')
                ->description($description)
                ->price(floatval($request->price))
                ->availability('in stock');
        })->then(function ($response) {
            dd($response);
        })->otherwise(function ($response) {
            dd($response);
        })->catch(function ($e) {
            dd($e);
        });
        dd($pro);
    }
}
