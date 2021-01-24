<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ProductVariantTrait;

class Product extends Model
{
    use ProductVariantTrait;
    public function hasVariants(){
        return $this->hasMany('App\ProductVariant');
    }
    public function has_images(){
        return $this->hasMany('App\Image','product_id');
    }
  
}
