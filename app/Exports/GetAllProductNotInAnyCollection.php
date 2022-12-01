<?php
namespace App\Exports;

use App\Helpers\CommonHelpers;
use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GetAllProductNotInAnyCollection implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $shopurl;

    public function __construct($shopurl)
    {
        $this->shopurl = $shopurl;
    }

    public function collection()
    {

        // $query = 'query {
        //   products(first: 100) {
        //     edges {
        //       node {
        //         id
        //         title
        //         descriptionHtml
        //         vendor
        //         productType
        //         handle
        //         tags
        //         priceRange {
        //            maxVariantPrice {
        //               amount
        //            }
        //         }
        //         collections(first: 1) {
        //           edges {
        //             node {
        //               id
        //             }
        //           }
        //         }
        //       }
        //     }
        //   }
        // }
        // ';

        // $body = [
        //     "query" => $query,
        // ];

       
        // $response = $shop->graph($body);

        $shop = Session::where('shop', $this->shopurl)->first();
        $products = CommonHelpers::getAllProducts($shop, $withCollection = true);

        // $products = $response['data']['products']['edges'];

        // print_r($products);
        // exit;

        foreach ($products as $data) {

            $product = $data;

            $checkincollection = $product['collections']['edges'];

            $countcollection = count($checkincollection);

            if ($countcollection > 0) {

                $arrofcsv = [];
                continue;

            }

            $price = $product['priceRange']['maxVariantPrice']['amount'];
            $arrofcsv[] = array(
                'Product Id' => $product['id'],
                'Product Title' => $product['title'],
                'Body (HTML)' => $product['descriptionHtml'],
                'Vendor' => $product['vendor'],
                'Product Type' => $product['productType'],
                'Handle' => $product['handle'],
                'Product Tags' => $product['tags'],
                'Price' => $price,
                'SKU' => '',
                'Option 1' => '',
                'Option 2' => '',
                'Option 3' => '',
            );
        }

        $productsdata = collect($arrofcsv);

        return $productsdata;
    }
    public function headings(): array
    {
        return [
            'Product Id',
            'Product Title',
            'Body (HTML)',
            'Vendor',
            'Product Type',
            'Handle',
            'Product Tags',
            'Price',
            'SKU',
            'Option 1',
            'Option 2',
            'Option 3',
        ];
    }
}
