<?php

namespace App\Jobs;

use App\Http\Controllers\GoogleController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GoogleUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $product,$shop,$request;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product,$shop,$request)
    {
        $this->product=$product;
        $this->request=$request;
        $this->shop=$shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $google=new GoogleController();
            $google->updateProduct($this->product,$this->shop,$this->request);
        }catch (\Exception $exception)
        {

        }
    }
}
