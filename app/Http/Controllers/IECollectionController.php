<?php
namespace App\Http\Controllers;

use App\Exports\GetAllProduct;
use App\Exports\GetAllProductNotInAnyCollection;
use App\Exports\GetCollectionWithHandle;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Jobs\ProductCSVJob;
use App\Models\Charge;
use App\Models\Collection;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class IECollectionController extends Controller
{
    public function fileExport(Request $request)
    {
        $shopurl = $request->header('url');
        $collection = Excel::store(new UsersExport($shopurl), 'public/'.$shopurl.'/Get-All-Collection.csv');
        $data = new Collection();
        $data->file = 'Get-All-Collection.csv';
        $data->path = 'storage/'.$shopurl.'/Get-All-Collection.csv';
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
        $shopurl = $request->header('url');
        $collection = Excel::store(new GetCollectionWithHandle($shopurl), 'public/'.$shopurl.'/Get-All-Collection-with-Handle.csv');
        $data = new Collection();
        $data->file = 'Get-All-Collection-with-Handle.csv';
        $data->path = 'storage/'.$shopurl.'/Get-All-Collection-with-Handle.csv';
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
        $shopurl = $request->header('url');
        $collection = Excel::store(new GetAllProduct($shopurl), 'public/'.$shopurl.'/Get-All-Product.csv');
        $data = new Collection();
        $data->file = 'Get-All-Product.csv';
        $data->path = 'storage/'.$shopurl.'/Get-All-Product.csv';
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
        $shopurl = $request->header('url');
        $collection = Excel::store(new GetAllProductNotInAnyCollection($shopurl), 'public/'.$shopurl.'/Get-Product-Not-In-Any-Collection.csv');
        $data = new Collection();
        $data->file = 'Get-Product-Not-In-Any-Collection.csv';
        $data->path = 'storage/'.$shopurl.'/Get-Product-Not-In-Any-Collection.csv';
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
