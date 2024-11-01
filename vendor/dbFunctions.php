<?php

    /**
     * @package WaleeTrackingPlugin
     */

     /*
      Plugin Name: Walee Tracking
      Plugin URL: http://walee.pk/wordpress-plugin
      Description: This plugin tracks the performance of influencers on SME's website
      Version: 1.0.0
      Author: Techlets Pvt Ltd
      Author URL: https://techletspk.com/
      License: GLPv2 or later
      Text Domain: Walee Tracking Wordpress Plugin
      */

      /*
        This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program.  If not, see <https://www.gnu.org/licenses/>.
      */

function addNewSourceInDb($ii, $ss){
    global $wpdb;
    $ip = get_client_ip();
    $source = $ss;
    if($ip != ''){
        $wpdb->insert( 
            $wpdb->prefix."wt_match_data", 
            array( 
                'ip' => $ip, 
                'referrer' => $source 
            )
         );
         $clickId = $wpdb->insert_id;
         sendCurlPost("/api/tracking/newWordPressHook", [
            'referrer' => $source,
            'hookType' => 'Link Click',
            'foriegn_id' => $clickId
        ]);
    }
}

function saveFormData() {
    global $wpdb;
    $refRes = getRef();
    if(count($refRes)){
        $ref = $refRes[0];
        $ref = $ref->referrer;
        $ip = get_client_ip();
        $formData = $_POST['formdata'];
        $wpdb->insert( 
            $wpdb->prefix."wt_formsubmittion", 
            array( 
                'formData' => $formData, 
                'referrer' => $ref,
                'ip' => $ip 
            )
         );
         $id = $wpdb->insert_id;
         sendCurlPost("/api/tracking/newWordPressHook", [
            'data' => $formData, 
            'dataIp' => $ip,
            'referrer' => $ref,
            'hookType' => 'Custom',
            'foriegn_id' => $id
        ]);
    }
}

function addPageView($page, $type){
    global $wpdb;
    if(strpos($page, 'ajax.php') !== false || strpos($page, 'wc-ajax') !== false || strpos($page, 'wp-admin') !== false || strpos($page, 'rest_route') !== false){
        return;
    }  
    $refRes = getRef();
    if(count($refRes)){
        $ref = $refRes[0];
        $ref = $ref->referrer;
        $wpdb->insert( 
            $wpdb->prefix."wt_views", 
            array( 
                'page' => $page, 
                'referrer' => $ref,
                'type' => $type 
            )
         );
         $view_id = $wpdb->insert_id;
         if($type == 'php'){
            sendCurlPost("/api/tracking/newWordPressHook", [
                'page' => $page, 
                'referrer' => $ref,
                'hookType' => 'Page Views',
                'foriegn_id' => $view_id
            ]);
         }
    }
}

