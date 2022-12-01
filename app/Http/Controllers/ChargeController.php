<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Session;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function chargeHandle(Request $request)
    {
        if ($request->charge_id) {
            $data = new Charge();
            $data->shop = $request->shop;
            $data->charge_id = $request->charge_id;
            $data->save();
            $shop = Session::where("shop", $request->shop)->first();
            if($shop) {
                $shop->plan = 1;
                $shop->save();
            }
            return redirect()->to('/login?shop=' . $request->shop);
        } else {
            return redirect()->to('/login?shop=' . $request->shop);
        }
    }
    public function GetChargeID(Request $request)
    {
        $shopurl = $request->header('url');

        $chargedata = Charge::where('shop', $shopurl)->first('charge_id');

        if ($chargedata) {
            return $chargedata;
        } else {
            return [
                'message' => 'You have free user',
            ];
        }
    }
}
