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
                    Bulk Edit
                </h1>
                <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <a class="link-fx" href="">Products</a>
                        </li>
                        <li class="breadcrumb-item">Bulk Edit
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="row mb2">
            <div class="col-sm-12">
                <button class="btn btn-success btn-square float-right">Save</button>
            </div>
        </div>
        <div class="block">
            <div class="block-content">
                <div class="row items-push">

                </div>
            </div>
        </div>
    </div>
@endsection
