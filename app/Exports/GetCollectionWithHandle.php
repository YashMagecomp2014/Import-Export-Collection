<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GetCollectionWithHandle implements FromCollection, WithHeadings
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
            collections(first: 50) {
              edges {
                cursor
                node {
                  title
                  descriptionHtml
                  handle
                  updatedAt
                  sortOrder
                  image {
                    src
                  }
                  seo {
                    description
                    title
                   }
                  products(first: 10){
                    edges{
                      cursor
                      node{
                        handle
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
        $collections = $response['data']['collections']['edges'];

        if (!$collections) {
            $arrofcsv[] = array(
                "title" => '',
                'Body (HTML)' => '',
                "handle" => '',
                'Rules' => '',
                "products" => '',
                'Disjunctive' => '',
                'Sort Order' => '',
                'Template Suffix' => '',
                'Published' => '',
                'SEO Title' => '',
                'SEO Description' => '',

            );
        }

        // $data = $collections;
        foreach ($collections as $data) {

            $collection = $data['node'];

            $product = $collection['products']['edges'];

            foreach ($product as $key => $producthandle) {

                $handle = $producthandle['node'];

                if ($key == 0) {
                    $array = array(
                        "title" => $collection['title'],
                        'Body (HTML)' => $collection['descriptionHtml'],
                        "handle" => $collection['handle'],
                        'Rules' => '',
                        "products" => $handle['handle'],
                        'Disjunctive' => '',
                        'Sort Order' => $collection['sortOrder'],
                        'Template Suffix' => '',
                        'Published' => 'true',
                        'SEO Title' => $collection['seo']['title'],
                        'SEO Description' => $collection['seo']['description'],

                    );

                    if (isset($collection['image']['src']) && $collection['image']['src']) {
                        $array['image'] = $collection['image']['src'];
                    }
                    $arrofcsv[] = $array;

                } else {
                    $arrofcsv[] = array(
                        "title" => '',
                        'Body (HTML)' => '',
                        "handle" => '',
                        'Rules' => '',
                        "products" => $handle['handle'],
                        'Disjunctive' => '',
                        'Sort Order' => '',
                        'Template Suffix' => '',
                        'Published' => '',
                        'SEO Title' => '',
                        'SEO Description' => '',

                    );

                }

            }

        }

        $result = collect($arrofcsv);

        return $result;
    }
    public function headings(): array
    {
        return [

            'Title',
            'Body (HTML)',
            'Handle',
            'Rules',
            'Products',
            'Disjunctive',
            'Sort Order',
            'Template Suffix',
            'Published',
            'SEO Title',
            'SEO Description',
            'Image',
            'productid',

        ];
    }
}
