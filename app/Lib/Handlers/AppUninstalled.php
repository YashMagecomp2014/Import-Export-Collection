<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Models\Charge;
use App\Models\Collection;
use Illuminate\Support\Facades\Log;
use Shopify\Webhooks\Handler;
use App\Models\Session;
use Illuminate\Support\Facades\Storage;

class AppUninstalled implements Handler
{
    public function handle(string $topic, string $shop, array $body): void
    {
        Log::debug("App was uninstalled from $shop - removing all sessions");
        Session::where('shop', $shop)->delete();
        Collection::where('shop' , $shop)->delete();
        Charge::where('shop' , $shop)->delete();
        $store = Storage::deleteDirectory('public/'.$shop);
        
    }
}
