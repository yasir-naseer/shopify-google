@extends('layouts.index')
@section('content')
    <style>
        iframe{
            width: 100%;
        }
    </style>
    <div class="bg-body-light">
        <div class="content content-full pt-2 pb-2">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h4 my-2">
                    {{ \Illuminate\Support\Str::limit($product->title,20,'...') }}
                </h1>
                <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Products</a>
                        </li>
                        <li class="breadcrumb-item">  {{ \Illuminate\Support\Str::limit($product->title,20,'...') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="row mb2">
            <div class="col-sm-6">
            </div>
            @if($product->import_from_shopify != 1)
                <div class="col-sm-6 text-right">
                    <a href="{{ route('product.edit',$product->id) }}" class="btn btn-primary btn-square ">Edit Product</a>
                </div>
            @endif
        </div>
        <div class="block">
            <div class="block-content">
                <div class="row items-push">
                    <div class="col-sm-6">
                        <!-- Images -->
                        <div class="row js-gallery" >
                            <?php
                            if(count($product->has_images) > 0){
                                $images = DB::table('images')->where('product_id', $product->id)->orderByRaw("CAST(position as UNSIGNED) ASC")->get();
                            }
                            else{
                                $images = [];
                            }

                            ?>

                            <div class="col-md-12 mb2">
                                @if(count($images) > 0)
                                    @if($product->import_from_shopify == 1)
                                        <a class="img-link img-link-zoom-in img-lightbox" href="{{$images[0]->image}}">
                                            <img class="img-fluid" src="{{$images[0]->image}}" alt="">
                                        </a>
                                    @else
                                        @if($images[0]->isV == 0)
                                            <a class="img-link img-link-zoom-in img-lightbox" href="{{asset('images')}}/{{$images[0]->image}}">
                                                <img class="img-fluid" src="{{asset('images')}}/{{$images[0]->image}}" alt="">
                                            </a>
                                        @else
                                            <a class="img-link img-link-zoom-in img-lightbox" href="{{asset('images/variants')}}/{{$images[0]->image}}">
                                                <img class="img-fluid" src="{{asset('images/variants')}}/{{$images[0]->image}}" alt="">
                                            </a>
                                        @endif
                                    @endif

                                @endif
                            </div>
                            @if(count($images) > 0)
                                @foreach($images as $image)
                                    <div class="col-md-4">
                                        @if($product->import_from_shopify == 1)
                                            <a class="img-link img-link-zoom-in img-lightbox" href="{{$image->image}}">
                                                <img class="img-fluid" src="{{$image->image}}" alt="">
                                            </a>
                                        @else
                                            @if($image->isV == 0)
                                                <a class="img-link img-link-zoom-in img-lightbox" href="{{asset('images')}}/{{$image->image}}">
                                                    <img class="img-fluid" src="{{asset('images')}}/{{$image->image}}" alt="">
                                                </a>
                                            @else
                                                <a class="img-link img-link-zoom-in img-lightbox" href="{{asset('images/variants')}}/{{$image->image}}">
                                                    <img class="img-fluid" src="{{asset('images/variants')}}/{{$image->image}}" alt="">
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <hr>
                    
                        @if($product->tags != null)
                            <div class="tags" style="margin-top: 5px">

                                <h4 style="margin-bottom: 5px">Tags</h4>
                                @foreach(explode(',',$product->tags) as $tag)
                                    <span class="badge badge-info">{{$tag}}</span>
                                @endforeach
                            </div>

                    @endif
                    <!-- END Images -->
                    </div>
                    <div class="col-sm-6">
                        <!-- Vital Info -->
                        <div class="clearfix" style="margin-top: 5px;width: 100%">

                            @if($product->quantity > 0)
                                @if($product->varaint_count($product) > 0 && count($product->hasVariants) > 0)
                                    <span class="h5">
                                        <span class="font-w600 text-success">IN STOCK</span><br><small>{{$product->varaint_count($product)}} Available in {{count($product->hasVariants)}} Variants</small>
                                    </span>
                                @elseif($product->quantity > 0)
                                    <span class="h5">
                                        <span class="font-w600 text-success">IN STOCK</span><br><small>{{$product->quantity}} Available  </small>
                                    </span>
                                @else
                                    <span class="h5">
                                        <span class="font-w600 text-danger">OUT OF STOCK</span><br><small>Not Available</small>
                                    </span>
                                @endif
                            @else
                                <span class="h5">
                                <span class="font-w600 text-danger">OUT OF STOCK</span><br><small>Not Available</small>
                            </span>
                            @endif
                            <div class="text-right d-inline-block" style="float: right">
                                <span class="h3 font-w700 text-success">${{number_format($product->price,2)}} </span>
                            </div>
                        </div>
                        <hr>
                        <p>{!! $product->description !!}</p>
                        <!-- END Vital Info -->
                    </div>
                    <div class="col-md-12">
                        <!-- Extra Info -->
                        <div class="block">
                            <ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
                                <li class="nav-item active">
                                    <a class="nav-link" href="#ecom-product-comments">Variants</a>
                                </li>
                            </ul>
                            <div class="block-content tab-content">
                                <div class="tab-pane pull-r-l active" id="ecom-product-comments">
                                    @if(count($product->hasVariants) > 0)
                                        <table class="table table-striped table-borderless remove-margin-b">
                                            <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Cost</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($product->hasVariants as $index => $variant)
                                                <tr>
                                                    <td>
                                                        @if($product->import_from_shopify == 1)
                                                            <img class="img-avatar img-avatar-variant" style="border: 1px solid whitesmoke" data-form="#varaint_image_form_{{$index}}" data-input=".varaint_file_input"
                                                                 @if($variant->has_image == null)  src="https://wfpl.org/wp-content/plugins/lightbox/images/No-image-found.jpg"
                                                                 @else  src="{{$variant->has_image->image}}" @endif @ alt="">
                                                        @else
                                                            <img class="img-avatar img-avatar-variant" style="border: 1px solid whitesmoke" data-form="#varaint_image_form_{{$index}}" data-input=".varaint_file_input"
                                                                 @if($variant->has_image == null)  src="https://wfpl.org/wp-content/plugins/lightbox/images/No-image-found.jpg"
                                                                 @else @if($variant->has_image->isV == 1) src="{{asset('images/variants')}}/{{$variant->has_image->image}}" @else src="{{asset('images')}}/{{$variant->has_image->image}}" @endif @endif alt="">
                                                        @endif

                                                    </td>
                                                    <td class="variant_title">
                                                        @if($variant->option1 != null) {{$variant->option1}} @endif    @if($variant->option2 != null) / {{$variant->option2}} @endif    @if($variant->option3 != null) / {{$variant->option3}} @endif
                                                    </td>
                                                    <td>
                                                        @if($variant->quantity >0)
                                                            {{$variant->quantity}}
                                                        @else
                                                            Out of Stock
                                                        @endif
                                                    </td>
                                                    <td>${{number_format($variant->price,2)}}</td>
                                                    <td>${{number_format($variant->cost,2)}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p>This Product has Zero Variants</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- END Extra Info -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
