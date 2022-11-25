<?php
namespace App\Exports;

use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Exports\UsersExport;

class GetSelectedCollectionWithProduct implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $shopurl;

    public function __construct($shopurl, $finalid)
    {
        $this->shopurl = $shopurl;
        $this->id = $finalid;
    }

    public function collection()
    {

        $query = '{
        nodes(ids: [' . $this->id . ']) {
          id
          ... on Collection {
            id
            handle
            title
            descriptionHtml
            sortOrder
            image{
                src
            }
            seo{
                description
                title
            }
            products(first: 10) {
              edges {
                node {
                  id
                  title
                  handle
                  seo { 
                    description
                    title
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

        $collections = $response['data']['nodes'];
        
        // $data = $collections;
        foreach ($collections as $data) {
          

            $collection = $data;

            $product = $collection['products']['edges'];

            foreach ($product as $key => $producthandle) {

                $handle = $producthandle['node'];

                if ($key == 0) {
                    $arrofcsv[] = array(
                        "title" => $collection['title'],
                        'Body (HTML)' => $collection['descriptionHtml'],
                        "handle" => $collection['handle'],
                        'Image' => $collection['image'],
                        'Rules' => '',
                        "products" => $handle['handle'],
                        'Disjunctive' => '',
                        'Sort Order' => $collection['sortOrder'],
                        'Template Suffix' => '',
                        'Published' => 'true',
                        'SEO Title' => $collection['seo']['title'],
                        'SEO Description' => $collection['seo']['description'],

                    );

                } else {
                    $arrofcsv[] = array(
                        "title" => '',
                        'Body (HTML)' => '',
                        "handle" => '',
                        'Image' => '',
                        'Rules' => '',
                        "products" => $handle['handle'],
                        'Disjunctive' => '',
                        'Sort Order' => '',
                        'Template' => '',
                        'Suffix' => '',
                        'Published' => '',
                        'SEO' => '',
                        'Title' => '',
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
            'Image',
            'Rules',
            'Products',
            'Disjunctive',
            'Sort Order',
            'Template Suffix',
            'Published',
            'SEO Title',
            'SEO Description',

        ];
    }
}
