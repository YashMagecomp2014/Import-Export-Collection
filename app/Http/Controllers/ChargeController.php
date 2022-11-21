<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function chargeHandle(Request $request)
    {
        $data = new Charge();
        $data->shop = $request->shop;
        $data->charge_id = $request->charge_id;
        $data->save();
        return redirect()->to('/login?shop=' . $request->shop);
    }
    public function GetChargeID(Request $request)
    {
        $shopurl = $request->header('url');
      
        $chargedata = Charge::where('shop', $shopurl)->first('charge_id');

        if($chargedata){
            return $chargedata;
        }else{
            return [
                'message' => 'you have free user'
            ];
        }
    }
}
