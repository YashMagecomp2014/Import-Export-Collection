<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function PlanCreation(Request $request)
    {
        $shopName = $request->header("url");
        $variable = [

            "lineItems" => [
                [
                    "plan" => [
                        "appRecurringPricingDetails" => [
                            "price" => [
                                "amount" => 19,
                                "currencyCode" => "USD",
                            ],

                        ],
                    ],
                ],
            ],
            "name" => "storelaravelapp Recurring Plan",
            "returnUrl" => route('active.charge') . "?shop=" . $shopName,
            "test" => true,
            "trialDays" => 7,
        ];

        $query = 'mutation AppSubscriptionCreate($name: String!, $lineItems: [AppSubscriptionLineItemInput!]!, $returnUrl: URL!, $trialDays: Int, $test: Boolean) {
            appSubscriptionCreate(name: $name, returnUrl: $returnUrl, lineItems: $lineItems, trialDays: $trialDays, test: $test) {
              userErrors {
                field
                message
              }
              appSubscription {
                id
              }
              confirmationUrl
            }
          }';

        $finalquery = [
            "query" => $query,
            "variables" => $variable,
        ];

        $token = Session::where('shop', $shopName)->first('access_token');

        // print_r($token->access_token);
        // exit;
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://'.$shopName.'/admin/api/2022-10/graphql.json');
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

        $response = json_decode($result, true);

        $confirmurl = $response['data']['appSubscriptionCreate']['confirmationUrl'];
        $planid = $response['data']['appSubscriptionCreate']['appSubscription']['id'];
        $arrOfPlan = [

            'id' => $planid,
            'confirmationUrl' => $confirmurl,
        ];

        return $arrOfPlan;
    }
}
