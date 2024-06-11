<?php

namespace App\Http\Controllers;

use App\Models\master_products;
use Illuminate\Http\Request;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use DB;

require_once(app_path('Helpers.php'));

class ProductegetController extends Controller
{
    public function getproduct()
    {
        
        $client = getShopifyData();
        


        $products = DB::table('new_master_product')
            ->select(
                'product_id',
                DB::raw('COUNT(*) AS duplicate_count'),
                DB::raw('GROUP_CONCAT(CONCAT(sku, "|", variant_id ,"|",inventory_id, "|", inventory_quantity)) AS skus_with_variant')
            )
            ->where('status', '=', '1')
            ->groupBy('product_id')
            ->limit(200)
            ->get()
            ->toArray();
        //    dd($products);

        if (empty($products)) {
            $update = DB::table('new_master_product')
                ->update(['status' => 1]);
            if (!empty($update)) {
                $products = DB::table('new_master_product')
                    ->select('product_id', DB::raw('COUNT(*) AS duplicate_count'), DB::raw('GROUP_CONCAT(CONCAT(sku, "|", variant_id ,"|",inventory_id, "|", inventory_quantity)) AS skus_with_variant'))
                    ->where('status', '=', '1')
                    ->groupBy('product_id')
                    ->limit(200)
                    ->get()->toArray();
            } else {
                return;
            }
        }

        if (!empty($products)) {
           
            foreach ($products as $products_sku) {


                $inventory = 0;
                $count = 0;
                    $productId = $products_sku->product_id;
                if ($products_sku->duplicate_count > 1) {
                    $skus_with_variant = explode(',', $products_sku->skus_with_variant);
                    $inventory_quentityCheck = false;
                    foreach ($skus_with_variant as $sku_with_variant) {
                        if(empty($sku_with_variant)){
                            continue;
                        }
                        $data =  explode('|', $sku_with_variant);
                        list($sku, $variant_id, $inventory_id, $inventory_quentity) = explode('|', $sku_with_variant);
                        $getInventory = $this->inventoryCheck($sku);
                        $product_data = $getInventory['data']['products']['items'];

                        if (isset($product_data) && !empty($product_data)) {
                            foreach ($product_data as $data) {
                                if ($data['stock_status'] == 'IN_STOCK') {

                                    $productId = $products_sku->product_id;
                                   
                                    // $body = [
                                    //     "product" => [
                                    //         "status" => "active",
                                    //     ],
                                    // ];

                                    // $client = getshopifydata();
                                    // $response = $client->put("/products/$productId", $body);
                                    // $data = $response->getDecodedBody();

                                    $variantId = $variant_id;

                                    $body = [
                                        "variant" => [
                                            "inventory_policy" => "continue"
                                        ]
                                    ];
                                    $client = getshopifydata();
                                    $response1 = $client->put("/variants/$variantId", $body);
                                    $data = $response1->getDecodedBody();
                                } else {
                                   
                                    $variantId = $variant_id;
                                    $body = [
                                        "variant" => [
                                            "inventory_policy" => "deny"
                                        ]
                                    ];
                                    $client = getshopifydata();
                                    $response1 = $client->put("/variants/$variantId", $body);
                                    $data = $response1->getDecodedBody();
                                    if ($inventory_quentity > 0) {
                                        $inventory_quentityCheck = true;
                                    }
                                    $count++;
                                }
                            }
                        }
                    }

                    if ($count == $products_sku->duplicate_count) {
                        $productId = $products_sku->product_id;
                        if (isset($inventory_quentityCheck) && !empty($inventory_quentityCheck)) {
                           
                            // $body = [
                            //     "product" => [
                            //         "status" => "active",
                            //     ],
                            // ];
                        } else {

                            // $body = [
                            //     "product" => [
                            //         "status" => "draft",
                            //     ],
                            // ];
                        }
                        // $client = getshopifydata();
                        // $response1 = $client->put("/products/$productId", $body);
                        // $data = $response1->getDecodedBody();
                    } else {
                        // $body = [
                        //     "product" => [
                        //         "status" => "active",
                        //     ],
                        // ];
                        // $client = getshopifydata();
                        // $response1 = $client->put("/products/$productId", $body);
                        // $data = $response1->getDecodedBody();
                    }
                } else {

                    $skus_with_variant = explode(',', $products_sku->skus_with_variant);
                    if(empty($skus_with_variant)){
                        continue;
                    }
                    foreach ($skus_with_variant as $sku_with_variant) {
                        if(empty($sku_with_variant)){
                            continue;
                        }
                        list($sku, $variant_id, $inventory_id, $inventory_quentity) = explode('|', $sku_with_variant);
                        $getInventory = $this->inventoryCheck($sku);
                        $product_data = $getInventory['data']['products']['items'];
                        if (isset($product_data) && !empty($product_data)) {
                            foreach ($product_data as $data) {
                                if ($data['stock_status'] == 'IN_STOCK') {

                                    $productId = $products_sku->product_id;
                                    
                                    // $body = [
                                    //     "product" => [
                                    //         "status" => "active",
                                    //     ],
                                    // ];

                                    // $client = getshopifydata();
                                    // $response1 = $client->put("/products/$productId", $body);
                                    // $data = $response1->getDecodedBody();


                                    $variantId = $variant_id;
                                    $body = [
                                        "variant" => [
                                            "inventory_policy" => "continue"
                                        ]
                                    ];
                                    $client = getshopifydata();
                                    $response1 = $client->put("/variants/$variantId", $body);
                                    $data = $response1->getDecodedBody();
                                } else {
                                    
                                    $variantId = $variant_id;
                                    $body = [
                                        "variant" => [
                                            "inventory_policy" => "deny"
                                        ]
                                    ];
                                    $client = getshopifydata();
                                    $response1 = $client->put("/variants/$variantId", $body);
                                    $data = $response1->getDecodedBody();

                                    if ($inventory_quentity > '0') {

                                        $productId = $products_sku->product_id;
                                        // $body = [
                                        //     "product" => [
                                        //         "status" => "active",
                                        //     ],
                                        // ];
                                        // $client = getshopifydata();
                                        // $response1 = $client->put("/products/$productId", $body);
                                        // $data = $response1->getDecodedBody();
                                    } else {
                                        // $productId = $products_sku->product_id;
                                        // $body = [
                                        //     "product" => [
                                        //         "status" => "draft",
                                        //     ],
                                        // ];
                                        // $client = getshopifydata();
                                        // $response1 = $client->put("/products/$productId", $body);
                                        // $data = $response1->getDecodedBody();
                                    }
                                }
                            }
                        }
                    }
                }
                $update = DB::table('new_master_product')
                    ->where('product_id', $products_sku->product_id)
                    ->update(['status' => '0']);
            }
        } else {
        }
    }

