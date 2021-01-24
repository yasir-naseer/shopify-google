@extends('layouts.index')
@section('content')
    <div class="bg-body-light">
        <div class="content content-full pt-3 pb-3">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h5 my-2">
                    Edit Product
                </h1>
                <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Products</a>
                        </li>

                        <li class="breadcrumb-item">Edit Product</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <form action="{{ route('product.update', $product->id) }}" class="form-horizontal push-30" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="content">
            <div id="forms-div" class="row">
                <div class="col-sm-8">
                    <div class="block">
                        <div>
                            <input type="hidden" name="type[]" value="basic-info">
                            <div class="block-content block-content-full">

                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <label for="product-name">Title</label>
                                        <input class="form-control" type="text" id="product-name" name="title"
                                               value="{{$product->title}}"  placeholder="Short Sleeve Shirt" >
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-12 push-10">
                                        <div class="form-material form-material-primary">
                                            <label>Long Description</label>
                                        </div>
                                    </div>
                                    <div class="col-xs-12">
                                    <textarea class="js-summernote" name="description"
                                              placeholder="Please Enter Description here !">{{$product->description}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="block">
                        <div class="block-header">
                            <h3 class="block-title">Images</h3>
                            <div class="text-right d-inline-block">
                                <a style="margin-left: 10px;" class="btn btn-sm btn-primary text-white" data-toggle="modal" data-target="#add_product_images">Add More Images</a>
                            </div>
                        </div>
                        <div class="block-content pb-4">
                            @if(count($product->has_images) >0)
                                <div class="row editable" id="image-sortable" data-product="{{$product->id}}" data-route="{{route('product.update_image_position',$product->id)}}">

                                    @foreach(DB::table('images')->where('product_id', $product->id)->orderByRaw("CAST(position as UNSIGNED) ASC")->cursor() as $image)
                                        <div class="col-lg-4 preview-image animated fadeIn mb-2" data-id="{{$image->id}}">
                                            <div class="options-container fx-img-zoom-in fx-opt-slide-right">
                                                @if($image->isV == 0)
                                                    <img class="img-fluid options-item" src="{{asset('images')}}/{{$image->image}}" alt="" >
                                                @else
                                                    <img class="img-fluid options-item" src="{{asset('images/variants')}}/{{$image->image}}" alt="">
                                                @endif
                                                <div class="options-overlay bg-black-75">
                                                    <div class="options-overlay-content">
                                                        <a class="btn btn-sm btn-light delete-file" data-type="existing-product-image-delete" data-token="{{csrf_token()}}" data-route="{{route('product.delete.existing.image',$product->id)}}" data-file="{{$image->id}}"><i class="fa fa-times"></i> Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @if(count($product->hasVariants) == 0)
                        <div class="block">
                            <div class="block-header">
                                <h3 class="block-title">Pricing</h3>
                            </div>
                            <div action="{{route('product.update',$product->id)}}" method="post">
                                @csrf
                                <input type="hidden" name="type[]" value="pricing">
                                <div class="block-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group row">
                                                <div class="col-md-6 push-10">
                                                    <label>Price</label>
                                                    <input type="number" step="any" class="form-control" name="price"
                                                           value="{{$product->price}}"  placeholder="$ 0.00" >
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Cost Per Item</label>
                                                    <input type="number" step="any" class="form-control" name="cost"
                                                           value="{{$product->cost}}"  placeholder="$ 0.00">

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="col-xs-12 ">
                                                    <label>Quantity</label>
                                                    <input type="number" step="any" class="form-control" name="quantity" value="{{$product->quantity}}" placeholder="0" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="col-xs-12 ">
                                                    <label>Weight</label>
                                                    <input type="number" step="any" class="form-control" value="{{$product->weight}}" name="weight" placeholder="0.0Kg">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="col-xs-12 ">
                                                    <label>SKU</label>
                                                    <input type="text" class="form-control" name="sku" value="{{$product->sku}}" >
                                                    @error('sku')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">

                                                <div class="col-xs-12 ">
                                                    <label>Barcode</label>
                                                    <input type="text" class="form-control" value="{{$product->barcode}}" name="barcode">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($product->variants == 1)
                        <div class="block">
                            <div class="block-header">
                                <h3 class="block-title">Product Weight/Quantity</h3>
                            </div>
                            <div action="{{route('product.update',$product->id)}}" method="post">
                                @csrf
                                <input type="hidden" name="type[]" value="pricing-for-variant">
                                <div class="block-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <div class="col-xs-12">
                                                    <label>Price</label>
                                                    <input type="number" step="any" class="form-control" name="price" value="{{$product->price}}"  placeholder="$ 0.00" >
                                                </div>
                                                <div class="col-xs-12">
                                                    <label>Weight</label>
                                                    <input type="number" step="any" class="form-control" value="{{$product->weight}}" name="weight" placeholder="0.0Kg">
                                                </div>
                                                <div class="col-xs-12 ">
                                                    <label>Quantity</label>
                                                    <input type="number" step="any" class="form-control" name="quantity" value="{{$product->quantity}}" placeholder="0" >
                                                </div>
                                                <div class="col-xs-12 ">
                                                    <label>SKU</label>
                                                    <input type="text" class="form-control" name="sku" value="{{$product->sku}}" >
                                                    @error('sku')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="block">
                            <div class="block-header d-inline-flex" style="width: 100%" >
                                <h3 class="block-title">
                                    Variant
                                </h3>

                                <div class="text-left d-inline-block">
                                    <div class="custom-control custom-checkbox d-inline-block">
                                        <input type="checkbox" name="variants" class="custom-control-input" id="val-terms" checked value="1">
                                        <label class="custom-control-label" for="val-terms">This product has multiple options, like
                                            different sizes or colors</label>
                                    </div>
                                </div>


                                <div class="text-right d-inline-block">
                                    {{--                                    <a class="btn btn-sm btn-light" style="margin-left: 10px;" data-toggle="modal" data-target="#edit_options">Edit Options</a>--}}
                                    <a href="{{route('product.existing_product_update_variants',$product->id)}}" style="margin-left: 10px;" class="btn btn-sm btn-light">Edit Variants</a>
                                </div>
                            </div>

                            <hr>
                            <div class="ml-2 row mb-3">
                                <div class="col-md-2">
                                    <label for="">Bulk Pricing/Cost</label>
                                </div>
                                <div class="col-md-2">
                                    <label for="">Price</label>
                                    <input id="bulk-var-price" type="number" class="form-control" >
                                </div>
                                <div class="col-md-2">
                                    <label for="">Cost</label>
                                    <input id="bulk-var-cost" type="number" class="form-control" >
                                </div>
                            </div>
                            <div class="block-content" style="padding-top: 0 !important;">
                                <table class="table variants-div js-table-sections table-hover table-responsive">
                                    <thead>
                                    <tr>
                                        <th style="vertical-align: top">Title</th>
                                        <th style="vertical-align: top">Image</th>
                                        <th style="vertical-align: top">Price</th>
                                        <th style="vertical-align: top">Cost</th>
                                        <th style="vertical-align: top">Quantity</th>
                                        <th style="vertical-align: top">SKU</th>
                                        <th style="vertical-align: top">Barcode</th>
                                    </tr>
                                    </thead>
                                    @if(count($product->hasVariants) > 0)
                                        <input type="hidden" name="type[]" value="single-variant-update">
                                        @foreach($product->hasVariants as $index => $v)
                                            <div action="{{route('product.update',$product->id)}}" method="post">
                                                @csrf
                                                <input type="hidden" name="variant_id[]" value="{{$v->id}}">
                                                <tbody class="js-table-sections-header">
                                                <tr>
                                                    <td class="variant_title">
                                                        @if($v->option1 != null) {{$v->option1}} @endif    @if($v->option2 != null) / {{$v->option2}} @endif    @if($v->option3 != null) / {{$v->option3}} @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <img class="img-avatar " style="border: 1px solid whitesmoke"  data-input=".varaint_file_input" data-toggle="modal" data-target="#select_image_modal{{$v->id}}"
                                                             @if($v->has_image == null)  src="https://wfpl.org/wp-content/plugins/lightbox/images/No-image-found.jpg"
                                                             @else @if($v->has_image->isV == 0) src="{{asset('images')}}/{{$v->has_image->image}}" @else src="{{asset('images/variants')}}/{{$v->has_image->image}}" @endif @endif alt="">
                                                        <div class="modal fade" id="select_image_modal{{ $v->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-block-popout" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-popout" role="document">
                                                                <div class="modal-content">
                                                                    <div class="block block-themed block-transparent mb-0">
                                                                        <div class="block-header bg-primary-dark">
                                                                            <h3 class="block-title">Select Image For Variant</h3>
                                                                            <div class="block-options">
                                                                                <button type="button" class="btn-block-option">
                                                                                    <i class="fa fa-fw fa-times"  data-dismiss="modal" aria-label="Close"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        <div class="block-content font-size-sm">
                                                                            <div class="row">
                                                                                @foreach($product->has_images as $image)
                                                                                    <div class="col-md-4">
                                                                                        @if($image->isV == 0)
                                                                                            <img class="img-fluid options-item" src="{{asset('images')}}/{{$image->image}}" alt="">
                                                                                        @else
                                                                                            <img class="img-fluid options-item" src="{{asset('images/variants')}}/{{$image->image}}" alt="">
                                                                                        @endif
                                                                                        <p style="color: #ffffff;cursor: pointer" data-image="{{$image->id}}" data-variant="{{$v->id}}" data-type="product" class="rounded-bottom bg-info choose-variant-image text-center">Choose</p>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                            <!-- <p class="text-center font-weight-bold">OR</p>
                                                                            <hr> -->
                                                                            <!-- <a class="img-avatar-variant btn btn-sm btn-primary text-white mb2" data-form="#varaint_image_form_{{$index}}">Upload New Picture</a> -->

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" class="form-control var-price-row" name="single-var-price-{{$v->id}}" placeholder="$0.00" value="{{$v->price}}">
                                                    </td>

                                                    <td><input type="number" step="any" class="form-control var-cost-row" name="single-var-cost-{{$v->id}}" value="{{$v->cost}}" placeholder="$0.00"></td>
                                                    <td><input type="number" step="any" class="form-control" value="{{$v->quantity}}" name="single-var-quantity-{{$v->id}}" placeholder="0"></td>
                                                    <td><input type="text" class="form-control" name="single-var-sku-{{$v->id}}" value="{{$v->sku}}"></td>
                                                    <td><input type="text" class="form-control" name="single-var-barcode-{{$v->id}}" value="{{$v->barcode}}" placeholder="">
                                                    </td>

                                                </tr>
                                                </tbody>
                                                <tbody>
                                                <tr>

                                                    <td style="vertical-align: middle"> @if($v->option1 != null) Option1: @endif</td>
                                                    <td>
                                                        @if($v->option1 != null)
                                                            <input type="text" class="form-control" name="option1-{{$v->id}}" placeholder="$0.00" value="{{$v->option1}}">
                                                        @endif
                                                    </td>
                                                    <td style="vertical-align: middle">@if($v->option2 != null) Option2: @endif</td>
                                                    <td>
                                                        @if($v->option2 != null)
                                                            <input type="text" class="form-control" name="option2-{{$v->id}}" placeholder="$0.00" value="{{$v->option2}}">
                                                        @endif
                                                    </td>
                                                    <td style="vertical-align: middle">@if($v->option3 != null) Option3: @endif</td>
                                                    <td>
                                                        @if($v->option3 != null)
                                                            <input type="text" class="form-control" name="option3-{{$v->id}}" placeholder="$0.00" value="{{$v->option3}}">
                                                        @endif
                                                    </td>

                                                </tr>
                                                </tbody>
                                            </div>
                                        @endforeach
                                    @endif
                                </table>
                            </div>
                            {{--                            <div class="form-image-src" style="display: none">--}}
                            {{--                                @if(count($product->hasVariants) > 0)--}}
                            {{--                                    @foreach($product->hasVariants as $index => $v)--}}
                            {{--                                        <div id="varaint_image_form_{{$index}}" action="{{route('product.update',$product->id)}}" method="post" enctype="multipart/form-data">--}}
                            {{--                                            @csrf--}}
                            {{--                                            <input type="hidden" name="type[]" value="variant-image-update">--}}
                            {{--                                            <input type="hidden" name="var_id" value="{{$v->id}}">--}}
                            {{--                                            <input type="file" name="varaint_src" class="varaint_file_input" accept="image/*">--}}
                            {{--                                        </div>--}}
                            {{--                                    @endforeach--}}
                            {{--                                @endif--}}
                            {{--                            </div>--}}
                        </div>
                        {{--                        <div class="modal fade" id="edit_options" tabindex="-1" role="dialog" aria-labelledby="modal-block-popout" aria-hidden="true">--}}
                        {{--                            <div class="modal-dialog modal-dialog-popout modal-xl" role="document">--}}
                        {{--                                <div class="modal-content">--}}
                        {{--                                    <div class="block block-themed block-transparent mb-0">--}}
                        {{--                                        <div class="block-header bg-primary-dark">--}}
                        {{--                                            <h3 class="block-title">Edit Options</h3>--}}
                        {{--                                            <div class="block-options">--}}
                        {{--                                                <button type="button" class="btn-block-option">--}}
                        {{--                                                    <i class="fa fa-fw fa-times"  data-dismiss="modal" aria-label="Close"></i>--}}
                        {{--                                                </button>--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="block-content" style="padding: 20px !important;">--}}
                        {{--                                            <div class="row">--}}
                        {{--                                                @if(count($product->option1($product))>0)--}}
                        {{--                                                    <div class="col-md-12" style="margin-bottom: 10px">--}}
                        {{--                                                        <div class="row">--}}
                        {{--                                                            <div class="col-md-3">--}}
                        {{--                                                                <input type="text" class="form-control" value="Option1">--}}
                        {{--                                                            </div>--}}
                        {{--                                                            <div class="col-md-9">--}}
                        {{--                                                                @foreach($product->option1($product) as $a)--}}
                        {{--                                                                    <span class="badge badge-info">--}}
                        {{--                                                                        <span >{{$a}}</span>--}}
                        {{--                                                                        <a><i data-option="option1" class="remove-option fa fa-times" style="color: white"></i></a>--}}
                        {{--                                                                    </span>--}}
                        {{--                                                                @endforeach--}}
                        {{--                                                                    <hr>--}}

                        {{--                                                                    <input type="text"  name="cost" value="{{$product->cost}}" style="display: none">--}}
                        {{--                                                                    <input type="text" name="prod-price" value="{{$product->price}}" style="display: none">--}}
                        {{--                                                                    <input type="text"  name="sku" value="{{$product->sku}}" style="display: none">--}}
                        {{--                                                                    <input type="text"  name="quantity" value="{{$product->quantity}}" style="display: none">--}}


                        {{--                                                                    <input class="js-tags-options1-update form-control mt-3" type="text"--}}
                        {{--                                                                    id="product-meta-keywords" name="option1-update" value="" data-role="tagsinput">--}}

                        {{--                                                                    <div class="old-option1-update-form" action="{{route('product.update',$product->id)}}" method="post">--}}
                        {{--                                                                        @csrf--}}
                        {{--                                                                        <input type="hidden" name="type[]" value="old-option-update">--}}
                        {{--                                                                        <div class="variants_table" style="display: none;">--}}
                        {{--                                                                        <hr>--}}
                        {{--                                                                        <h3 class="block-title">--}}
                        {{--                                                                            Preview--}}
                        {{--                                                                                <button type="button" class="update-option-1-btn btn btn-primary float-right">Update this option</button>--}}
                        {{--                                                                        </h3>--}}
                        {{--                                                                        <br>--}}
                        {{--                                                                        <div class="form-group">--}}
                        {{--                                                                            <div class="col-xs-12 push-10">--}}
                        {{--                                                                                <table class="table table-hover">--}}
                        {{--                                                                                    <thead>--}}
                        {{--                                                                                    <tr>--}}
                        {{--                                                                                        <th style="width: 20%;">Title</th>--}}
                        {{--                                                                                        <th style="width: 15%;">Price</th>--}}
                        {{--                                                                                        <th style="width: 17%;">Cost</th>--}}
                        {{--                                                                                        <th style="width: 10%;">Quantity</th>--}}
                        {{--                                                                                        <th style="width: 20%;">SKU</th>--}}
                        {{--                                                                                        <th style="width: 20%;">Barcode</th>--}}
                        {{--                                                                                    </tr>--}}
                        {{--                                                                                    </thead>--}}
                        {{--                                                                                    <tbody class="option-1-table-body">--}}

                        {{--                                                                                    </tbody>--}}
                        {{--                                                                                </table>--}}
                        {{--                                                                            </div>--}}
                        {{--                                                                        </div>--}}
                        {{--                                                                    </div>--}}
                        {{--                                                                    </div>--}}
                        {{--                                                            </div>--}}
                        {{--                                                        </div>--}}
                        {{--                                                        <div class="row" style="margin-top:10px ">--}}
                        {{--                                                            @if(count($product->option2($product)) == 0)--}}
                        {{--                                                                <div class="col-md-12 add-option-button">--}}
                        {{--                                                                    <a class="btn btn-light add-option-div">Add Other Option</a>--}}
                        {{--                                                                </div>--}}
                        {{--                                                                <div class="div2 row col-md-12" style="display: none">--}}
                        {{--                                                                    <div class="col-md-3">--}}
                        {{--                                                                        <input type="text" class="form-control" readonly value="Option2">--}}
                        {{--                                                                    </div>--}}
                        {{--                                                                    <div class="new-option-add col-md-7" action="{{route('product.update',$product->id)}}" method="post">--}}
                        {{--                                                                        @csrf--}}
                        {{--                                                                        <input type="hidden" name="type[]" value="new-option-add">--}}
                        {{--                                                                        <div class="">--}}
                        {{--                                                                            <input type="hidden" name="option" value="option2">--}}
                        {{--                                                                            <input type="text" class="form-control option-value" name="value" value="" placeholder="Enter Only One Option Value">--}}
                        {{--                                                                        </div>--}}
                        {{--                                                                    </div>--}}

                        {{--                                                                    <div class="col-md-2">--}}
                        {{--                                                                        <a class="btn btn-light delete-option-value"><i class="fa fa-times"></i></a>--}}
                        {{--                                                                    </div>--}}
                        {{--                                                                </div>--}}
                        {{--                                                            @endif--}}
                        {{--                                                        </div>--}}
                        {{--                                                    </div>--}}
                        {{--                                                @endif--}}
                        {{--                                                @if(count($product->option2($product))>0)--}}
                        {{--                                                    <div class="col-md-12" style="margin-bottom: 10px">--}}
                        {{--                                                        <div class="row">--}}
                        {{--                                                            <div class="col-md-3">--}}
                        {{--                                                                <input type="text" class="form-control" readonly value="Option2">--}}
                        {{--                                                            </div>--}}
                        {{--                                                            <div class="col-md-9">--}}

                        {{--                                                                @foreach($product->option2($product) as $a)--}}
                        {{--                                                                    <span class="badge badge-info">--}}
                        {{--                                                                        <span>{{$a}}</span>--}}
                        {{--                                                                        <a><i data-option="option2" class="remove-option fa fa-times" style="color: white"></i></a>--}}
                        {{--                                                                    </span>--}}
                        {{--                                                                @endforeach--}}
                        {{--                                                            </div>--}}

                        {{--                                                        </div>--}}
                        {{--                                                        <div class="row" style="margin-top:10px ">--}}
                        {{--                                                            @if(count($product->option3($product)) == 0)--}}
                        {{--                                                                <div class="col-md-12 add-option-button" style="">--}}
                        {{--                                                                    <a class="btn btn-light add-option-div">Add Other Option</a>--}}
                        {{--                                                                </div>--}}
                        {{--                                                                <div class="div2 row col-md-12" style="display: none">--}}
                        {{--                                                                    <div class="col-md-3">--}}
                        {{--                                                                        <input type="text" class="form-control" readonly value="Option3">--}}
                        {{--                                                                    </div>--}}
                        {{--                                                                    <div class="new-option-add col-md-7"  action="{{route('product.update',$product->id)}}" method="post">--}}
                        {{--                                                                        @csrf--}}
                        {{--                                                                        <input type="hidden" name="type[]" value="new-option-add">--}}
                        {{--                                                                        <div class="">--}}
                        {{--                                                                            <input type="hidden" name="option" value="option3">--}}
                        {{--                                                                            <input type="text" class="form-control option-value" name="value" value="" placeholder="Enter Only One Option Value">--}}
                        {{--                                                                        </div>--}}
                        {{--                                                                    </div>--}}
                        {{--                                                                    <div class="col-md-2">--}}
                        {{--                                                                        <a class="btn btn-light delete-option-value"><i class="fa fa-times"></i></a>--}}
                        {{--                                                                    </div>--}}
                        {{--                                                                </div>--}}
                        {{--                                                            @endif--}}
                        {{--                                                        </div>--}}
                        {{--                                                    </div>--}}
                        {{--                                                @endif--}}
                        {{--                                                @if(count($product->option3($product))>0)--}}
                        {{--                                                    <div class="col-md-12">--}}
                        {{--                                                        <div class="row">--}}
                        {{--                                                            <div class="col-md-3">--}}
                        {{--                                                                <input type="text" class="form-control" readonly value="Option3">--}}
                        {{--                                                            </div>--}}
                        {{--                                                            <div class="col-md-9">--}}
                        {{--                                                                @foreach($product->option3($product) as $a)--}}
                        {{--                                                                    <span class="badge badge-info">--}}
                        {{--                                                                        <span>{{$a}}</span>--}}
                        {{--                                                                        <a><i data-option="option3" class="remove-option fa fa-times" style="color: white"></i></a>--}}
                        {{--                                                                    </span>--}}
                        {{--                                                                @endforeach--}}
                        {{--                                                            </div>--}}
                        {{--                                                        </div>--}}
                        {{--                                                    </div>--}}
                        {{--                                                @endif--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="deleted-data">--}}
                        {{--                                            <div id="variant-options-update" action="{{route('product.update',$product->id)}}" method="post">--}}
                        {{--                                                @csrf--}}
                        {{--                                                <input type="hidden" name="type[]" value="variant-option-delete">--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}

                        {{--                                        <div class="block-content block-content-full text-right border-top">--}}
                        {{--                                            <button type="button" data-option1="" data-option2="" data-option3="" data-deleted="0" class="variant-options-update-save btn btn-primary">Save</button>--}}
                        {{--                                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">--}}
                        {{--                                                Discard--}}
                        {{--                                            </button>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    @else
                        <div class="block">
                            <div class="block-header d-inline-flex" style="width: 100%" >
                                <h3 class="block-title">No Variant For This Products</h3>
                                <div class="text-right d-inline-block" >
                                    <a href="{{route('product.existing_product_new_variants',$product->id)}}">Add New Variants</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-sm-4">
                    <div class="block">
                        <div class="block-header">
                            <h3 class="block-title">Organization</h3>
                        </div>
                        <div action="{{route('product.update',$product->id)}}" method="post">
                            @csrf
                            <input type="hidden" name="type[]" value="organization">
                            <div class="block-content">
                                <div class="form-group">
                                    <div class="col-xs-12 push-10">
                                        <label>Product Type</label>
                                        <input type="text" class="form-control" name="product_type"
                                               value="{{$product->type}}"  placeholder="eg. Shirts" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-12 push-10">
                                        <label>Vendor</label>
                                        <input type="text" class="form-control" name="vendor" placeholder="eg. Nike"
                                               value="{{$product->vendor}}"   >
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <div class="form-material form-material-primary">
                                            <label>Tags</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block">
                        <div class="block-header">
                            <h3 class="block-title">More Details</h3>
                        </div>
                        <div action="{{route('product.update',$product->id)}}" method="post">
                            @csrf
                            <input type="hidden" name="type[]" value="more-details">
                            <div class="block-content">
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <label>Product Status</label>
                                        <br>
                                        <div class="custom-control custom-radio mb-1">
                                            <input type="radio" class="custom-control-input" id="example-radio-customPublished" @if($product->status == 1) checked="" @endif name="status" value="1" checked="">
                                            <label class="custom-control-label" for="example-radio-customPublished">Published</label>
                                        </div>
                                        <div class="custom-control custom-radio mb-1">
                                            <input type="radio" class="custom-control-input" id="example-radio-customDraft" @if($product->status == 0) checked="" @endif name="status" value="0" >
                                            <label class="custom-control-label" for="example-radio-customDraft">Draft</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content" style="margin-bottom: 10px">
                <div class="row ">
                    <div class="col-sm-12 text-right">
                        <hr>
                        <button class="btn btn-primary text-white btn-square " type="submit">Update</button>
                        <a href="{{ route('product.edit',$product->id) }}" class="btn btn-default btn-square">Discard</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="add_product_images" tabindex="-1" role="dialog" aria-labelledby="modal-block-popout" aria-hidden="true">
        <div class="modal-dialog modal-dialog-popout modal-lg" role="document">
            <div class="modal-content">
                <div class="block block-themed block-transparent mb-0">
                    <div class="block-header bg-primary-dark">
                        <h3 class="block-title">Add Product Images</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option">
                                <i class="fa fa-fw fa-times"  data-dismiss="modal" aria-label="Close"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-right border-top">
                        <form class=" " action="{{route('product.add.images',$product->id)}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="col-md-12" style="padding-bottom: 13px;width: 682px">
                                <div class="dropzone dz-clickable">
                                    <div class="dz-default dz-message"><span>Click here to upload images.</span></div>
                                    <div class="row preview-drop"></div>
                                </div>
                                <input style="display: none" type="file"  name="images[]" accept="image/*" class="push-30-t push-30 dz-clickable images-upload" multiple >
                            </div>
                            <button type="submit" class="btn btn-default save-img">
                                Save
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">
                                Discard
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
