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

            $setting->merchantJson=$request->file('merchantJson')->store('credentials');

        }
        $setting->save();
        return back()->with('success','Settings Updated Successfully');
    }
}
