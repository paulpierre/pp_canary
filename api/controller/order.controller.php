<?php
/** ====================
 *  order.controller.php
 *  ====================
 *  ------
 *  ABOUT:
 *  ------
 *  The Order controller processes webhooks sent via Shopify anytime an order within shopify
 *  is created or modified.
 *
 *  ----------
 *  FUNCTIONS:
 *  ----------
 *
 *  • Create new orders in the database
 *  • Update orders with any change in fulfillment status
 *  • Insert tracking numbers into the system to make available for tracking/shipment status crawling
 *
 *  ---------
 *  SETTINGS:
 *  ---------
 *
 *  Order webhooks are setup here: https://omg-true.myshopify.com/admin/settings/notifications
 *  The following webhooks are setup and currently being called by Shopify:
 *
 *  1) Fulfillment Creation - http://api.omgtrue.com/fulfillment/webhook/create
 *  2) Fulfillment Update   - http://api.omgtrue.com/fulfillment/webhook/update
 *  3) Order Creation       - http://api.omgtrue.com/order/webhook/create
 *  4) Order Fulfillment    - http://api.omgtrue.com/order/webhook/fulfillment
 *
 */
global $controllerID,$controllerObject,$controllerFunction,$controllerData;




switch($controllerID)
{
    case 'webhook':
        $json = json_decode(file_get_contents('php://input'),true);
        process_orders($json['orders']);
    break;
}




