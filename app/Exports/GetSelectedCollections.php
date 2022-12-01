<?php
namespace App\Exports;

use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Str;

class GetSelectedCollections implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $shopurl;
    protected $finalid;

    public function __construct($shopurl, $finalid)
    {
        $this->shopurl = $shopurl;
        $this->id = $finalid;
    }

    public function collection()
    {

        // print_r($this->id)
        $query = 'query {
            nodes(ids: [' . $this->id . ']) {
              id
              ... on Collection {
                title
                handle
                descriptionHtml
                sortOrder
                image{
                    src
                }
                seo{
                    description
                    title
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

            $sort_order = $collection['sortOrder'];
            
            $sororder = self::Sortorder($sort_order);

            $array = array(
                "title" => $collection['title'],
                'Body (HTML)' => $collection['descriptionHtml'],
                'Rules' => '',
                "products" => '',
                'Disjunctive' => '',
                'Sort Order' => $sororder,
                'Template Suffix' => '',
                'Published' => 'true',
                'SEO Title' => $collection['seo']['title'],
                'SEO Description' => $collection['seo']['description'],

            );
            if(isset($collection['image']['src']) && $collection['image']['src']){
                $array['image'] = $collection['image']['src'];
            }
            $arrofcsv[] = $array;

        }
        
        $collectiondata = collect($arrofcsv);

        return $collectiondata;
    }
    public function headings(): array
    {
        return [
            'Collection',
            'Description',
            'Conditions',
            'Products',
            'Products must match',
            'Sort Order',
            'Template Suffix',
            'Published',
            'SEO Title',
            'SEO Description',
            'Collection Image',
            
        ];
    }
    public function Sortorder($sort_order)
    {

        $sortorder = [
            "alpha_asc" => "Product Title A-Z" ,
            "best_selling" => "Best Selling" ,
            "created" => "Oldest" ,
            "alpha_desc" => "Product Title Z-A" ,
            "price_asc" => "Lowest Price" ,
            "price_desc" => "Highest Price" ,
            "created_desc" => "Newest" ,
            "manual" => "Manually" ,
        ];

        $value = Str::slug($sort_order, "_");

        if (isset($sortorder[$value])) {
            return $sortorder[$value];
        } else {
            return "CREATED";
        }
    }
}
