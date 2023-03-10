<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Session;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function chargeHandle(Request $request)
    {
        info($request->all());
        if ($request->charge_id) {
            $data = new Charge();
            $data->shop = $request->shop;
            $data->charge_id = $request->charge_id;
            $data->plan = $request->plan;
            $data->save();
            $shop = Session::where("shop", $request->shop)->first();
            info($shop);
            info($request->plan);

            $shop->plan = $request->plan;
            $shop->save();

            if($request->plan == 1){
                return redirect()->back();
            }

            return redirect()->to('/login?shop=' . $request->shop);
        } else {
            return redirect()->to('/login?shop=' . $request->shop);
        }
    }
    public function GetChargeID(Request $request)
    {
        $shopurl = $request->header('url');

        $chargedata = Session::where('shop', $shopurl)->first();

        if ($chargedata) {
            return $chargedata;
        } else {
            return [
                'message' => 'You have free user',
            ];
        }
    }

    
}
