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
      Author URL: http://techletspk.com/
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
 
      defined( 'ABSPATH' ) || exit;

      include_once dirname( __FILE__ ) . '/vendor/makeTables.php';
      include_once dirname( __FILE__ ) . '/vendor/functions.php';
      
  
      add_action( 'admin_menu', 'walee_admin_menu' );
      register_activation_hook( __FILE__, 'plugin_activated' );
      register_deactivation_hook( __FILE__, 'plugin_deactivated' );
      register_uninstall_hook(__FILE__, 'plugin_uninstalled');
  
      function walee_admin_menu() {
          add_menu_page( 'Walee Tracking', 'Walee Tracking', 'manage_options', 'walee/sme-info', 'waleeSmeInfo', 'dashicons-welcome-view-site', 6  );
      }
  
      saveWaleeRefIndependent();
  
      addPageView($_SERVER['REQUEST_URI'], 'php');
  
  
      // add_action('wp_ajax_saveWaleeRef', 'saveWaleeRef');
      // add_action('wp_ajax_nopriv_saveWaleeRef', 'saveWaleeRef');
      // add_action('wp_ajax_saveWaleeView', 'saveWaleeRef');
      // add_action('wp_ajax_nopriv_saveWaleeView', 'saveWaleeRef');
      // add_action('wp_ajax_saveWaleeFormSubmissionData', 'saveWaleeFormSubmissionData');
      // add_action('wp_ajax_nopriv_saveWaleeFormSubmissionData', 'saveWaleeFormSubmissionData');
  
      
      add_action('woocommerce_add_to_cart', 'custome_add_to_cart');
      // add_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
      add_action( 'woocommerce_thankyou', 'woocommerce_thankyou', 10, 1);
  
      add_filter( 'woocommerce_add_cart_item_data', 'woo_custom_add_to_cart',10,2 );
  
      add_action( 'woocommerce_order_status_changed', 'orderStatusChanged', 99, 3 );
  
      // add_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
      // add_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
      // add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
  
  
      // function woocommerce_checkout_login_form() {
      //     file_put_contents('alihaider9_logs', 'woocommerce_checkout_login_form\n'.PHP_EOL, FILE_APPEND);
      // }
  
       
      add_action( 'rest_api_init', function () {
          register_rest_route( 'walee-tracking', '/syncLocalOrders', array(
              'methods' => 'POST',
              'callback' => 'syncLocalOrders',
          ));
          register_rest_route( 'walee-tracking', '/getLinkClicks', array(
              'methods' => 'POST',
              'callback' => 'getLinkClicks',
          ));
          register_rest_route( 'walee-tracking', '/getPageViews', array(
              'methods' => 'POST',
              'callback' => 'getPageViews',
          ));
          register_rest_route( 'walee-tracking', '/getAddToCarts', array(
              'methods' => 'POST',
              'callback' => 'getAddToCarts',
          )); 
          register_rest_route( 'walee-tracking', '/getSales', array(
              'methods' => 'POST',
              'callback' => 'getSales',
          )); 
          register_rest_route( 'walee-tracking', '/checkSecurity', array(
              'methods' => 'POST',
              'callback' => 'checkSecurity',
          )); 
          register_rest_route( 'walee-tracking', '/getCategories', array(
              'methods' => 'POST',
              'callback' => 'getCategories',
          )); 
      });