<?php

namespace App\Http\Controllers;

use App\Exports\GetSelectedCollections;
use App\Exports\GetSelectedCollectionWithProduct;
use App\Models\Collection;
use App\Models\Session;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $shopurl = $request->header('url');
        $collection = Collection::orderBy('created_at', 'desc')->where('shop', $shopurl)->get();
        return $collection;
    }

    public function store(Request $request)
    {
        $data = new Collection();
        if ($request->file('file')) {
            $file = $request->file('file');
            $filename = date('YmdHi') . $file->getClientOriginalName();
            $path = $file->move(public_path('public/file'), $filename);
            $data['file'] = $filename;
            $data['path'] = 'public/file/' . $filename;
        }
        $data->save();
        return response()->json(['success' => "CSV upload successfully."]);
    }

    public function delete(Request $request)
    {
        $ids = $request->ids;
        $finalid = '[' . $ids . ']';
        $lastid = json_decode($finalid);
        foreach ($lastid as $id) {
            $Collection = Collection::find($id);
            unlink($Collection->path);
            if ($Collection) {
                $Collection->delete();
            }
        }
        return response()->json(['success' => "Collection History Deleted successfully."]);
    }
    public function exportcsv(Request $request)
    {
        return response()->download(public_path('public/file/202210200734testing.csv'));
    }

    public function getallcollection(Request $request)
    {
        $shopurl = $request->header('url');

        $query = 'query {
            collections(first: 250) {
              nodes {
                id
                title
                handle
                updatedAt
                productsCount
                sortOrder
                seo {
                    description
                    title
                  }
              }
            }
          }';

        $body = [
            "query" => $query,
        ];
        return $this->curls($body, $shopurl);
    }

    public function getsmartcollection(Request $request)
    {
        $shopurl = $request->header('url');
        $query = 'query {
            collections(first: 250, query: "collection_type:smart") {
              nodes {
                id
                title
                handle
                updatedAt
                productsCount
                sortOrder
                seo {
                    description
                    title
                  }
              }
            }
          }';
        $body = [
            "query" => $query,
        ];
        return $this->curls($body, $shopurl);
    }

    public function getcustomcollection(Request $request)
    {
        $shopurl = $request->header('url');
        $query = 'query {
            collections(first: 250, query: "collection_type:custom") {
              nodes {
                id
                title
                handle
                updatedAt
                productsCount
                sortOrder
              }
            }
          }
          ';
        $body = [
            "query" => $query,
        ];

        return $this->curls($body, $shopurl);

    }
    public function curls($body, $shopurl)
    {
        $shop = Session::where('shop', $shopurl)->first();
        $response = $shop->graph($body);

        $collections = $response['data']['collections']['nodes'];

        return $collections;
    }

    public function GetSelectedCollections(Request $request)
    {

        $ids = $request->ids;
        $id = json_encode($ids);
        $finalid = str_replace(',', '","', $id);
        $shopurl = $request->header('url');

        $time = time();
        $collection = Excel::store(new GetSelectedCollections($shopurl, $finalid), 'public/' . $shopurl . '/' . $time . 'Get-Selected-Collection.csv');

        $data = new Collection();
        $data->file = $time . 'Get-Selected-Collection.csv';
        $data->path = 'storage/' . $shopurl . '/' . $time . 'Get-Selected-Collection.csv';
        $data->type = 'Export File Selected Collection';
        $data->shop = $shopurl;
        $data->save();

        return response()->json([
            "Success" => "Get-Selected-Collection.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);
    }

    public function GetSelectedCollectionsWithProducts(Request $request)
    {
        $time = time();
        $ids = $request->ids;
        $id = json_encode($ids);
        $finalid = str_replace(',', '","', $id);

        $shopurl = $request->header('url');

        $collection = Excel::store(new GetSelectedCollectionWithProduct($shopurl, $finalid), 'public/' . $shopurl . '/' . $time . 'Get-Selected-Collection-With-Product.csv');

        $data = new Collection();
        $data->file = $time . 'Get-Selected-Collection-With-Product.csv';
        $data->path = 'storage/' . $shopurl . '/' . $time . 'Get-Selected-Collection-With-Product.csv';
        $data->type = 'Export File Selected Collection with Product';
        $data->shop = $shopurl;
        $data->save();

        return response()->json([
            "Success" => "Get-Selected-Collection-With-Product.csv",
            "message" => "Export Successfully",
            "status" => 200,
        ]);
    }
}