function process_orders($orders)
{


//print 'order_count: ' . count($orders) . PHP_EOL;
    $display = '';


    $order_instance = new Order();
    $item_instance = new Item();
    $fulfillment_instance = new Fulfillment();


    $order_count = 0;

    foreach ($orders as $o) {
        /** ------------
         *  PARSE ORDERS
         *  ------------ */
        $order_shopify_id = $o['id'];
        log_error('order_shopify_id: ' . $order_shopify_id);

        $order_name = $o['name'];
        $customer_email = trim($o['email']);
        $customer_phone = (!empty($o['shipping_address']['phone'])) ? trim(strval($o['shipping_address']['phone'])) : trim(strval($o['billing_address']['phone']));
        $order_tcreate = date("Y-m-d H:i:s", strtotime($o['created_at']));
        $order_tmodified = !empty($o['updated_at'])?date("Y-m-d H:i:s", strtotime($o['updated_at'])):null;
        $order_tclose = !empty($o['closed_at'])?date("Y-m-d H:i:s", strtotime($o['closed_at'])):null;
        $order_is_closed = empty($o['closed_at']) ? true : false;
        $order_total_cost = $o['total_price'];
        $order_receipt_id = $o['name'];
        $order_currency = $o['currency'];
        $order_vendor = '';
        $order_alert_status = NOTIFICATION_STATUS_NONE;


        if (!empty($o['gateway'])) {
            if (strpos(strtolower($o['gateway']), 'stripe')) $order_gateway = GATEWAY_PROVIDER_STRIPE;
            elseif (strpos(strtolower($o['gateway']), 'paypal')) $order_gateway = GATEWAY_PROVIDER_PAYPAL;
            elseif (strpos(strtolower($o['gateway']), 'shopify_payments')) $order_gateway = GATEWAY_PROVIDER_PAYPAL;
            else $order_gateway = GATEWAY_PROVIDER_UNKNOWN;
        }

        $order_fulfillment_status = $o['fulfillment_status'];
        $order_is_dropified = (!empty($o['note']) && strpos(strtolower($o['note']), 'dropified'));

        /** --------------
         *  PARSE CUSTOMER
         *  -------------- */
        $order_customer_fn = $o['shipping_address']['first_name'];
        $order_customer_ln = $o['shipping_address']['last_name'];
        $order_customer_address1 = $o['shipping_address']['address1'];
        $order_customer_address2 = $o['shipping_address']['address2'];
        $order_customer_city = $o['shipping_address']['city'];
        $order_customer_zip = $o['shipping_address']['zip'];
        $order_customer_province = $o['shipping_address']['province'];
        $order_customer_country_code = $o['shipping_address']['country_code'];
        $order_customer_province_code = $o['shipping_address']['province_code'];
        $order_customer_phone = $o['shipping_address']['phone'];
        $order_tags =  strtolower($o['tags']);

        /** -----------------------------------
         *  CUSTOMER BILLING - ADDED 02-06-2018
         *  ----------------------------------- */
        $order_customer_billing_fn              = $o['billing_address']['first_name'];
        $order_customer_billing_ln              = $o['billing_address']['last_name'];
        $order_customer_billing_address1        = $o['billing_address']['address1'];
        $order_customer_billing_address2        = $o['billing_address']['address2'];
        $order_customer_billing_city            = $o['billing_address']['city'];
        $order_customer_billing_zip             = $o['billing_address']['zip'];
        $order_customer_billing_province        = $o['billing_address']['province'];
        $order_customer_billing_country_code    = $o['billing_address']['country_code'];
        $order_customer_billing_province_code   = $o['billing_address']['province_code'];
        $order_customer_billing_phone           = $o['billing_address']['phone'];
        
        
        /** -------------------------------------
         *  CANCELLED - ADDED BY RAFAL 2018-03-20
         *  ------------------------------------- */
        $order_tcancel = !empty($o['cancelled_at'])?date("Y-m-d H:i:s", strtotime($o['cancelled_at'])):'0000-00-00 00:00:00';
        
        $order_is_ocu = (strpos($order_tags,'ocu')> -1)?1:0;

        $_refund_status = trim($o['financial_status']);

        //print 'REFUND STATUS: ' . $_refund_status . PHP_EOL;

        switch(strtolower($_refund_status))
        {
            case 'partially_refunded':
                $order_refund_status = 2;
                //print 'REFUND!!: ' . $order_refund_status;
                break;
            case 'refunded':
                $order_refund_status = 1;
                //print 'REFUND!!: ' . $order_refund_status;
                break;
            default: case 'paid':
            $order_refund_status = 0;
            //print 'REFUND!!: ' . $order_refund_status;
            break;
        }
        
        /** --------------------------------------------
         *  FINANCIAL STATUS - ADDED BY RAFAL 2018-03-20
         *  -------------------------------------------- */
        
        $_financial_status = trim($o['financial_status']);
        
        switch(strtolower($_financial_status))
        {
            case 'pending':
                $order_financial_status = 1;
                break;
            case 'authorized':
                $order_financial_status = 2;
                break;
            case 'partially_paid':
                $order_financial_status = 3;
                break;
            case 'paid':
                $order_financial_status = 4;
                break;
            case 'partially_refunded':
                $order_financial_status = 5;
                break;
            case 'refunded':
                $order_financial_status = 6;
                break;
            case 'voided':
                $order_financial_status = 7;
                break;
            default: case '':
                $order_financial_status = 0;
            break;
        }
                
        $order_is_fulfilled = false;    //has the fulfillment process started or not
        $order_is_delivered = false;    //has the order been completed and delivered
        $order_is_tracking = false;     //whether we should track the order

        $order_array = Array(
            'order_name'=>$order_name,
            'order_customer_email' => $customer_email,
            'order_customer_fn' => $order_customer_fn,
            'order_customer_ln' => $order_customer_ln,
            'order_customer_address1' => $order_customer_address1,
            'order_customer_address2' => $order_customer_address2,
            'order_customer_city' => $order_customer_city,
            'order_customer_country' => $order_customer_country_code,
            'order_customer_province' => $order_customer_province,

            /** -----------------------------------
             *  CUSTOMER BILLING - ADDED 02-06-2018
             *  ----------------------------------- */
            'order_customer_billing_fn'=>               $order_customer_billing_fn,
            'order_customer_billing_ln'=>               $order_customer_billing_ln,
            'order_customer_billing_address1'=>         $order_customer_billing_address1,
            'order_customer_billing_address2'=>         $order_customer_billing_address2,
            'order_customer_billing_city'=>             $order_customer_billing_city,
            'order_customer_billing_zip'=>              $order_customer_billing_zip,
            'order_customer_billing_province'=>         $order_customer_billing_province,
            'order_customer_billing_country'=>          $order_customer_billing_country_code,
            'order_customer_billing_phone'=>            $order_customer_billing_phone,

            'order_currency' => $order_currency,
            'order_tags' => $order_tags,
            'order_is_ocu'=>$order_is_ocu,
            'order_customer_zip' => $order_customer_zip,
            'order_customer_phone' => $customer_phone,//$order_customer_phone,
            'order_fulfillment_status' => $order_fulfillment_status,
            'order_is_refunded'   => $order_refund_status,
            'order_shopify_id' => $order_shopify_id,
            'order_gateway' => $order_gateway,
            'order_receipt_id'=>$order_receipt_id,
            'order_total_cost' => $order_total_cost,
            'order_topen' => $order_tcreate,
            'order_tclose' => $order_tclose,
            
            
            /** --------------------------------------------
             *  FINANCIAL STATUS - ADDED BY RAFAL 2018-03-20
             *  -------------------------------------------- */
            'order_financial_status' => $order_financial_status,
            
            /** -------------------------------------
             *  CANCELLED - ADDED BY RAFAL 2018-03-20
             *  ------------------------------------- */
            'order_tcancel' => $order_tcancel,
        );


        /** -----------------------------
         *  CREATE AND STORE ORDER OBJECT
         *  ----------------------------- */


        /** -----------------------------------
         *  LOOKUP ORDERS BY SHOPIFY'S ORDER ID
         *  ----------------------------------- */
        if (isset($order_object)) unset($order_object);
        $order_object = $order_instance->fetch_order_by_order_shopify_id($order_shopify_id);


        /** ------------------------------
         *  IF IT DOESN'T EXIST, CREATE IT
         *  ------------------------------ */
        if (!$order_object) {
            $order_object = new Order($order_array);
            $order_id = $order_object->save();
        } else {
            $order_id = $order_object->id;
        }


        /** --------------------------
         *  PARSE REFUNDED LINE ITEMS
         *  ------------------------- */



        if(!empty($o['refunds']))
        {
            $refund_list = Array();
            $refund_check = Array();
            foreach($o['refunds'] as $refund)
            {
                $refund_date =date("Y-m-d H:i:s", strtotime($refund['created_at']));
                foreach($refund['refund_line_items'] as $item)
                {
                    $refund_list[$item['line_item_id']]=$refund_date;
                    $refund_check[] = $item['line_item_id'];
                }
            }
            //print 'refund! ' . print_r($refund_list,true);
            //print 'refund! ' . print_r($refund_check,true);
        }




        /** ----------------
         *  PARSE LINE ITEMS
         *  ---------------- */

        foreach ($o['line_items'] as $p) {
            $item_shopify_id = $p['id'];
            $item_shopify_product_id = $p['product_id'];
            $item_shopify_variant_id = $p['variant_id'];
            $item_name = $p['name'];
            $item_quantity = $p['quantity'];
            $item_sku = $p['sku'];
            $item_price = $p['price'];
            $item_is_refunded = 0;
            //$item_refund_tcreate = null;
            if(!empty($o['refunds'])) {
                if (in_array(trim($item_shopify_id), $refund_check)) {
                    $item_is_refunded = 1;
                    $item_refund_tcreate = $refund_list[$item_shopify_id];
                    print 'FOUND REFUND!';
                }
            }

            $item_array = Array(
                'order_id' => $order_id,
                'order_shopify_id' => $order_shopify_id,
                'item_shopify_id' => $item_shopify_id,
                'item_quantity' => $item_quantity,
                'item_sku' => $item_sku,
                'item_price'=>floatval($item_price),
                'item_shopify_product_id' => $item_shopify_product_id,
                'item_shopify_variant_id' => $item_shopify_variant_id,
                'item_name' => $item_name,
                'item_is_refunded' => $item_is_refunded,

            );

            if(isset($item_refund_tcreate)) $item_array['item_refund_tcreate']=$item_refund_tcreate;

            /** -------------------------
             *  STORE / UPDATE LINE ITEMS
             *  ------------------------- */
            if (isset($item_object)) unset($item_object);

            $item_object = $item_instance->fetch_item_by_shopify_item_id($item_shopify_id);
            if (!$item_object) {
                $item_object = new Item($item_array);
                $item_id = $item_object->save();
            } else {
                $item_id = $item_object->id;
                $item_object->save($item_array);
            }
            $order_object->order_items[] = $item_object;

        }


        /** ------------
         *  FULFILLMENTS
         *  ------------ */
        if (!empty($o['fulfillments'])) {
            foreach ($o['fulfillments'] as $fulfillment) {
                
                /**
                 * Added by Rafal 2018-04-12
                 * 
                 * Prevent added cancelled, error or failure
                 * fulfillments
                 */
                if($fulfillment['status'] == 'cancelled' || $fulfillment['status'] == 'error' || $fulfillment['status'] == 'failure'){
                    continue;
                }
                
                $fulfillment_shopify_id = $fulfillment['id'];
                $fulfillment_shipment_status = strtolower($fulfillment['shipment_status']);
                $fulfillment_topen = date("Y-m-d H:i:s", strtotime($fulfillment['created_at']));
                $fulfillment_tmodified = date("Y-m-d H:i:s", strtotime($fulfillment['updated_at']));
                $fulfillment_tracking_company = strtolower($fulfillment['tracking_company']);
                $fulfillment_tracking_number = strval($fulfillment['tracking_number']);
                $fulfillment_tracking_url = $fulfillment['tracking_url'];

                $order_is_fulfilled = true;


                $fulfillment_array = Array(
                    'order_id' => $order_id,
                    'order_shopify_id' => $order_shopify_id,
                    'shipment_status' => $fulfillment_shipment_status,
                    'fulfillment_topen'=>$fulfillment_topen,
                    'fulfillment_shopify_id' => $fulfillment_shopify_id,
                    'tracking_number' => $fulfillment_tracking_number,
                    'tracking_company' => $fulfillment_tracking_company,
                    'tracking_url' => $fulfillment_tracking_url,
                    'is_fulfilled' => $order_is_fulfilled,
                );

                $order_delivery_status = false;

                //Normally USPS is the only courier that updates this appropriately. If its delivered set status
                switch ($fulfillment_shipment_status) {
                    case 'delivered':
                        $order_delivery_status = DELIVERY_STATUS_DELIVERED;
                        $order_is_delivered = true;
                        $fulfillment_array['status_delivered_tcreate'] = current_timestamp();
                        break;

                    case 'confirmed':
                        $order_delivery_status = DELIVERY_STATUS_CONFIRMED;
                        $fulfillment_array['status_confirmed_tcreate'] = current_timestamp();
                        break;

                    case 'in_transit':
                        $order_delivery_status = DELIVERY_STATUS_IN_TRANSIT;
                        $fulfillment_array['status_in_transit_tcreate'] = current_timestamp();
                        break;

                    case 'out_for_delivery':
                        $order_delivery_status = DELIVERY_STATUS_OUT_FOR_DELIVERY;
                        $fulfillment_array['status_out_for_delivery_tcreate'] = current_timestamp();
                        break;

                    case 'failure':
                        $order_delivery_status = DELIVERY_STATUS_FAILURE;
                        $fulfillment_array['status_failure_tcreate'] = current_timestamp();
                        break;

                    default:
                        $order_delivery_status = DELIVERY_STATUS_UNKNOWN;
                        break;
                }

                if ($order_delivery_status)
                    $order_array['delivery_status'] = $order_delivery_status;

                if ($fulfillment_tracking_company !== 'USPS') $order_is_tracking = true;

                /**
                 *                            'status_delivered_tcreate'=>'',
                 * 'status_confirmed_tcreate'=>'',
                 * 'status_in_transit_tcreate'=>'',
                 * 'status_out_for_delivery_tcreate'=>'',
                 * 'status_failure_tcreate'=>'',
                 * 'status_not_found_tcreate'=>'',
                 * 'status_customer_pickup_tcreate'=>'',
                 * 'status_alert_tcreate'=>'',
                 * 'status_expired_tcreate'=>'',
                 */
                /** -----------------------------------
                 *  CREATE AND STORE FULFILLMENT OBJECT
                 *  ----------------------------------- */

                //Lets see if the order exists
                if (isset($fulfillment_object)) unset($fulfillment_object);

                $fulfillment_object = $fulfillment_instance->fetch_fulfillment_by_shopify_fulfillment_id($fulfillment_shopify_id);

                if (!$fulfillment_object) {
                    $fulfillment_object = new Fulfillment($fulfillment_array);
                    $fulfillment_id = $fulfillment_object->save();
                } else {


                    $fulfillment_id = $fulfillment_object->id;

                    $fulfillment_object->save($fulfillment_array);
                }


                $order_object->order_fulfillments[] = $fulfillment_object;

            }

            /** -----------------------
             *  UPDATE THE ORDER OBJECT
             *  ----------------------- */
            $order_object->save($order_array);
        } else {
            /** -------------------------------------
             *  ADDED BY RAFAL 2018-03-20
             * 
             *  This allow to update cancell date and
             *  financial status if it is different
             *  than in table
             *  ------------------------------------- */
            
            $cancel_array = Array();
            
            if(isset($order_object -> financial_status) && $order_object -> financial_status != $order_financial_status){
                $cancel_array['order_financial_status'] = $order_financial_status;
            }
            
            if(isset($order_object -> tcancel) && $order_object -> tcancel != $order_financial_status){
                $cancel_array['order_tcancel'] = $order_tcancel;
            }
            
            if (sizeof($cancel_array) > 0) {
                
                $db_conditions = Array('order_id' => $order_id);
                
                if (isset($db_instance)) unset($db_instance);                
                $db_instance = new Database;
                $db_instance -> db_update('orders',$cancel_array,$db_conditions,$isOr=false);
            }
        }

        $order_count++;
        if ($order_count >= ORDER_COUNT_LIMIT && ORDER_COUNT_LIMIT != 0)exit(); //exit('ORIGINAL DATA: ' . PHP_EOL . print_r($order_count, true));
    }


    $res = print_r($order_instance, true);
    //print '<pre>' . $res;

}



/*
api_response(array(
    'code'=>RESPONSE_SUCCESS,
    'data'=>'HAII'
));*/