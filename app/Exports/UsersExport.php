<?php
namespace App\Exports;

use App\Models\Session;
use GuzzleHttp\Psr7\Request;
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

        $query = 'query { 
            collections(first: 250) { 
                edges 
                { 
                    node 
                    { 
                        title 
                        handle 
                        descriptionHtml 
                        sortOrder 
                        image
                        { 
                            src
                         } 
                         seo 
                         { 
                            description 
                            title 
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
                'Image' => '',
                "products" => '',
                'Disjunctive' => '',
                'Sort Order' => '',
                'Template Suffix' => '',
                'Published' => '',
                'SEO Title' => '',
                'SEO Description' => '',

            );
        }

        foreach ($collections as $data) {

            $collection = $data['node'];

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

        $collectiondata = collect($arrofcsv);

        return $collectiondata;
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
        ];
    }

    public function curls($body, $shop)
    {
        $token = Session::where('shop', $shop)->first('access_token');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://' . $shop . '/admin/api/2022-10/graphql.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Shopify-Access-Token: ' . $token->access_token . '';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }
}
