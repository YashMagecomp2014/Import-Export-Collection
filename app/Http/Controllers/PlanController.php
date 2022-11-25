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

        $body = [
            "query" => $query,
            "variables" => $variable,
        ];

        $shop = Session::where('shop', $this->shopurl)->first();
        $response = $shop->graph($body);


        $confirmurl = $response['data']['appSubscriptionCreate']['confirmationUrl'];
        $planid = $response['data']['appSubscriptionCreate']['appSubscription']['id'];
        $arrOfPlan = [

            'id' => $planid,
            'confirmationUrl' => $confirmurl,
        ];

        return $arrOfPlan;
    }
}
