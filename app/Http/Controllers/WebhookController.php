<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class WebhookController extends Controller
{
  public function createproduct()
  {
    $HmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
    $webhookData = file_get_contents('php://input');
    $verified = $this->verify_webhook($webhookData, $HmacHeader);
    if (isset($verified) && !empty($verified)) {

      // $myfile = fopen("testing.txt", "w") or die("Unable to open file!");
      // $txt = $webhookData;
      // fwrite($myfile, $txt);
      // fclose($myfile);

      $datas = json_decode($webhookData, true);
      $data['product'] = $datas;
      $product_id = $datas['id'];
      $status = $datas['status'];
      $json_data = $webhookData;

      if (isset($product_id)) {
        $receivedVariants = array_column($data['product']['variants'], 'id');
        $variant_ids = DB::table('new_master_product')
          ->select('variant_id')
          ->where('product_id', $product_id)
          ->get()->toArray();
  
        $existingVariants = array();
        foreach ($variant_ids as $varient) {
          $existingVariants[] = $varient->variant_id;
        }

        $deletedVariants = array_diff($existingVariants, $receivedVariants);

        if (!empty($deletedVariants)) {
          DB::table('new_master_product')
            ->whereIn('variant_id', $deletedVariants)
            ->delete();
        }


        foreach ($data['product']['variants'] as $chakesku) {
          $variants = $chakesku['id'];
          $sku = $chakesku['sku'];
          $inventory_item_id = $chakesku['inventory_item_id'];
          $inventory_quantity = $chakesku['inventory_quantity'];

          if (isset($sku)) {

            $result = DB::table('new_master_product')->where('sku', $sku)->first();
            // print_r($result);
            if ($result) {
              $data_insert = DB::table('new_master_product')->insert([
                'product_id' => $product_id,
                'variant_id' => $variants,
                'sku' => $sku,
                'inventory_id' => $inventory_item_id,
                'inventory_quantity' => $inventory_quantity,
                'statu' => $status,
                'json_data' => $json_data
              ]);

              print_r($data_insert);
            }
          }
        }
      }
    }
  }

  public function updateproduct()
  {
    $HmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
    $webhookData = file_get_contents('php://input');
    $verified = $this->verify_webhook($webhookData, $HmacHeader);
    // $myfile = fopen("testing.txt", "w") or die("Unable to open file!");
    // $txt = $webhookData;
    // fwrite($myfile, $txt);
    // fclose($myfile);
    // $verified = 1;
    // $webhookData = '{"id":7571688390730,"title":"Al Mar 40th Annv SERE Linerlock A\/O","body_html":"","vendor":"borrasoutdoor","product_type":"","created_at":"2024-04-12T10:28:50-04:00","handle":"al-mar-40th-annv-sere-linerlock-a-o","updated_at":"2024-04-12T10:28:51-04:00","published_at":"2024-04-12T10:28:50-04:00","template_suffix":"","published_scope":"global","tags":"","status":"active","admin_graphql_api_id":"gid:\/\/shopify\/Product\/7571688390730","variants":[{"product_id":7571688390730,"id":41053641310282,"title":"Default Title","price":"0.00","sku":"AMK9202","position":2,"inventory_policy":"continue","compare_at_price":null,"fulfillment_service":"manual","inventory_management":"shopify","option1":"Default Title","option2":null,"option3":null,"created_at":"2024-04-12T10:28:51-04:00","updated_at":"2024-04-12T10:28:51-04:00","taxable":true,"barcode":"810485021908","grams":299370,"weight":299.3700000000000045474735088646411895751953125,"weight_unit":"kg","inventory_item_id":43149657243722,"inventory_quantity":5,"old_inventory_quantity":0,"requires_shipping":true,"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/41053641310282","image_id":null}],"options":[{"product_id":7571688390730,"id":10106235387978,"name":"Title","position":1,"values":["Default Title"]}],"images":[],"image":null}';
    if (isset($verified) && !empty($verified)) {
      $datas = json_decode($webhookData, true);
      $data['product'] = $datas;
      $product_id = $datas['id'];
      print_r($product_id);
      $status = $datas['status'];
      $json_data = $webhookData;

      if (isset($product_id)) {
        $receivedVariants = array_column($data['product']['variants'], 'id');
        $variant_ids = DB::table('new_master_product')
          ->select('variant_id')
          ->where('product_id', $product_id)
          ->get()->toArray();
        $existingVariants = array();
        foreach ($variant_ids as $varient) {
          $existingVariants[] = $varient->variant_id;
        }

        $deletedVariants = array_diff($existingVariants, $receivedVariants);

        if (!empty($deletedVariants)) {
          DB::table('new_master_product')
            ->whereIn('variant_id', $deletedVariants)
            ->delete();
        }


        foreach ($data['product']['variants'] as $chakesku) {
          $variants = $chakesku['id'];
          $sku = $chakesku['sku'];
          $inventory_item_id = $chakesku['inventory_item_id'];
          $inventory_quantity = $chakesku['inventory_quantity'];

          if (isset($sku)) {
            $result = DB::table('new_master_product')->where('sku', $sku)->first();

            if ($result) {
              $productupdate = DB::table('new_master_product')
                ->where('variant_id', $variants)
                ->update([
                  'sku' => $sku,
                  'inventory_quantity' => $inventory_quantity,
                  'statu' => $status,
                  'json_data' => $json_data
                ]);

              print_r($productupdate);
            } else {
              DB::table('new_master_product')->insert([
                'product_id' => $product_id,
                'variant_id' => $variants,
                'sku' => $sku,
                'inventory_id' => $inventory_item_id,
                'inventory_quantity' => $inventory_quantity,
                'statu' => $status,
                'json_data' => $json_data
              ]);
            }
          }
        }
      }
    }
  }

  public function deleteproduct()
  {
    $HmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
    $webhookData = file_get_contents('php://input');
    $verified = $this->verify_webhook($webhookData, $HmacHeader);
    // $webhookData = '{"admin_graphql_api_id":"gid:\/\/shopify\/Product\/7652417437770","body_html":"\u003cp\u003etest\u003c\/p\u003e","created_at":"2024-05-14T09:11:42-04:00","handle":"testing-2","id":7652417437770,"product_type":"","published_at":"2024-05-14T09:12:33-04:00","template_suffix":"","title":"testing","updated_at":"2024-05-14T09:19:20-04:00","vendor":"borrasoutdoor","status":"active","published_scope":"global","tags":"","variants":[{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/41241345392714","barcode":"","compare_at_price":null,"created_at":"2024-05-14T09:15:22-04:00","fulfillment_service":"manual","id":41241345392714,"inventory_management":"shopify","inventory_policy":"deny","position":3,"price":"11.00","product_id":7652417437770,"sku":"RED123","taxable":true,"title":"large \/ red","updated_at":"2024-05-14T09:15:22-04:00","option1":"large","option2":"red","option3":null,"grams":0,"image_id":null,"weight":0.0,"weight_unit":"kg","inventory_item_id":43337468379210,"inventory_quantity":0,"old_inventory_quantity":0,"requires_shipping":true},{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/41241361350730","barcode":"","compare_at_price":null,"created_at":"2024-05-14T09:19:20-04:00","fulfillment_service":"manual","id":41241361350730,"inventory_management":"shopify","inventory_policy":"deny","position":4,"price":"11.00","product_id":7652417437770,"sku":"YELLOW22","taxable":true,"title":"very small \/ yellow","updated_at":"2024-05-14T09:19:20-04:00","option1":"very small","option2":"yellow","option3":null,"grams":0,"image_id":null,"weight":0.0,"weight_unit":"kg","inventory_item_id":43337484337226,"inventory_quantity":0,"old_inventory_quantity":0,"requires_shipping":true}],"options":[{"name":"Size","id":10202630193226,"product_id":7652417437770,"position":1,"values":["large","very small"]},{"name":"Color","id":10202634125386,"product_id":7652417437770,"position":2,"values":["red","yellow"]}],"images":[],"image":null,"media":[],"variant_gids":[{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/41241345392714"},{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/41241361350730"}]}';
    // $verified = '1';
    if (isset($verified) && !empty($verified)) {
      $data = json_decode($webhookData, true);
      $del_id = $data['id'];
      // $myfile = fopen("deleted.txt", "w") or die("Unable to open file!");
      // $txt = $del_id;
      // fwrite($myfile, $txt);
      // fclose($myfile);
      // $deleted = DB::table('testing_table')->whereIn('product_id', $del_id)->delete();
      $deleted = DB::table('new_master_product')->where('product_id', $del_id)->delete();
      print_r($deleted);
      die;
    }
  }

  public function verify_webhook($webhookData = null, $HmacHeader = null)
  {
    $store = base64_encode(hash_hmac('sha256', $webhookData, 'ffb0f5e5980e9b372ab0b9d5a33d7094c39615d8a0360894e99eeaffd9083673', true));
    return ($HmacHeader == $store) ? 'store1' : false;
  }
}
