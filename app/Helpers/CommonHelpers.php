<?php

namespace App\Helpers;

use App\Models\Session;
use Exception;
use Illuminate\Support\Facades\Log;

class CommonHelpers
{
    /**
     * get all collection of store
     * @param Session $shop
     * @param boolean $withProduct
     * @return Collection
     */
    public static function getAllCollections($shop, $withProduct = false)
    {
        $cursor = "";
        $collections = [];
        $isFree = $shop->plan ? false : true;

        try {
            do {
                $query = self::getCollectionQuery($withProduct, $cursor, $isFree);
                $body = [
                    "query" => $query,
                ];

                $response = $shop->graph($body);

                if (isset($response["errors"]) && $response["errors"]) {
                    break;
                }
                if (isset($response["data"]["collections"]["pageInfo"]) && $response["data"]["collections"]["pageInfo"] && !$isFree) {
                    $pageInfo = $response["data"]["collections"]["pageInfo"];
                } else {
                    $pageInfo = null;
                }
                if (isset($response["data"]["collections"]["edges"]) && count($response["data"]["collections"]["edges"]) > 0) {
                    $nodes = array_column($response["data"]["collections"]["edges"], "node");
                    $lastIndex = count($response["data"]["collections"]["edges"]) - 1;
                    if (isset($response["data"]["collections"]["edges"][$lastIndex]["cursor"]) && $response["data"]["collections"]["edges"][$lastIndex]["cursor"]) {
                        $cursor = $response["data"]["collections"]["edges"][$lastIndex]["cursor"];
                    }
                    if ($withProduct) {
                        foreach ($nodes as $index => $node) {
                            // if (isset($node["productsCount"]) && (int) $node["productsCount"] > 10) {

                            $id = $node['id'];
                            $productdata = self::getProductHandle($id, $isFree);
                            $body = [
                                "query" => $productdata,
                            ];
                            $responses = $shop->graph($body);
                            $nodes[$index]['products'] = $responses['data']['collection']['products'];
                        }
                    }
                    $collections = array_merge($collections, $nodes);
                }

                if ($response['extensions']['cost']['throttleStatus']['currentlyAvailable'] < 200) {
                    sleep(4);
                }
            } while ($pageInfo && $pageInfo['hasNextPage']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return collect($collections);

    }

    /**
     * get collection query.
     * @param boolean $withProduct
     * @param string $cursor
     * @return string
     */

    public static function getProductHandle($id, $isFree)
    {
        if ($isFree) {
            $first = 10;
        } else {
            $first = 250;
        }

        // $after = ', after: "' . $cursor . '"';
        $query = '{
          collection(id: "' . $id . '") {
            products(first: ' . $first . ') {
              edges {
                cursor
                node {
                  handle
                }
              }
              pageInfo {
                hasNextPage
              }
            }
          }
        }';

        return $query;

    }
    public static function getCollectionQuery($withProduct, $cursor = "", $isFree = false)
    {
        if ($isFree) {
            $first = 10;
        } else {
            $first = 180;
        }
        $after = "";
        if ($cursor) {
            $after = ', after: "' . $cursor . '"';
        }
        $query = 'query {
                collections(first: ' . $first . ',' . $after . ') {
                  edges {
                    node {
                      id
                      title
                      descriptionHtml
                      handle
                      productsCount
                      sortOrder
                      ruleSet {
                        appliedDisjunctively
                        rules {
                          column
                          relation
                          condition
                        }
                      }
                      image {
                        src
                      }
                      seo {
                        description
                        title
                      }
                    }
                    cursor
                  }
                  pageInfo {
                    hasNextPage
                  }
                }
              }';

        return $query;
    }
    public static function getAllProducts($shop, $withCollection = false)
    {
        $cursor = "";
        $product = [];
        $isFree = $shop->plan ? false : true;

        try {
            do {
                $query = self::getProductsQuery($cursor, $withCollection, $isFree);
                $body = [
                    "query" => $query,
                ];

                $response = $shop->graph($body);

                if (isset($response["errors"]) && $response["errors"]) {
                    break;
                }
                if (isset($response["data"]["products"]["pageInfo"]) && $response["data"]["products"]["pageInfo"] && !$isFree) {
                    $pageInfo = $response["data"]["products"]["pageInfo"];
                } else {
                    $pageInfo = null;
                }
                if (isset($response["data"]["products"]["edges"]) && count($response["data"]["products"]["edges"]) > 0) {
                    $nodes = array_column($response["data"]["products"]["edges"], "node");
                    $lastIndex = count($response["data"]["products"]["edges"]) - 1;
                    if (isset($response["data"]["products"]["edges"][$lastIndex]["cursor"]) && $response["data"]["products"]["edges"][$lastIndex]["cursor"]) {
                        $cursor = $response["data"]["products"]["edges"][$lastIndex]["cursor"];
                    }

                    if ($withCollection) {
                        foreach ($nodes as $index => $node) {
                            // if (isset($node["productsCount"]) && (int) $node["productsCount"] > 10) {

                            // print_r($node);
                            // exit;
                            $id = $node['id'];
                            $productdata = self::GetCollection($id);
                            $body = [
                                "query" => $productdata,
                            ];
                            $responses = $shop->graph($body);
                            $nodes[$index]['collections'] = $responses['data']['product']['collections'];
                        }
                    }
                    $products = array_merge($product, $nodes);
                }

                if ($response['extensions']['cost']['throttleStatus']['currentlyAvailable'] < 200) {
                    sleep(4);
                }
            } while ($pageInfo && $pageInfo['hasNextPage']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return collect($products);

    }

    public static function getProductsQuery($cursor, $withCollection, $isFree)
    {

        if ($isFree) {
            $first = 10;
        } else {
            $first = 250;
        }
        $query = '{
          products(first: ' . $first . ') {
            edges {
              cursor
              node {
                id
                title
                descriptionHtml
                vendor
                productType
                handle
                tags
                priceRange {
                  maxVariantPrice {
                    amount
                  }
                }
              }
            }
            pageInfo {
              hasPreviousPage
            }
          }
        }
        ';

        return $query;

    }

    public static function GetCollection($id)
    {
        $query = '{
          product(id: "' . $id . '") {
            collections (first: 1){
              edges {
                node {
                  id
                }
              }
            }
          }
        }
        ';

        return $query;
    }
}
