<?php

namespace App\Jobs;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductCSVJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $header;
    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($handle, $shopurl)
    {
        $this->handle = $handle;
        $this->shopurl = $shopurl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $productbyhandle = [
            "handle" => $this->handle,
        ];

        $productbyhandlequery = 'query getProductIdFromHandle($handle: String!) {
            productByHandle(handle: $handle) {
            id
            }
            }';

        $finalquery = [
            "query" => $productbyhandlequery,
            "variables" => $productbyhandle,
        ];

        $result = $this->curls($finalquery, $this->shopurl);

        $productid = json_decode($result, true);

        $invalidhandle = $productid['data'];

        if ($invalidhandle['productByHandle'] == null) {

            $isError = true;
            $returnResponse["error"] = [];

            $returnResponse["error"][] = "Please insert valid product handle";

            $returnResponse["is_error"] = true;
            return $returnResponse;

        }
        $pid = $productid['data']['productByHandle']['id'];

        return $pid;
    }

    public function curls($finalquery, $shopurl)
    {
        $token = Session::where('shop', $shopurl)->first('access_token');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://' . $shopurl . '/admin/api/2022-10/graphql.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($finalquery));

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
