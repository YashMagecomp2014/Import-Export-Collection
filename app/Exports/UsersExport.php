<?php
namespace App\Exports;

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

        $token = Session::where('shop', $this->shopurl)->first('access_token');

        // $shopurl = $request->header('url');
        // print_r($this->shopurl);
        // exit;
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->shopurl . '/admin/api/2022-10/graphql.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n\"query\": \"query { collections(first: 10) { edges { node { id title handle updatedAt productsCount sortOrder seo { description title } } } } }\"\n}");

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Shopify-Access-Token: ' . $token->access_token . '';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($result, true);
        $collections = $response['data']['collections']['edges'];

        // $data = $collections;
        foreach ($collections as $data) {

            $collection = $data['node'];

            $arrofcsv[] = array(
                'Title' => $collection['title'],
                'Handle' => $collection['handle'],
                'updatedAt' => $collection['updatedAt'],
                'productsCount' => $collection['productsCount'],
                'sortOrder' => $collection['sortOrder'],
                'seo Title' => $collection['seo']['title'],
                'seo Description' => $collection['seo']['description'],
    
            );

        }

       
        

        $collectiondata = collect($arrofcsv);

        return $collectiondata;
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
