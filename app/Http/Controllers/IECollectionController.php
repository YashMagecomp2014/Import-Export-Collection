<?php
namespace App\Http\Controllers;

use App\Exports\GetAllProduct;
use App\Exports\GetAllProductNotInAnyCollection;
use App\Exports\GetCollectionWithHandle;
use App\Exports\UsersExport;
use App\Models\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IECollectionController extends Controller
{
    public function fileExport(Request $request)
    {
        $time = time();
        $shopurl = $request->header('url');
        Excel::store(new UsersExport($shopurl), 'public/' . $shopurl . '/' . $time . 'Get-All-Collection.csv');
        $data = new Collection();
        $data->file = $time. 'Get-All-Collection.csv';
        $data->path = 'storage/' . $shopurl . '/' .$time. 'Get-All-Collection.csv';
        $data->type = 'Export File All Collection';
        $data->shop = $shopurl;
        $data->save();

        return response()->json([
            "Success" => "Get-All-Collection.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);
    }

    public function fileExportwithproduct(Request $request)
    {
        $time = time();
        $shopurl = $request->header('url');
        Excel::store(new GetCollectionWithHandle($shopurl), 'public/' . $shopurl . '/' .$time. 'Get-All-Collection-with-Handle.csv');
        $data = new Collection();
        $data->file = $time. 'Get-All-Collection-with-Handle.csv';
        $data->path = 'storage/' . $shopurl . '/' .$time. 'Get-All-Collection-with-Handle.csv';
        $data->type = 'Export File All Collection With Product';
        $data->shop = $shopurl;
        $data->save();

        return response()->json([
            "Success" => "Get-All-Collection-with-Handle.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);
    }
    public function GetAllProduct(Request $request)
    {
        $time = time();
        $shopurl = $request->header('url');
        Excel::store(new GetAllProduct($shopurl), 'public/' . $shopurl . '/' .$time. 'Get-All-Product.csv');
        $data = new Collection();
        $data->file = $time. 'Get-All-Product.csv';
        $data->path = 'storage/' . $shopurl . '/' .$time. 'Get-All-Product.csv';
        $data->type = 'Export File All Product';
        $data->shop = $shopurl;
        $data->save();

        return response()->json([
            "Success" => "Get-All-Product.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);

    }
    public function GetAllProductNotInAnyCollection(Request $request)
    {
        $time = time();
        $shopurl = $request->header('url');
        Excel::store(new GetAllProductNotInAnyCollection($shopurl), 'public/' . $shopurl . '/' .$time. 'Get-Product-Not-In-Any-Collection.csv');
        $data = new Collection();
        $data->file =  $time. 'Get-Product-Not-In-Any-Collection.csv';
        $data->path = 'storage/' . $shopurl . '/' .$time. 'Get-Product-Not-In-Any-Collection.csv';
        $data->type = 'Export File Products not in collection';
        $data->shop = $shopurl;
        $data->save();

        return response()->json([
            "Success" => "Get-Product-Not-In-Any-Collection.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);

    }
}
