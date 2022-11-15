<?php

namespace App\Http\Controllers;

use App\Models\Session;

class PlanController extends Controller
{
    public function PlanCreation()
    {

        $variable = [
            "input" => [
                "name" => "storelaravelapp Recurring Plan",
                "returnUrl" => "https://storelaravelapp.myshopify.com",
                "test" => "true",
                "trialDays" => 7,
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

            ],
        ];

        $query = 'mutation AppSubscriptionCreate($name: String!, $lineItems: [AppSubscriptionLineItemInput!]!, $returnUrl: URL!, $trialDays: Int) {
            appSubscriptionCreate(name: $name, returnUrl: $returnUrl, lineItems: $lineItems, trialDays: $trialDays) {
              userErrors {
                field
                message
              }
              appSubscription {
                name
                test
                trialDays
                returnUrl
                lineItems{
                    plan{
                        usageRecords{
                        price{
                            amount
                            currencyCode
                        }
                    }
                }
                }
              }
              confirmationUrl
            }
          }';

        $finalquery = [
            "query" => $query,
            "variables" => $variable,
        ];

        $token = Session::where('shop', 'storelaravelapp.myshopify.com')->first('access_token');

        // print_r($token->access_token);
        // exit;
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://storelaravelapp.myshopify.com/admin/api/2022-10/graphql.json');
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

        info($result);
    }
}
