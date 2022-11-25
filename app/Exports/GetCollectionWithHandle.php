<?php
namespace App\Exports;

use App\Helpers\CommonHelpers;
use App\Models\Session;
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

            info('hey');
        }

        // $data = $collections;
        foreach ($response as $data) {

            $products = $data['products']['edges'];

            foreach ($products as $key => $product) {

                $handle = $product['node']['handle'];

                if ($key == 0) {
                    $array = array(
                        "title" => $data['title'],
                        'Body (HTML)' => $data['descriptionHtml'],
                        "handle" => $data['handle'],
                        'Rules' => '',
                        "products" => $handle,
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

                } else {
                    $arrofcsv[] = array(
                        "title" => '',
                        'Body (HTML)' => '',
                        "handle" => '',
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