function getRef(){
    global $wpdb;
    $ip = get_client_ip();
    return $wpdb->get_results( "SELECT referrer FROM ".$wpdb->prefix."wt_match_data 
                                    WHERE ip = '$ip' AND ip != '' AND createdOn > DATE_ADD(NOW(), INTERVAL -7 DAY) 
                                    AND referrer is not null ORDER BY createdOn DESC LIMIT 1;");
}

function getOrderDetailz($orderId) {
    global $wpdb;
    return $wpdb->get_results( "SELECT id, referrer FROM ".$wpdb->prefix."wt_sales WHERE orderId = '$orderId' ORDER BY createdOn DESC LIMIT 1;");
}

function orderStatusChanged( $order_id, $old_status, $new_status ){
    global $wpdb;
    $ref = getOrderDetailz($order_id);
    if(count($ref)){
        $order = wc_get_order( $order_id );
        $newStatus = $order->get_status();
        $newPrice = $order->get_total();
        $items = $order->get_items();
        $wpdb->update($wpdb->prefix."wt_sales", array('totalPrice'=>$newPrice, 'order_status'=>$new_status), array('id'=>$order_id));
        $ref = $ref[0];
        if($ref->id && $ref->referrer){
            $orderLine = [];
            foreach ( $items as $item ) {
                $product = wc_get_product( $item->get_product_id() );
                $terms = get_the_terms( $item->get_product_id(), 'product_cat' );
                $product_cat_slug = 'not_found';
                foreach ( $terms as $term ) {
                    $product_cat_slug= $term->slug;
                }
                $orderLine[] = [
                    'sale_id' => $order_id, 
                    'proName' => $item->get_name(),
                    'proSku' => $product->get_sku(),
                    'proPrice' => $product->get_price(),
                    'proPriceSale' => $product->get_sale_price(),
                    'qty' => $item->get_quantity(),
                    'proId' => $item->get_product_id(),
                    'proCategory' => $product_cat_slug
                    ];
            }
            sendCurlPost("/api/tracking/orderStatusUpdated", [
                'hookType' => 'Sales',
                'foriegn_id' => $ref->id,
                'referrer' => $ref->referrer,
                'order_status' => $new_status,
                'updated_price' => $newPrice,
                'orderLine' => $orderLine
            ]);       
        }
    }
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    return $ipaddress;
}

function addToCartEvent($productId){
    global $wpdb;
    $product = wc_get_product( $productId );
    $refRes = getRef();
    if(count($refRes)){
        $ref = $refRes[0];
        $ref = $ref->referrer;
        $wpdb->insert( 
            $wpdb->prefix."wt_carts", 
            array( 
                'proId' => $productId, 
                'referrer' => $ref,
                'proSku' => $product->get_sku(),
                'proName' => $product->get_name(),
                'proPrice' => $product->get_price(),
                'proPriceSale' => $product->get_sale_price()
            )
         );
         $addCart_id = $wpdb->insert_id;
         sendCurlPost("/api/tracking/newWordPressHook", [
            'proId' => $productId, 
            'referrer' => $ref,
            'hookType' => 'Add To Cart',
            'proSku' => $product->get_sku(),
            'proName' => $product->get_name(),
            'proPrice' => $product->get_price(),
            'proPriceSale' => $product->get_sale_price(),
            'foriegn_id' => $addCart_id
        ]);
    }
}
  
function orderPlacedEvent($orderId){
    global $wpdb;

    $ref = getOrderDetailz($orderId);
    if(count($ref)){
        return;
    }

    $order = wc_get_order( $orderId );
    $order_data = $order->get_data();
    $items = $order->get_items();
    $refRes = getRef();
    $totalItems = 0;
    foreach ( $items as $item ) {
        $totalItems += $item->get_quantity();
    }
    if(count($refRes)){
        $ref = $refRes[0];
        $ref = $ref->referrer;
        // $wpdb->delete( $wpdb->prefix."wt_sales", array( 'orderId' => $orderId ) );
        // $wpdb->delete( $wpdb->prefix."wt_sale_line", array( 'sale_id' => $orderId ) );
        $wpdb->insert( 
            $wpdb->prefix."wt_sales", 
            array( 
                'orderId' => $orderId, 
                'referrer' => $ref,
                'userPhone' => $order_data['billing']['phone'],
                'userMail' => $order_data['billing']['email'],
                'totalItems' => $totalItems,
                'totalPrice' => $order->get_total(),
                'order_status' => $order->get_status(),
                'currency' => $order->get_currency(),
                'paymentMethod' => $order_data['payment_method']
            )
        );
        $waleeOrderId = $wpdb->insert_id;
        $hookArr = [
            'orderId' => $orderId, 
            'referrer' => $ref,
            'hookType' => 'Sales',
            'userPhone' => $order_data['billing']['phone'],
            'userMail' => $order_data['billing']['email'],
            'totalItems' => $totalItems,
            'totalPrice' => $order->get_total(),
            'order_status' => $order->get_status(),
            'currency' => $order->get_currency(),
            'paymentMethod' => $order_data['payment_method'],
            'orderLine' => [],
            'foriegn_id' => $waleeOrderId
        ];
        foreach ( $items as $item ) {
            $product = wc_get_product( $item->get_product_id() );
            $terms = get_the_terms( $item->get_product_id(), 'product_cat' );
            $product_cat_slug = 'not_found';
            foreach ( $terms as $term ) {
                $product_cat_slug= $term->slug;
            }
            $wpdb->insert( 
                $wpdb->prefix."wt_sale_line", 
                array( 
                    'sale_id' => $waleeOrderId, 
                    'proName' => $item->get_name(),
                    'proSku' => $product->get_sku(),
                    'proPrice' => $product->get_price(),
                    'proPriceSale' => $product->get_sale_price(),
                    'qty' => $item->get_quantity(),
                    'proId' => $item->get_product_id()
                )
            );
            $hookArr['orderLine'][] = [
                'sale_id' => $waleeOrderId, 
                'proName' => $item->get_name(),
                'proSku' => $product->get_sku(),
                'proPrice' => $product->get_price(),
                'proPriceSale' => $product->get_sale_price(),
                'qty' => $item->get_quantity(),
                'proId' => $item->get_product_id(),
                'proCategory' => $product_cat_slug
                ];
        }
        sendCurlPost("/api/tracking/newWordPressHook", $hookArr);
    }
}

function syncLocalOrders( $request ) {
    global $wpdb;
    $waleeOrders = $wpdb->get_results( "SELECT id, order_status, orderId FROM ".$wpdb->prefix."wt_sales;");
    foreach ( $waleeOrders as $waleeOrder ) {
        $order = wc_get_order( $waleeOrder->orderId );
        $newStatus = $order->get_status();
        $newPrice = $order->get_total();
        $wpdb->update($wpdb->prefix."wt_sales", array('totalPrice'=>$newPrice, 'order_status'=>$newStatus), array('id'=>$waleeOrder->id));
    }
    return json_encode(array('message' => 'synced')) ;
} 

function getLinkClicks( $request ) {
    return getTableDataForAPI('wt_match_data');
} 
 
function getPageViews( $request ) {
    return getTableDataForAPI('wt_views');
}

function getAddToCarts( $request ) {
    return getTableDataForAPI('wt_carts');
}

function getSales( $request ) {
    global $wpdb;
    $body = file_get_contents('php://input');
    $body = json_decode($body, true);
    // ----------------
    $whereClause = "wp_wt_sales.id = wp_wt_sale_line.sale_id";
    if(isset($body['referrer']) && $body['referrer']){
        $whereClause .= " AND referrer = '".$body['referrer']."'";
    }
    if(isset($body['startDate']) && $body['startDate']){
        $whereClause .= " AND createdOn >= '".$body['startDate']."'";
    }
    if(isset($body['endDate']) && $body['endDate']){
        $whereClause .= " AND createdOn <= '".$body['endDate']."'";
    }
    // ---------------- 
    $res = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wt_sales,".$wpdb->prefix."wt_sale_line 
                                    WHERE $whereClause;");
    return json_encode(array('data' => $res)) ;
}

function getTableDataForAPI($tableName){
    global $wpdb;
    $body = file_get_contents('php://input');
    $body = json_decode($body, true);
    // ----------------
    $whereClause = "";
    $isAnyWhereAdded = false;
    if(isset($body['referrer']) && $body['referrer']){
        if($isAnyWhereAdded){
            $whereClause .= " AND ";
        }
        $isAnyWhereAdded = true;
        $whereClause .= "referrer = '".$body['referrer']."'";
    }
    if(isset($body['startDate']) && $body['startDate']){
        if($isAnyWhereAdded){ 
            $whereClause .= " AND ";
        }
        $isAnyWhereAdded = true;
        $whereClause .= "createdOn >= '".$body['startDate']."'";
    }
    if(isset($body['endDate']) && $body['endDate']){
        if($isAnyWhereAdded){
            $whereClause .= " AND ";
        }
        $isAnyWhereAdded = true;
        $whereClause .= "createdOn <= '".$body['endDate']."'";
    }
    if(!$isAnyWhereAdded){
        $whereClause = "1";
    }
    // ---------------- 
    $res = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$tableName." 
                                    WHERE $whereClause;");
    return json_encode(array('data' => $res)) ;
}

function plugin_activated(){
    global $wpdb;
    $wpdb->delete( $wpdb->prefix."wt_config", array( 'installed_version' => '1.0.0' ) );
    $wpdb->insert( 
        $wpdb->prefix."wt_config", 
        array( 
            'installed_version' => '1.0.0', 
            'domain' =>  $_SERVER['SERVER_NAME']
        )
    );
    sendCurlPost("/api/tracking/newWordpressActivated", []);
}

function plugin_deactivated(){
    global $wpdb;
    $wpdb->delete( $wpdb->prefix."wt_config", array( 'installed_version' => '1.0.0' ) );
    sendCurlPost("/api/tracking/newWordpressDeactivated", []);
}

function plugin_uninstalled() {
    plugin_deactivated();
}

function sendCurlPost($url, $vars){
    $vars['installed_version'] = '1.0.0';
    $vars['type'] = 'wordpress';
    $vars['domain'] = $_SERVER['SERVER_NAME'];
    $payload = json_encode( $vars );
    $postUrl = "https://influencersofpakistan.com".$url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$postUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $server_output = curl_exec($ch);
    curl_close ($ch);
}

function checkSecurity(){
    return md5_file("dbFunctions.php");
}

function getCategories(){
    // prior to wordpress 4.5.0
    $args1 = array(
        // 'number'     => $number,
        // 'orderby'    => $orderby,
        // 'order'      => $order,
        // 'hide_empty' => $hide_empty,
        // 'include'    => $ids
    );

    $product_categories1 = get_terms( 'product_cat', $args1 );

    // since wordpress 4.5.0
    $args2 = array(
        'taxonomy'   => "product_cat"//,
        // 'number'     => $number,
        // 'orderby'    => $orderby,
        // 'order'      => $order,
        // 'hide_empty' => $hide_empty,
        // 'include'    => $ids
    );
    $product_categories2 = get_terms($args2);
    return json_encode(array('cats_old' => $product_categories1, 'cats_new' => $product_categories2), true);
}