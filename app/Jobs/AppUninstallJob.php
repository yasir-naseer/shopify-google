<?php namespace App\Jobs;

use App\Product;
use App\ProductVariant;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use stdClass;

class AppUninstallJob implements ShouldQueue
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
        try {
            $shop=json_encode($this->data);
            $shop=json_decode($shop,FALSE);
            $user=User::where('name',$shop->domain)->first();
            $productIds=Product::where('shop_id',$user->id)->pluck('shopify_id');
            Product::where('shop_id',$user->id)->delete();
            ProductVariant::whereIn('product_id',$productIds)->delete();
            $user->forceDelete();
        }catch (\Exception $exception)
        {

        }
    }
}
