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

        try {
            do {
                $query = self::getCollectionQuery($withProduct, $cursor);
                $body = [
                    "query" => $query,
                ];

                $response = $shop->graph($body);

                if (isset($response["errors"]) && $response["errors"]) {
                    break;
                }
                if (isset($response["data"]["collections"]["pageInfo"]) && $response["data"]["collections"]["pageInfo"]) {
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
                            $productdata = self::getProductHandle($id);
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

    public static function getProductHandle($id)
    {

        // $after = ', after: "' . $cursor . '"';
        $query = '{
          collection(id: "' . $id . '") {
            products(first: 250) {
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
    public static function getCollectionQuery($withProduct, $cursor = "")
    {
        $after = "";
        if ($cursor) {
            $after = ', after: "' . $cursor . '"';
        }
        if ($withProduct) {
            $query = 'query {
                collections(first: 250,' . $after . ') {
                  edges {
                    node {
                      id
                      title
                      descriptionHtml
                      handle
                      productsCount
                      sortOrder
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
        } else {
            $query = 'query {
            collections(first: 250' . $after . ') {
              edges {
                node {
                  id
                  title
                  descriptionHtml
                  handle
                  productsCount
                  sortOrder
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
        }

        return $query;
    }
}
