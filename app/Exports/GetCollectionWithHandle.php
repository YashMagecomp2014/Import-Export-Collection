<?php
namespace App\Exports;

use App\Helpers\CommonHelpers;
use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Str;

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

        $shop = Session::where("shop", $this->shopurl)->first();
        if (!$shop) {
            return collect([]);
        }
        // $response = $shop->graph($body);
        $response = CommonHelpers::getAllCollections($shop, $withProduct = true);

        $data = $response->toArray();

        if (!$data) {
            $arrofcsv[] = array(
                "title" => '',
                'Body (HTML)' => '',
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
        foreach ($response as $data) {

            $products = $data['products']['edges'];


            

            $sort_order = $data['sortOrder'];
            
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

            if (!$products) {
                $array = array(
                    "title" => $data['title'],
                    'Body (HTML)' => $data['descriptionHtml'],
                    'Rules' => '',
                    "products" => '',
                    'Disjunctive' => '',
                    'Sort Order' => $data['sortOrder'],
                    'Template Suffix' => '',
                    'Published' => 'true',
                    'SEO Title' => $data['seo']['title'],
                    'SEO Description' => $data['seo']['description'],

                );
                if (isset($data['image']['src']) && $data['image']['src']) {
                    $array['image'] = $data['image']['src'];
                }
                $arrofcsv[] = $array;

            }

            foreach ($products as $key => $product) {

                $handle = $product['node']['handle'];

                if ($key == 0) {
                    $array = array(
                        "title" => $data['title'],
                        'Body (HTML)' => $data['descriptionHtml'],
                        'Rules' => $rule,
                        "products" => $handle,
                        'Disjunctive' => $Disjunctive,
                        'Sort Order' => $sororder,
                        'Template Suffix' => '',
                        'Published' => 'true',
                        'SEO Title' => $data['seo']['title'],
                        'SEO Description' => $data['seo']['description'],

                    );

                    if (isset($data['image']['src']) && $data['image']['src']) {
                        $array['image'] = $data['image']['src'];
                    }
                    $arrofcsv[] = $array;

                } else {
                    $arrofcsv[] = array(
                        "title" => '',
                        'Body (HTML)' => '',
                        'Rules' => '',
                        "products" => $handle,
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
