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
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        ProductApi::merchant([
            'app_name' => $setting->storeName,
            'merchant_id' => $setting->merchantId,
            'client_credentials_path' => storage_path($setting->merchantJson)
        ])->insert(function($product) use($Product){
            $product->offerId($Product->product_id)
                ->title($Product->title)
                ->description($Product->body_html)
                ->price($Product->variants[0]->price)
                ->custom('purchase_quantity_limit', $Product->variants[0]->inventory_quantity)
//                ->availabilityDate( today()->addDays(7) );
            ;
        })->then(function($response){
            dd($response);
        })->otherwise(function($response){
            dd($response);
        })->catch(function($e){
            dd($e);
        });;
    }
}
