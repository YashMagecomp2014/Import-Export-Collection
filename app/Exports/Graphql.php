<?php
namespace App\Exports;

use App\Models\Session;

class Graphql 
{
    public function curls($body, $shop)
    {
        $token = Session::where('shop', $shop)->first('access_token');
        $ch = curl_init();
        info($shop);
        curl_setopt($ch, CURLOPT_URL, 'https://' . $shop . '/admin/api/2022-10/graphql.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Shopify-Access-Token: ' . $token->access_token . '';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }
}