    public function inventoryCheck($sku)
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

    public function shopifyimportproduct()

    {

        $checkpage = DB::table('productpagination')->where('id', 1)->first();
        // dd($checkpage->limit);


        $client = getshopifydata();

        if (empty($checkpage)) {

            $pageInfon =  ['limit' => '25'];

            $response =  $client->GET(path: "products.json", query: $pageInfon);
            // dd($response);
        } else {

            $pageInfon =  ['limit' => $checkpage->limit, 'page_info' => $checkpage->page_info];
            // dd($pageInfon);
            $response =  $client->GET(path: "products.json", query: $pageInfon);
            // dd($response);
        }

        $res = $response->getDecodedBody();

        // dd($res);

        $serializedPageInfo = serialize($response->getPageInfo());


        $pageInfo = unserialize($serializedPageInfo);
        // dd($pageInfo);

        try {

            $getNextPageQuery = $pageInfo->getNextPageQuery()['page_info'];
            // dd($getNextPageQuery);
        } catch (\Throwable $th) {

            $getNextPageQuery = 0;
        }



        foreach ($res['products'] as $productdata) {

            $product = $productdata;

            $src = null;

            if (isset($product['image']['src'])) {

                $src =   $product['image']['src'];
            }


            if (isset($productdata['variants']) && !empty($productdata['variants'])) {



                foreach ($productdata['variants'] as $variants) {

                    $variant =  $variants;

                    DB::table('new_master_product')->insertOrIgnore(

                        [
                            'product_id' => $productdata['id'],

                            'statu' => $product['status'],

                            'variant_id' => $variants['id'],



                            // 'product_id' => $variant['product_id'],

                            // 'title' => $variant['title'],

                            // 'price' => $variant['price'],

                            'sku' => $variant['sku'],

                            // 'position' => $variant['position'],

                            // 'shopify_created_at' => $variant['created_at'],

                            // 'inventory_policy' => $variant['inventory_policy'],

                            // 'compare_at_price' => $variant['compare_at_price'],

                            // 'fulfillment_service' => $variant['fulfillment_service'],

                            // 'inventory_management' => $variant['inventory_management'],

                            // 'option1' => $variant['option1'],

                            // 'option2' => $variant['option2'],

                            // 'option3' => $variant['option3'],

                            // 'taxable' => $variant['taxable'],

                            // 'barcode' => $variant['barcode'],

                            // 'grams' => $variant['grams'],

                            // 'image_id' => $variant['image_id'],

                            // 'weight' => $variant['weight'],

                            // 'weight_unit' => $variant['weight_unit'],

                            'inventory_id' => $variant['inventory_item_id'],

                            'inventory_quantity' => $variant['inventory_quantity'],

                            // 'old_inventory_quantity' => $variant['old_inventory_quantity'],

                            // 'requires_shipping' => $variant['requires_shipping'],

                            // 'admin_graphql_api_id' => $variant['admin_graphql_api_id'],

                            // 'shopify_variants_data' => json_encode($variant),
                            'json_data' => json_encode($product)

                        ]

                    );
                }
            }

            $data = DB::table('productpagination')->updateOrInsert(

                ['id' => '1'],

                ['page_info' => $getNextPageQuery, 'limit' => '25']

            );

            echo $data;
        }
    }
}
