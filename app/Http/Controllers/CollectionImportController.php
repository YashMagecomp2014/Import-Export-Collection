<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Jobs\ImportCsvToShopify;
use App\Models\Collection;
use App\Models\Session;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CollectionImportController extends Controller
{
    protected $FREE_COLLECTION_COUNT = 10;

    public function fileImport(Request $request)
    {
        // validations
        $shopurl = $request->header('url');
        $shop = Session::where('shop', $shopurl)->first();

        $validator = Validator::make($request->file(), [
            'file' => 'required|mimes:csv,txt',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // read file
        $collectionFileData = Excel::ToCollection(new UsersImport, $request->file('file'));
        $collections = $collectionFileData->toArray();

        // print_r($collections);
        // exit;
        // file is empty check
        if (!$collections[0]) {
            return response()->json([
                "status" => 200,
                "data" => [
                    'success' => "",
                    "errors" => "Doesn't Insert Blank CSV",
                ],
            ]);
        }

        // Collumn validation
        $excel_collumn = array_keys($collections[0][0]);
        $final_collumn = ["title", "body_html", "image", "rules", "products", "disjunctive", "sort_order", "template_suffix", "published", "seo_title", "seo_description"];
        $has_change = array_diff($final_collumn, $excel_collumn);
        if (count($has_change) > 0) {
            return response()->json([
                "status" => 200,
                "data" => [
                    'success' => "",
                    "errors" => "Please Follow Rules",
                ],
            ]);
        }

        //check plan
        $charge = $shop->charge()->first();
        if (!$charge) {
            $collectionTitles = $collectionFileData->pluck('*.title')->toArray()[0];
            if (count($collectionTitles) > $this->FREE_COLLECTION_COUNT) {
                return response()->json([
                    "status" => 200,
                    "data" => [
                        'success' => "",
                        "errors" => "You have insert Only $this->FREE_COLLECTION_COUNT Collection",
                    ],
                ]);
            }
        }

        // save file and move to public folder
        $time = time();
        $data = new Collection();
        $file = $request->file('file');
        $filename = $time . $file->getClientOriginalName();
        $path = $file->move(storage_path('app/public/'.$shopurl), $filename);
        $data['file'] = $filename;
        $data['path'] = 'storage/' . $shopurl .'/'. $filename;
        $data['type'] = "Import file";
        $data->shop = $shopurl;
        $data->save();

        // dispatch job 
        dispatch(new ImportCsvToShopify($collectionFileData, $collections[0], $shop, $data));

        return response()->json([
            "status" => 200,
            "data" => [
                "success" => "Csv uploaded successfully",
            ],
        ]);
    }
}
