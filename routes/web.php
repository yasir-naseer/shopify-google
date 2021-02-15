<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth.shopify']], function () {
    Route::get('/products','ProductController@index')->name('product.create');
    Route::get('/','ProductController@all')->name('home');
    Route::any('/products/{id}/view','ProductController@view')->name('product.view');
    Route::any('/products/{id}/edit','ProductController@edit')->name('product.edit');
    Route::any('/products/{id}/update','ProductController@UpdateGlobal')->name('product.update');
    Route::any('/products/{id}/add/new/variants','ProductController@updateExistingProductNewVariants')->name('product.update.add.new.variants');
    Route::any('/products/{id}/update/old/variants','ProductController@updateExistingProductOldVariants')->name('product.update.old.variants');
    Route::any('/products/{id}/change/status','ProductController@updateProductStatus')->name('product.change.status');
    Route::any('/products/{id}/delete/existing/image','ProductController@deleteExistingProductImage')->name('product.delete.existing.image');
    Route::any('/products/{id}/add/images','ProductController@productAddImages')->name('product.add.images');
    Route::post('/products/save','ProductController@save')->name('product.save');
    Route::post('/products/variant/save','ProductController@variant')->name('product.variant');
    Route::get('/products/{id}/delete','ProductController@delete')->name('product.delete');
    Route::get('/products/{id}/images-position-update','ProductController@update_image_position')->name('product.update_image_position');
    Route::get('/products/{id}/varaints/new','ProductController@add_existing_product_new_variants')->name('product.existing_product_new_variants');
    Route::get('/products/{id}/varaints/update','ProductController@update_existing_product_new_variants')->name('product.existing_product_update_variants');
    Route::get('/push/{id}/to-store','ProductController@import_to_shopify')->name('import_to_shopify');
    Route::get('/sync/products', 'ProductController@storeProducts')->name('sync.products');
    Route::get('/variant/{id}/change/image/{image_id}', 'ProductController@change_image')->name('change_image');


    Route::get('/settings','SettingController@index')->name('settings');
    Route::post('/settings/{id}/update','SettingController@update')->name('settings.update');
});




//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
