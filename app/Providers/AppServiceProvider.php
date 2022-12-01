<?php

namespace App\Providers;

use App\Lib\DbSessionStorage;
use App\Lib\Handlers\AppUninstalled;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Shopify\ApiVersion;
use Shopify\Context;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Shopify\Exception\MissingArgumentException
     */
    public function boot()
    {
        Context::initialize(
            Config::get('shopify.api_key'),
            Config::get('shopify.api_secret'),
            Config::get('shopify.scopes'),
            str_replace('https://', '', Config::get('shopify.host')),
            new DbSessionStorage(),
            ApiVersion::LATEST
        );

        URL::forceScheme('https');

        Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());

        HeadingRowFormatter::extend('custom', function ($value, $key) {
            // return 'title';

            $collection_arr = [
                'Collection' => 'title',
                'Description' => 'body_html',
                'Conditions' => 'rules',
                'Products' => 'products',
                'Products must match' => 'disjunctive',
                'Sort Order' => 'sort_order',
                'Theme template' => 'template_suffix',
                'Published' => 'published',
                'SEO Title' => 'seo_title',
                'SEO Description' => 'seo_description',
                'Collection Image' => 'image',
            ];

            if (isset($collection_arr[$value])) {
                return $collection_arr[$value];
            } else {
                return $value;
            }

        });
    }
}
