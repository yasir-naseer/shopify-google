<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $shop=Auth::user();
        $setting=Setting::where('shop',$shop->name)->first();
        if($setting===null)
        {
            $setting=new Setting();
            $setting->shop=$shop->name;
            $setting->save();
        }

        return view('products.settings',compact('setting'));
    }

    public function update($id,Request $request)
    {
        $setting=Setting::find($id);
        $setting->merchantId=$request->input('merchantId');
        $setting->storeName=$request->input('storeName');
        if ($request->hasFile('merchantJson'))
        {
            if ($setting->merchantJson!==null)
            {
                if(Storage::exists($setting->merchantJson))
                {
                    Storage::delete($setting->merchantJson);
                }
            }
            $name=$setting->merchantId.'.'.$request->file('merchantJson')->getClientOriginalExtension();
            $setting->merchantJson=$request->file('merchantJson')->storeAs('credentials',$name);

        }

        if ($request->input('shopifyUpdate'))
        {
            $setting->shopifyUpdate=true;
        }else
        {
            $setting->shopifyUpdate=false;
        }

        if ($request->input('googleUpdate'))
        {
            $setting->googleUpdate=true;
        }else
        {
            $setting->googleUpdate=false;
        }
        if ($request->input('googleWebhook'))
        {
            $setting->googleWebhook=true;
        }else
        {
            $setting->googleWebhook=false;
        }
        if ($request->input('mtnSku'))
        {
            $setting->mtnSku=true;
        }else
        {
            $setting->mtnSku=false;
        }
        $setting->save();
        return back()->with('success','Settings Updated Successfully');
    }
}
