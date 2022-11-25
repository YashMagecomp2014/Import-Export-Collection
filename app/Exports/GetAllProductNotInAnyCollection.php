<?php
namespace App\Exports;

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

        $query = 'query {
          products(first: 100) {
            edges {
              node {
                id
                title
                descriptionHtml
                vendor
                productType
                handle
                tags
                collections(first: 1) {
                  edges {
                    node {
                      id
                    }
                  }
                }
                variants(first: 1) {
                  edges {
                    cursor
                    node {
                      id
                      title
                      sku
                    }
                  }
                }
              }
            }
          }
        }
        ';

        $body = [
            "query" => $query,
        ];

        $shop = Session::where('shop', $this->shopurl)->first();
        $response = $shop->graph($body);
        
        $products = $response['data']['products']['edges'];

        foreach ($products as $data) {

            // $collectiondata = $data['node']['collections']['edges'];

            // foreach ($collectiondata as $collection) {
            //     $finalcollection = $collection['node'];
            // }

            $product = $data['node'];

            // $price = $product['priceRange']['maxVariantPrice']['amount'];

            $checkincollection = $product['collections']['edges'];

            $countcollection = count($checkincollection);

          

            if ($countcollection > 0) {

              // print_r($countcollection);
              // exit;
                $arrofcsv = [];
                continue;

            }

            $variant = $product['variants']['edges'];

            foreach ($variant as $data) {

                $variantdata = ($data['node']);

                // print_r($variantdata);
                // exit;

                $arrofcsv[] = array(
                    'Product Id' => $product['id'],
                    'Product Title' => $product['title'],
                    'Body (HTML)' => $product['descriptionHtml'],
                    'Vendor' => $product['vendor'],
                    'Product Type' => $product['productType'],
                    'Handle' => $product['handle'],
                    'Product Tags' => $product['tags'],
                    'Variant Title' => $variantdata['title'],
                    'Price' => '',
                    'SKU' => $variantdata['sku'],
                    'Option 1' => $variantdata['title'],
                    'Option 2' => '',
                    'Option 3' => '',
                );
            }
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
            'Variant Title',
            'Price',
            'SKU',
            'Option 1',
            'Option 2',
            'Option 3',
        ];
    }
}
