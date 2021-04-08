<?php

namespace App\Jobs;

use App\Http\Controllers\GoogleController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GoogleUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $product,$request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product,$request)
    {
        $this->product=$product;
        $this->request=$request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $google=new GoogleController();
        $google->updateProduct($this->product,$this->request);
    }
}
