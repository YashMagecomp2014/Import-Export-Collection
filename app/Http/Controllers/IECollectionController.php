<?php
namespace App\Http\Controllers;

use App\Exports\GetAllProduct;
use App\Exports\GetAllProductNotInAnyCollection;
use App\Exports\GetCollectionWithHandle;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Charge;
use App\Models\Collection;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class IECollectionController extends Controller
{
    public function fileImport(Request $request)
    {
        $shopurl = $request->header('url');
        $validator = Validator::make($request->file(), [
            'file' => 'required|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Read file and convert to collections.
        $collection = Excel::ToCollection(new UsersImport, $request->file('file'));
        $collection = $collection->toArray();

        $mainData = [];

        // Save data and upload file.
        $data = new Collection();
        if ($request->file('file')) {
            $file = $request->file('file');
            $filename = date('YmdHi') . $file->getClientOriginalName();
            $path = $file->move(public_path('public/file'), $filename);
            $data['file'] = $filename;
            $data['path'] = 'public/file/' . $filename;
            $data['type'] = "Import file";
            $data->shop = $shopurl;
        }
        $data->save();

        if (!$collection[0]) {
            $isError = true;
            $returnResponse["error"] = [];
            $returnResponse["error"][] = "Doesn't Insert Blank CSV";
            $returnResponse["is_error"] = true;
            $data->errors = $returnResponse["error"];
            $data->save();

            return response()->json([
                "status" => 200,
                "data" => [
                    'success' => "",
                    "errors" => "Doesn't Insert Blank CSV",
                ],
            ]);
        }

        if (!array_key_exists('title', $collection[0][0]) || !array_key_exists('body_html', $collection[0][0]) || !array_key_exists('handle', $collection[0][0]) || !array_key_exists('rules', $collection[0][0]) || !array_key_exists('products', $collection[0][0]) || !array_key_exists('disjunctive', $collection[0][0]) || !array_key_exists('sort_order', $collection[0][0]) || !array_key_exists('template_suffix', $collection[0][0]) || !array_key_exists('published', $collection[0][0]) || !array_key_exists('seo_title', $collection[0][0]) || !array_key_exists('seo_description', $collection[0][0])) {
            $isError = true;
            $returnResponse["error"] = [];
            $returnResponse["error"][] = "Please Follow Rules";
            $returnResponse["is_error"] = true;
            $data->errors = $returnResponse["error"];
            $data->save();
            return response()->json([
                "status" => 200,
                "data" => [
                    'success' => "",
                    "errors" => "Please Follow Rules",
                ],
            ]);
        }
        foreach ($collection as $items) {
            foreach ($items as $item) {
               if(!$item["title"] && $item['productid']) {
                    $lastindex = array_key_last($mainData);
                    if (isset($mainData[$lastindex]) && isset($mainData[$lastindex]['product_ids'])) {
                        $mainData[$lastindex]['product_ids'][] = $item['productid'];
                    } else {
                        $mainData[$lastindex]['product_ids'] = [$item['productid']];
                    }

                } else if (!$item["title"] && $item['products']) {
                    $lastindex = array_key_last($mainData);
                    if (isset($mainData[$lastindex]) && isset($mainData[$lastindex]['product_handles'])) {
                        $mainData[$lastindex]['product_handles'][] = $item['products'];
                    } else {
                        $mainData[$lastindex]['product_handles'] = [$item['products']];
                    }

                }else {

                    if (isset($item['products'])) {
                        $item['product_handles'] = [$item['products']];
                    }
                    $mainData[] = $item;
                }

                foreach ($mainData as $items) {

                    $validator = Validator::make($items, [
                        'title' => 'required',
                        'sort_order' => [
                            'required',
                            Rule::in(['manual', 'best-selling', 'alpha-asc', 'alpha-desc', 'price-desc', 'price-asc', 'created-desc', 'created']),
                        ],
                    ]);
                    $error = json_decode($validator->errors(), true);
                    if (isset($error['sort_order']) && $error['sort_order']) {
                        if ($validator->fails()) {
                            $isError = true;
                            $returnResponse["error"] = [];
                            $returnResponse["error"][] = "Sort order must be one of: manual, best-selling, alpha-asc, alpha-desc, price-desc, price-asc, created-desc, created";
                            $returnResponse["is_error"] = true;
                            $data->errors = $returnResponse["error"];
                            $data->save();
                            return response()->json($validator->errors());
                        }
                    } else if (isset($error['title']) && $error['title']) {
                        if ($validator->fails()) {
                            $isError = true;
                            $returnResponse["error"] = [];
                            $returnResponse["error"][] = 'Required Title';
                            $returnResponse["is_error"] = true;
                            $data->errors = $returnResponse["error"];
                            $data->save();
                            return response()->json($validator->errors());
                        }
                    }
                }
            }
        }

        return $mainData;

        $chargedata = Charge::where('shop', $shopurl)->first('charge_id');

        if (isset($chargedata->charge_id) && $chargedata->charge_id) {
            $plan = "prime";
        } else {
            $plan = 'free';
        }

        if (count($mainData) > 10 && $plan == "free") {
            $isError = true;
            $returnResponse["error"] = [];
            $returnResponse["error"][] = "You have insert Only 10 Collection";
            $returnResponse["is_error"] = true;
            $data->errors = $returnResponse["error"];
            $data->save();
            return response()->json([
                "status" => 200,
                "data" => [
                    'success' => "",
                    "errors" => "You have insert Only 10 Collection",
                ],
            ]);
        }

        $errorMessages = [];
        foreach ($mainData as $rows) {
            $result = self::collection($rows, $shopurl);
            if (isset($result["is_error"]) && $result["is_error"]) {
                $errorMessages[] = implode(", ", $result["error"]);
            }
        }

        if ($errorMessages) {
            $data->errors = json_encode($errorMessages);
            $data->save();
            return response()->json([
                "status" => 200,
                "data" => [
                    "success" => "Csv uploaded successfully",
                    "errors" => $errorMessages,
                ],
            ]);
        } else {
            return response()->json([
                "status" => 200,
                "data" => [
                    "success" => "Csv uploaded successfully",
                ],
            ]);
        }
    }

    public function collection($rows, $shopurl)
    {
       
        if ($rows['products']) {

            if (isset($rows['product_handles']) && $rows['product_handles']) {
                foreach ($rows['product_handles'] as $handle) {
                    $productbyhandle = [
                        "handle" => $handle,
                    ];

                    $productbyhandlequery = 'query getProductIdFromHandle($handle: String!) {
        productByHandle(handle: $handle) {
        id
        }
        }';

                    $finalquery = [
                        "query" => $productbyhandlequery,
                        "variables" => $productbyhandle,
                    ];

                    $result = $this->curls($finalquery, $shopurl);

                    $productid = json_decode($result, true);

                    $invalidhandle = $productid['data'];

                    if ($invalidhandle['productByHandle'] == null) {

                        $isError = true;
                        $returnResponse["error"] = [];

                        $returnResponse["error"][] = "Please insert valid product handle";

                        $returnResponse["is_error"] = true;
                        return $returnResponse;

                    }
                    $pid[] = $productid['data']['productByHandle']['id'];
                }
            }
        }

        if (isset($rows['rules']) && $rows['rules']) {
            $rule = [];
            $rules = explode(',', $rows['rules']);

            foreach ($rules as $ruledata) {

                $lastrules = explode(' ', $ruledata);

                if (count($lastrules) < 3) {

                    $isError = true;
                    $returnResponse["error"] = [];
                    $returnResponse["error"][] = $rows['title'] . 'Please insert valid Rules';
                    $returnResponse["is_error"] = true;

                    return $returnResponse;

                    $lastrules[1] = '';
                    $lastrules[2] = '';
                }

                $rule[] = [
                    'column' => strtoupper($lastrules[0]),
                    'relation' => strtoupper($lastrules[1]),
                    'condition' => strtoupper($lastrules[2]),
                ];

            }
        }

       
        $variable = [
            "input" => [
                "title" => $rows['title'],
                'handle' => $rows['handle'],
                "descriptionHtml" => $rows['body_html'],
            ],
        ];

        if (isset($rows['product_handles']) && !$rows['product_handles'] == null) {
           
            $variable['input']['products'] = $pid;
        }

        if(!array_key_exists('product_ids', $rows)){
            $variable['input']['products'] = [
                $rows['productid']
            ];
        }
        if (isset($rows['product_ids']) && !$rows['product_ids'] == null) {
           
            $variable['input']['products'] = $rows['product_ids'];
        }

        if (isset($rule) && !$rule == null) {

            $variable['input']['ruleSet'] = [
                "appliedDisjunctively" => $rows['disjunctive'],
                "rules" => $rule,
            ];
        }
        if (isset($rows['seo']) && !$rows['seo'] == null) {

            $variable['input']['seo'] = [
                "description" => $rows['seo_description'],
                "title" => $rows['seo_title'],
            ];
        }
        if (isset($rows['image']) && !$rows['image'] == null) {

            $variable['input']['image'] = [
                'src' => $rows['image'],
                'altText' => "logo-school",
            ];
        }

        // print_r($variable);
        // exit;

        $query = 'mutation CollectionCreate($input: CollectionInput!) {
            collectionCreate(input: $input) {
            userErrors {
              field
              message
            }
            collection {
              id
              title
              descriptionHtml
              handle
              sortOrder
              image {
                src
                altText
              }
              seo {
                description
                title
              }
              ruleSet {
                appliedDisjunctively
                rules {
                  column
                  relation
                  condition
                }
              }
            }
            }
            }';

        $finalquery = [
            "query" => $query,
            "variables" => $variable,
        ];

        $result = $this->curls($finalquery, $shopurl);

        $responce = json_encode($result);

        return $responce;

    }

    public function curls($finalquery, $shopurl)
    {
        $token = Session::where('shop', $shopurl)->first('access_token');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://' . $shopurl . '/admin/api/2022-10/graphql.json');
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
        return $result;
    }

    public function fileExport(Request $request)
    {
        $shopurl = $request->header('url');
        $collection = Excel::store(new UsersExport($shopurl), 'public/Get-All-Collection.csv');
        $data = new Collection();
        $data->file = 'Get-All-Collection.csv';
        $data->path = 'storage/Get-All-Collection.csv';
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
        $collection = Excel::store(new GetCollectionWithHandle($shopurl), 'public/Get-All-Collection-with-Handle.csv');
        $data = new Collection();
        $data->file = 'Get-All-Collection-with-Handle.csv';
        $data->path = 'storage/Get-All-Collection-with-Handle.csv';
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
        $collection = Excel::store(new GetAllProduct($shopurl), 'public/Get-All-Product.csv');
        $data = new Collection();
        $data->file = 'Get-All-Product.csv';
        $data->path = 'storage/Get-All-Product.csv';
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
        $collection = Excel::store(new GetAllProductNotInAnyCollection($shopurl), 'public/Get-Product-Not-In-Any-Collection.csv');
        $data = new Collection();
        $data->file = 'Get-Product-Not-In-Any-Collection.csv';
        $data->path = 'storage/Get-Product-Not-In-Any-Collection.csv';
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
