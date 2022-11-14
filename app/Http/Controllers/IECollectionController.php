<?php
namespace App\Http\Controllers;

use App\Exports\GetAllProduct;
use App\Exports\GetAllProductNotInAnyCollection;
use App\Exports\GetCollectionWithHandle;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Collection;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class IECollectionController extends Controller
{
    public function fileImport(Request $request)
    {
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
        

        foreach ($collection as $items) {

            foreach ($items as $item) {

                if (!array_key_exists('title', $item) || !array_key_exists('body_html', $item) || !array_key_exists('handle', $item) || !array_key_exists('image', $item) || !array_key_exists('rules', $item) || !array_key_exists('products', $item) || !array_key_exists('disjunctive', $item) || !array_key_exists('sort_order', $item) || !array_key_exists('template_suffix', $item) || !array_key_exists('published', $item)) {

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
                if (!$item["title"] && $item['products']) {
                    $lastindex = array_key_last($mainData);
                    if (isset($mainData[$lastindex]) && isset($mainData[$lastindex]['product_handles'])) {
                        $mainData[$lastindex]['product_handles'][] = $item['products'];
                    } else {
                        $mainData[$lastindex]['product_handles'] = [$item['products']];
                    }

                } else {

                    if (isset($item['products'])) {
                        $item['product_handles'] = [$item['products']];
                    }
                    $mainData[] = $item;
                }

                foreach ($mainData as $items) {

                    $validator = Validator::make($items, [
                        'title' => 'required',
                    ]);
                    if ($validator->fails()) {
                        $isError = true;
                        $returnResponse["error"] = [];
                        $returnResponse["error"][] = 'Required Title';
                        $returnResponse["is_error"] = true;
                        $data->errors = $returnResponse["error"];
                        $data->save();

                        return response()->json($validator->errors());
                    }
                    $validator = Validator::make($items, [
                        'sort_order' => 'required',
                    ]);

                    if ($validator->fails()) {
                        $isError = true;
                        $returnResponse["error"] = [];
                        $returnResponse["error"][] = "Sort order must be one of: manual, best-selling, alpha-asc, alpha-desc, price-desc, price-asc, created-desc, created";
                        $returnResponse["is_error"] = true;
                        $data->errors = $returnResponse["error"];
                        $data->save();

                        return response()->json($validator->errors());
                    }
                }
            }
        }

        $shopurl = $request->header('url');
        $errorMessages = [];
        foreach ($mainData as $rows) {
            $result = self::collection($rows, $shopurl, $data);
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

    public function collection($rows, $shopurl, $data)
    {

        // print_r($rows);
        // exit;
        if (isset($rows['rules']) && $rows['rules']) {

            $rules = explode(' ', $rows['rules']);

            if (!array_key_exists(1, $rules)) {

                $data->errors = $rows['title'] . 'Please insert valid Rules';
                $data->save();
                $isError = true;
                $returnResponse["error"] = [];
                $returnResponse["error"][] = $rows['title'] . 'Please insert valid Rules';
                $returnResponse["is_error"] = true;

                return $returnResponse;

                $rules[1] = '';
                $rules[2] = '';
            }

            $variable = [
                "input" => [
                    "title" => $rows['title'],
                    'handle' => $rows['handle'],
                    "descriptionHtml" => "View <b>every</b> shoe available in our store.",
                    "ruleSet" => [
                        "appliedDisjunctively" => $rows['disjunctive'],
                        "rules" => [
                            "column" => strtoupper($rules[0]),
                            "relation" => strtoupper($rules[1]),
                            "condition" => strtoupper($rules[2]),
                        ],
                    ],
                ],
            ];

        } else if ($rows['products']) {

            $variable = [
                "input" => [
                    "title" => $rows['title'],
                    'handle' => $rows['handle'],
                    "descriptionHtml" => "View <b>every</b> shoe available in our store.",
                ],
            ];

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

            $collectionid = json_decode($result, true);
            if (isset($collectionid['data']['collectionCreate']['userErrors']) && count($collectionid['data']['collectionCreate']['userErrors']) > 0) {
                $isError = true;
                $returnResponse["error"] = [];
                foreach ($collectionid['data']['collectionCreate']['userErrors'] as $error) {
                    $returnResponse["error"][] = $rows['handle'] . ' ' . $error['message'];
                }
                $returnResponse["is_error"] = true;
                return $returnResponse;
            }
            $cid = $collectionid['data']['collectionCreate']['collection']['id'];

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

                        $data->errors = "Please insert valid product handle";
                        $data->save();

                        $isError = true;
                        $returnResponse["error"] = [];

                        $returnResponse["error"][] = "Please insert valid product handle";

                        $returnResponse["is_error"] = true;
                        return $returnResponse;

                    }
                    $pid[] = $productid['data']['productByHandle']['id'];
                }

                //=====================***===============================
                // Add Product using COLLECTIONID
                //=====================***===============================

                $collectionwithproduct = [
                    "id" => $cid,
                    "productIds" => [],
                ];
                foreach ($pid as $productid) {
                    $collectionwithproduct['productIds'][] = $productid;
                }
                $querys = 'mutation collectionAddProducts($id: ID!, $productIds: [ID!]!) {
            collectionAddProducts(id: $id, productIds: $productIds) {
            collection {
              id
              title
              productsCount
              products(first: 10) {
                nodes {
                  id
                  title
                  handle
                }
              }
            }
            userErrors {
              field
              message
            }
            }
            }';
                $finalquery = [
                    "query" => $querys,
                    "variables" => $collectionwithproduct,
                ];

                return $this->curls($finalquery, $shopurl);
            }

        } else {

            // print_r($rows);
            // exit;
            $variable = [
                "input" => [
                    "title" => $rows['title'],
                    'handle' => $rows['handle'],
                    "descriptionHtml" => "View <b>every</b> shoe available in our store.",
                    "ruleSet" => [
                        "appliedDisjunctively" => $rows['disjunctive'],
                        "rules" => [
                            "column" => "TITLE",
                            "relation" => "CONTAINS",
                            "condition" => "shoe",
                        ],
                    ],
                ],
            ];
        }

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
        $collectionid = json_decode($result, true);

        if (isset($collectionid['data']['collectionCreate']['userErrors']) && count($collectionid['data']['collectionCreate']['userErrors']) > 0) {
            $isError = true;
            $returnResponse["error"] = [];
            foreach ($collectionid['data']['collectionCreate']['userErrors'] as $error) {
                $returnResponse["error"][] = $rows['handle'] . ' ' . $error['message'];
            }
            $returnResponse["is_error"] = true;
            return $returnResponse;
        }
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
        $data->save();

        return response()->json([
            "Success" => "Get-Product-Not-In-Any-Collection.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);

    }
}
