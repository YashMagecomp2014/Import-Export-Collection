<?php
namespace App\Exports;

use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Str;

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
            ruleSet {
                appliedDisjunctively
                rules {
                  column
                  relation
                  condition
                }
              }
            image{
                src
            }
            seo{
                description
                title
            }
            products(first: 250) {
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

            $sort_order = $collection['sortOrder'];
            
            $sororder = self::Sortorder($sort_order);

            $rule = '';
            $Disjunctive = '';
            if (isset($data['ruleSet']) && $data['ruleSet']) {
                $rules = $data['ruleSet']['rules'];

                $Disjunctives = $data['ruleSet']['appliedDisjunctively'];
                
                if($Disjunctives == 1){
                    $Disjunctive = 'any';
                }else{
                    $Disjunctive = 'all';
                }

                foreach ($rules as $value) {

                    $rule .= $value['column'] . ' ' . $value['relation'] . ' ' . $value['condition'] . ',';

                }
            }

            $product = $collection['products']['edges'];

            foreach ($product as $key => $producthandle) {

                $handle = $producthandle['node'];

                if ($key == 0) {
                    $array = array(
                        "title" => $collection['title'],
                        'Body (HTML)' => $collection['descriptionHtml'],
                        'Rules' => $rule,
                        "products" => $handle['handle'],
                        'Disjunctive' => $Disjunctive,
                        'Sort Order' => $sororder,
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
                        'Rules' => '',
                        "products" => $handle['handle'],
                        'Disjunctive' => '',
                        'Sort Order' => '',
                        'Template Suffix' => '',
                        'Published' => '',
                        'SEO Title' => '',
                        'SEO Description' => '',
                        'Image' => '',

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
            "alpha_asc" => "Product Title A-Z",
            "best_selling" => "Best Selling",
            "created" => "Oldest",
            "alpha_desc" => "Product Title Z-A",
            "price_asc" => "Lowest Price",
            "price_desc" => "Highest Price",
            "created_desc" => "Newest",
            "manual" => "Manually",
        ];

        $value = Str::slug($sort_order, "_");

        if (isset($sortorder[$value])) {
            return $sortorder[$value];
        } else {
            return "CREATED";
        }
    }
}
