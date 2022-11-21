<?php
namespace App\Exports;

use App\Models\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

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

        $token = Session::where('shop',$this->shopurl)->first('access_token');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://'.$this->shopurl.'/admin/api/2022-10/graphql.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Shopify-Access-Token: '.$token->access_token.'';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($result, true);

        $collections = $response['data']['nodes'];

        // $data = $collections;
        foreach ($collections as $data) {

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
}
