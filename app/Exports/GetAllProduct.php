<?php
namespace App\Exports;

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

        $query = 'query {
            products(first: 100) {
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

        $shop = $this->shopurl;
        $result = (new Graphql($body))->curls($body, $shop);

        $response = json_decode($result, true);
        $products = $response['data']['products']['edges'];

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

            $product = $data['node'];

            $variant = $product['variants']['edges'];

            foreach ($variant as $data) {

                $variantdata = ($data['node']);

                $price = $product['priceRange']['maxVariantPrice']['amount'];

                $arrofcsv[] = array(
                    'Product Id' => $product['id'],
                    'Product Title' => $product['title'],
                    'Body (HTML)' => $product['descriptionHtml'],
                    'Vendor' => $product['vendor'],
                    'Product Type' => $product['productType'],
                    'Handle' => $product['handle'],
                    'Product Tags' => $product['tags'],
                    'Variant Title' => $variantdata['title'],
                    'Price' => $price,
                    'SKU' => $variantdata['sku'],
                    'Option 1' => $variantdata['title'],
                    'Option 2' => '',
                    'Option 3' => '',

                );
            }

        }

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
