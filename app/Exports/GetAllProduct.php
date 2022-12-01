<?php
namespace App\Exports;

use App\Helpers\CommonHelpers;
use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GetAllProduct implements FromCollection, WithHeadings
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
        //     products(first: 200) {
        //       edges {
        //         cursor
        //         node {
        //           id
        //           title
        //           descriptionHtml
        //           vendor
        //           productType
        //           handle
        //           tags
        //           priceRange {
        //             maxVariantPrice {
        //               amount
        //             }
        //           }
        //         }
        //       }
        //     }
        //   }
        //   ';

        // $body = [
        //     "query" => $query,
        // ];

        $shop = Session::where('shop', $this->shopurl)->first();
        $products = CommonHelpers::getAllProducts($shop);

        // print_r($products);
        // exit;
        
        

        if (!$products) {
            $arrofcsv[] = array(
                'Product Id' => '',
                'Product Title' => '',
                'Body (HTML)' => '',
                'Vendor' => '',
                'Product Type' => '',
                'Handle' => '',
                'Product Tags' => '',
                'Variant Title' => '',
                'Price' => '',
                'SKU' => '',
                'Option 1' => '',
                'Option 2' => '',
                'Option 3' => '',

            );

        }

        $data = $products;
        foreach ($products as $data) {

            $product = $data;

            // $variant = $product['variants']['edges'];

            // foreach ($variant as $data) {

            // $variantdata = ($data['node']);

            $price = $product['priceRange']['maxVariantPrice']['amount'];

            $arrofcsv[] = array(
                'Product Id' => $product['id'],
                'Product Title' => $product['title'],
                'Body (HTML)' => $product['descriptionHtml'],
                'Vendor' => $product['vendor'],
                'Product Type' => $product['productType'],
                'Handle' => $product['handle'],
                'Product Tags' => $product['tags'],
                'Variant Title' => '',
                'Price' => $price,
                'SKU' => '',
                'Option 1' => '',
                'Option 2' => '',
                'Option 3' => '',

            );
        }

        // }

        // print_r($product);
        // exit;
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
            'Variant Title',
            'Price',
            'SKU',
            'Option 1',
            'Option 2',
            'Option 3',

        ];
    }
}
