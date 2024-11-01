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


include_once dirname( __FILE__ ) . '/dbFunctions.php';

function saveWaleeRef(){
    $ip = get_client_ip();
    $source = null;
    if(isset($_POST['action']) && $_POST['action'] == 'saveWaleeRef'){
        if(isset($_POST['source'])){
            $source = $_POST['source'];
        }
        if(isset($_GET['utm_source'])){
            $source = $_GET['utm_source'];
        }
        if($source != null && $_POST['action'] == 'saveWaleeRef'){
            addNewSourceInDb($ip, $source);
        }
    } else if (isset($_POST['action']) &&  $_POST['action'] == 'saveWaleeView'){
        $url = $_POST['url'];
        $url = str_replace("http://","",$url);
        $url = str_replace("https://","",$url);
        $url = str_replace("www","",$url);
        if(strpos($url, '/')){
            $url = substr($url, strpos($url, "/"));
        } else {
            $url = 'domainHome';
        }
        addPageView($url, 'ajax');
    }
    echo "";
    exit;
}

// function saveWaleeFormSubmissionData(){
//     if (isset($_POST['action']) &&  $_POST['action'] == 'saveWaleeFormSubmissionData'){
//         saveFormData();
//     }
//     echo "";
//     exit;
// }

function saveWaleeRefIndependent(){
    $ip = get_client_ip(); 
    $source = null;
    if(isset($_POST['source'])){
        $source = $_POST['source'];
    }
    if(isset($_GET['utm_source'])){
        $source = $_GET['utm_source'];
    }
    if($source != null){
        addNewSourceInDb($ip, $source);
    }
}

function woo_custom_add_to_cart( $cart_item_data,$productId ) {
    addToCartEvent($productId);
}

function woocommerce_thankyou($orderId) {
    orderPlacedEvent($orderId);
}

function waleeSmeInfo(){
	include_once dirname( __FILE__ ) . '/../templates/admin.php';
}