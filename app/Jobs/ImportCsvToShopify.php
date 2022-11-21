<?php

namespace App\Jobs;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ImportCsvToShopify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $collectionFileData;
    protected $collections;
    protected $shop;

    public $timeout = 10000;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($collectionFileData, $collections, $shop, $data)
    {
        $this->data = $data;
        $this->collectionFileData = $collectionFileData;
        $this->collections = $collections;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        //Using Product Handle Get Product ID
        $producthandle = $this->collectionFileData->pluck('*.products');

        $collection = $this->collectionFileData->toArray();

        $producthandle = $producthandle->toArray()[0];

        $producthandle = array_filter($producthandle);
        $producthandle = array_unique($producthandle);
        $producthandle = array_values($producthandle);

        $errorMessages = [];
        foreach ($producthandle as $handle) {
            $productid = $this->GetProductId($handle, $this->shop);
            if (isset($productid["is_error"]) && $productid["is_error"]) {
                $errorMessages[] = implode(", ", $productid["error"]);
            }
            $product_data[$handle] = $productid;
        }

        // prepare Main data
        $mainData = [];

        foreach ($this->collections as $key => $collection) {

            if ($collection['rules'] && $collection['title']) {

                $mainData[] = $collection;

            } else if ($collection["title"] && $collection['productid']) {

                if (isset($collection['productid'])) {
                    $collection['product_ids'][] = $collection['productid'];
                }
                $mainData[] = $collection;

            } else if (!$collection["title"] && $collection['productid']) {

                $lastindex = array_key_last($mainData);
                if (isset($mainData[$lastindex]) && isset($mainData[$lastindex]['product_ids'])) {
                    $mainData[$lastindex]['product_ids'][] = $collection['productid'];
                } else {
                    $mainData[$lastindex]['product_ids'] = [$collection['productid']];
                }

            } else if (!$collection["title"] && $collection['products']) {

                $lastindex = array_key_last($mainData);
                if (isset($mainData[$lastindex]) && isset($mainData[$lastindex]['product_ids'])) {

                    $handle = $collection['products'];

                    $productid = $product_data[$handle];

                    $mainData[$lastindex]['product_ids'][] = $productid;

                } else {

                    $handle = $collection['products'];

                    $productid = $product_data[$handle];

                    $mainData[$lastindex]['product_ids'] = [$productid];
                }

            } else if ($collection["title"] && $collection['products']) {

                if (isset($collection['products'])) {
                    $handle = $collection['products'];
                    $productid = $product_data[$handle];

                    $collection['product_ids'][] = $productid;
                }
                $mainData[] = $collection;
            } else if ($collection['title'] && $collection['sort_order'] && !$collection['products'] && !$collection['productid'] && !$collection['rules']) {
                $mainData[] = $collection;
            }
        }
        //Maindata to Push collection function
        foreach ($mainData as $rows) {
            if (isset($rows['sort_order']) && $rows['sort_order']) {
                $rows['sort_order'] = strtolower($rows['sort_order']);
            }
            $validator = Validator::make($rows, [
                'title' => 'required',
                'sort_order' => [
                    'required',
                    Rule::in(['manual', 'best-selling', 'alpha-asc', 'alpha-desc', 'price-desc', 'price-asc', 'created-desc', 'created']),
                ],
            ]);
            $error = json_decode($validator->errors(), true);

            if (isset($error['title']) && $error['title']) {

                $isError = true;
                $returnResponse["error"] = [];
                $returnResponse["error"][] = $error['title'][0];
                $returnResponse["is_error"] = true;
                $this->data->errors = $returnResponse["error"];
                $this->data->save();
                return $error['title'];
            } else if (isset($error['sort_order']) && $error['sort_order']) {

                $isError = true;
                $returnResponse["error"] = [];
                $returnResponse["error"][] = $error['sort_order'][0];
                $returnResponse["is_error"] = true;
                $this->data->errors = $returnResponse["error"];
                $this->data->save();
                return $error['sort_order'];
            }

            $result = self::collection($rows, $this->shop);
            if (isset($result["is_error"]) && $result["is_error"]) {
                $errorMessages[] = implode(", ", $result["error"]);
            }
        }

        //Error Message Save in Database
        if ($errorMessages) {
            $this->data->errors = json_encode($errorMessages);
            $this->data->save();
            return response()->json([
                "status" => 200,
                "data" => [
                    "success" => "Csv uploaded successfully",
                    "errors" => $errorMessages,
                ],
            ]);
        }
    }

    public function collection($rows, $shopurl)
    {
        //Rules Array Manage
        if (isset($rows['rules']) && $rows['rules']) {
            $rule = [];
            $rules = explode(',', $rows['rules']);

            foreach ($rules as $ruledata) {

                $lastrules = explode(' ', $ruledata);

                if (count($lastrules) < 3) {
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

        //Graphql Create a Variable
        $variable = [
            "input" => [
                "title" => $rows['title'],
                'handle' => $rows['handle'],
                "descriptionHtml" => $rows['body_html'],
            ],
        ];

        if (!array_key_exists('product_ids', $rows)) {
            $variable['input']['products'] = [
                $rows['productid'],
            ];
        }
        if (isset($rows['product_ids']) && !$rows['product_ids'] == null) {
            $variable['input']['products'] = array_unique($rows['product_ids']);
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

        //Graphql CreateCollection Query
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

        //Create FinalQuery for use Curl
        $finalquery = [
            "query" => $query,
            "variables" => $variable,
        ];

        //Call a curls function
        $result = $this->curls($finalquery, $shopurl);

        $responce = json_encode($result);

        return $responce;

    }
    public function GetProductId($handle, $shopurl)
    {
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
            $returnResponse["error"] = [];
            $returnResponse["error"][] = "Please insert valid product handle";
            $returnResponse["is_error"] = true;
            return $returnResponse;
        }
        $pid = $productid['data']['productByHandle']['id'];

        return $pid;
    }

    public function curls($finalquery, $shopurl)
    {

        $token = Session::where('shop', $shopurl['shop'])->first('access_token');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://' . $shopurl['shop'] . '/admin/api/2022-10/graphql.json');
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

}
