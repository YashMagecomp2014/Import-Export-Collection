<?php
namespace App\Exports;

use App\Helpers\CommonHelpers;
use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
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

        $response = CommonHelpers::getAllCollections($shop);

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
        }

        foreach ($response as $key => $data) {
            $collection = $data;

            $array = array(
                "title" => $collection['title'],
                'Body (HTML)' => $collection['descriptionHtml'],
                "handle" => $collection['handle'],
                'Rules' => '',
                "products" => '',
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
