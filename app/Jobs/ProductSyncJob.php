<?php

namespace App\Jobs;

use App\Http\Controllers\ProductController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $product;
    public $shop;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product,$shop)
    {
        $this->product=$product;
        $this->shop=$shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $con=new ProductController();
        $con->createProduct($this->product,$this->shop);
    }
}
