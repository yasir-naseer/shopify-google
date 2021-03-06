@extends('layouts.index')
@section('content')
    <div class="bg-body-light">
        <div class="content content-full pt-3 pb-3">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h5 my-2">
                    Settings
                </h1>
                <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Settings</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <form id="create_product_form" action="{{ route('settings.update',$setting->id) }}"
          class="form-horizontal {{--push-30-t--}} push-30" method="post" enctype="multipart/form-data">
        @csrf
        <div class="content">
            <div class="row">
                <div class="col-sm-6 col-md-6">
                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" class="form-control" name="storeName" value="{{$setting->storeName}}">
                    </div>
                    <div class="form-group">
                        <label>Google Merchant ID</label>
                        <input type="text" class="form-control" name="merchantId" value="{{$setting->merchantId}}">
                    </div>
                    <div class="form-group">
                        <label>Google Merchant Credentials(Json File)</label>
                        <input type="file" accept="application/json" class="form-control" name="merchantJson">
                        @if(\Illuminate\Support\Facades\Storage::exists($setting->merchantJson))
                            <p>Status: Active<img width="30px" style="display: inline-block"
                                                  src="{{asset('assets/active.svg')}}"/></p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="shopifyUpdate" value="1"
                                      @if($setting->shopifyUpdate==true) checked @endif/> Update on Shopify
                            Store</label>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="googleUpdate" value="1"
                                      @if($setting->googleUpdate==true) checked @endif/> Update on Google Merchant
                            Center</label>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="googleWebhook" value="1"
                                      @if($setting->googleWebhook==true) checked @endif/>Receive Webhook from Shopify for Google Merchant Center</label>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="mtnSku" value="1"
                                      @if($setting->mtnSku==true) checked @endif/>Assign Sku as Mtn Number for Google Merchant Center.</label>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <h5>Follow These Steps to Create API Connection and Download JSON file to upload here</h5>
                    <a href="{{asset('setting.png')}}" target="_blank" class="lightbox">
                        <img src="{{asset('setting.png')}}" width="100%" />
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection
