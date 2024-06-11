<?php

use Shopify\Context;
use Shopify\Clients\Rest;
use Shopify\Clients\Graphql;
use Shopify\Auth\FileSessionStorage;

if (!function_exists('getshopifydata')) { 
    function getshopifydata()
    {
        Context::initialize(
            apiKey: env('SHOPIFY_API_KEY'),
            apiSecretKey: env('SHOPIFY_API_SECRET'),
            scopes: env('SHOPIFY_APP_SCOPES'),
            hostName: env('SHOPIFY_APP_HOST_NAME'),
            sessionStorage: new FileSessionStorage('/'),
            apiVersion: '2024-04',
            isEmbeddedApp: true,
            isPrivateApp: false,
        );
        return $client = new Rest(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_API_accessToken'));
    }
}

if (!function_exists('inventoryCheck')) {
    function inventoryCheck($sku)
    {
        $queryString = <<<QUERY
            {
                products(filter: { sku: { eq: "$sku" } }) {
                    total_count
                    items {
                        id
                        sku
                        name
                        stock_status
                    }
                }
            }
            QUERY;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://store.blueridgeknives.com/graphql",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(array('query' => $queryString)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
