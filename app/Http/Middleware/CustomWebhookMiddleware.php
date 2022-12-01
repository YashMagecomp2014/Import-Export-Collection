<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomWebhookMiddleware
{
    public function verify_webhook($data, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, config('shopify.api_secret'), true));
        return hash_equals($hmac_header, $calculated_hmac);
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $hmac_header = "";
        if (isset($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256']) && $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] != "") {
            $hmac_header = "";
        }
        $data = file_get_contents('php://input');
        $verified = self::verify_webhook($data, $hmac_header);
        Log::error('Webhook verified: ' . var_export($verified, true));
        if ($verified) {
            return $next($request);
        } else {
            return response('Unauthorized', 401);
        }
    }

}
