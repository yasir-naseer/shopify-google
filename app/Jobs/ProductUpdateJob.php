<?php namespace App\Jobs;

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProductController;
use App\Setting;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use stdClass;

class ProductUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain|string
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain.
     * @param stdClass $data       The webhook data (JSON decoded).
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $product=json_decode(json_encode($this->data),FALSE);
        $shop=User::where('name',$this->shopDomain)->first();
        $setting = Setting::where('shop', $shop->name)->first();
        if ($setting->googleUpdate==true && $setting->googleWebhook==true)
        {
            $google=new GoogleController();
            $product=json_decode(json_encode($this->data),FALSE);
            $google->createProduct($product);
        }
    }
}
