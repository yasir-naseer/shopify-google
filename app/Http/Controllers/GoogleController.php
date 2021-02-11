<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MOIREI\GoogleMerchantApi\Facades\ProductApi;
use MOIREI\GoogleMerchantApi\Facades\OrderApi;

class GoogleController extends Controller
{
    public function createProduct($Product)
    {
        $shop = Auth::user();
        $setting = Setting::where('shop', $shop->name)->first();
        $sizes = $Product->hasVariants->pluck('option1')->toArray();
        $sizes = collect($sizes)->unique()->toArray();

        $pro = ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path('app/' . $setting->merchantJson)
        ])->insert(function ($product) use ($Product, $sizes) {
            $customAttr = new \Google_Service_ShoppingContent_CustomAttribute();
            $customAttr->setName('weight');
            $customAttr->setValue(10.01);
            $product->offerId($Product->shopify_id)
                ->title($Product->title)
                ->link('https://googlemerchant.myshopify.com/products/test-product')
                ->imageLink('https://cdn.shopify.com/s/files/1/0526/9104/2478/products/product-03_1024x1024@2x.jpg')
                ->channel('online')
                ->targetCountry('US')
                ->contentLanguage('en')
                ->description('Good Products')
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
}
