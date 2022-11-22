<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Shopify\Webhooks\Topics;

class UninstallController extends Controller
{
    public function UninstallApp(Request $request)
    {
        $topic = Topics::APP_UNINSTALLED;
    }
}
