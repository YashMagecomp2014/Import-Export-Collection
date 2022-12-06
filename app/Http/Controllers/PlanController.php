<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function PlanCreation(Request $request)
    {
        $shopName = $request->header("url");

        $plan = $request->plan;

        $variable = [

            "lineItems" => [
                [
                    "plan" => [
                        "appRecurringPricingDetails" => [
                            "price" => [
                                "currencyCode" => "USD",
                            ],
                        ],
                    ],
                ],
            ],
            "returnUrl" => route('active.charge') . "?shop=" . $shopName. '&plan='.$plan,
            "test" => true,
            "trialDays" => 7,
        ];

        if($plan == 1){

            $variable['name'] = 'PLAN-1';
            $variable['lineItems'][0]['plan']['appRecurringPricingDetails']['price']['amount'] = 2.99;
            
        }else if($plan == 2){

            $variable['name'] = 'PLAN-2';
            $variable['lineItems'][0]['plan']['appRecurringPricingDetails']['price']['amount'] = 29.99;
        }
        $query = 'mutation AppSubscriptionCreate($name: String!, $lineItems: [AppSubscriptionLineItemInput!]!, $returnUrl: URL!, $trialDays: Int, $test: Boolean) {
            appSubscriptionCreate(name: $name, returnUrl: $returnUrl, lineItems: $lineItems, trialDays: $trialDays, test: $test) {
              userErrors {
                field
                message
              }
              appSubscription {
                id
                name
              }
              confirmationUrl
            }
          }';

        $body = [
            "query" => $query,
            "variables" => $variable,
        ];
        

        $shop = Session::where('shop', $shopName)->first();
        $response = $shop->graph($body);

       
        $confirmurl = $response['data']['appSubscriptionCreate']['confirmationUrl'];
        $planid = $response['data']['appSubscriptionCreate']['appSubscription']['id'];
        $planname = $response['data']['appSubscriptionCreate']['appSubscription']['name'];
        $arrOfPlan = [
            'id' => $planid,
            'name' => $planname,
            'confirmationUrl' => $confirmurl,
        ];

        return $arrOfPlan;
    }
}
