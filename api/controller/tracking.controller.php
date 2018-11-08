<?php
/** ======================
 *  tracking.controller.php
 *  ======================
 *  ------
 *  ABOUT:
 *  ------
 *  The Tracking controller handles storage of the parsed tracking data from PhantomJS
 *
 *  ----------
 *  FUNCTIONS:
 *  ----------
 *
 *
 */
global $controllerObject,$controllerFunction,$controllerID,$controllerData,$tracking_countries_array,$tracking_carriers_array;
header("HTTP/1.1 200 OK");

$tracking_company_id = $controllerFunction;

//Lets handle errors
if(empty($_POST['json'])) {
    log_error('tracking.controller.php called but no JSON data provided. POST: ' . PHP_EOL . print_r($_POST,true));
    is_crawler_error('tracking.controller.php called but no JSON data provided');
    exit();
}

if(!isset($tracking_company_id))
{
    log_error('Tracking company ID parameter not provided'.PHP_EOL);
    is_crawler_error('Tracking company ID parameter not provided in tracking.controller.php.');
}

$crawl_tstart = (isset($_POST['crawl_tstart']))?$_POST['crawl_tstart']:null;

$json_data = json_decode(urldecode($_POST['json']),true);

if ($json_data === null
    && json_last_error() !== JSON_ERROR_NONE) {
    log_error('tracking.controller.php received malformed json from crawler:' . PHP_EOL . $json_data . PHP_EOL);
    is_crawler_error('Malformed json provided in tracking.controller.php.');
    exit();
}


log_error( 'Looping through fulfillment objects.. ' . PHP_EOL);




foreach($json_data as $o)
{
    //Lets go through the results
    
    /** -----------------
     *  Run crawler class
     *  -----------------
     */
    
    $tracking_number =  $o['tracking_number'];
    $order_id = $o['order_id'];
    $fulfillment_id = $o['fulfillment_id'];

    /** ----------------------
     *  Fetch current tracking
     *  ----------------------
     */

    $tracking_country_from      = isset($tracking_countries_array[strval($o['tracking_country_from'])])?$tracking_countries_array[strval($o['tracking_country_from'])]['short']:null;
    $tracking_country_to        = isset($tracking_countries_array[strval($o['tracking_country_to'])])?$tracking_countries_array[strval($o['tracking_country_to'])]['short']:null;
    $tracking_carrier_from      = isset($o['tracking_carrier_from'])?$o['tracking_carrier_from']:null;
    $tracking_carrier_to        = isset($o['tracking_carrier_to'])?$o['tracking_carrier_to']:null;

    $tracking_last_date         = $o['tracking_last_date'];
    $tracking_last_status_text  = $o['tracking_last_status_text'];


    switch(strval($o['tracking_last_status']))
    {
        case '10':$tracking_last_status = DELIVERY_STATUS_IN_TRANSIT; break;
        case '20':$tracking_last_status = DELIVERY_STATUS_EXPIRED; break;
        case '30':$tracking_last_status = DELIVERY_STATUS_PICKUP; break;
        case '40':$tracking_last_status = DELIVERY_STATUS_DELIVERED; break;
        case '35':$tracking_last_status = DELIVERY_STATUS_FAILURE; break;
        case '50':$tracking_last_status = DELIVERY_STATUS_ALERT; break;
        case '00':case '0': $tracking_last_status = DELIVERY_STATUS_NOT_FOUND; break;
        
        default: $tracking_last_status = DELIVERY_STATUS_UNKNOWN; break;
    }


    if($tracking_last_status_text == 'radius') exit('17TRACK SCRAPING ERROR. NOT SUCCESSFUL!!');

    log_error(PHP_EOL."===========================================".PHP_EOL .
        'tracking_country_from: ' . $tracking_country_from . PHP_EOL .
        'tracking_country_to: ' . $tracking_country_to . PHP_EOL .
        'tracking_carrier_to: ' . $tracking_carrier_to . PHP_EOL .
        'tracking_carrier_from: ' . $tracking_carrier_from . PHP_EOL .
        'tracking_last_date: ' . $tracking_last_date . PHP_EOL .
        'tracking_last_status_text: ' . $tracking_last_status_text. PHP_EOL .
        'tracking_last_status ' . $tracking_last_status. PHP_EOL .
        "===========================================".PHP_EOL
    );

    $prev_delivery_status       = '';


    /** -----------------
     *  Fetch fulfillment
     *  -----------------
     */

    log_error( 'fetching fulfillment with ID: ' . $fulfillment_id . '..' . PHP_EOL);

    $fulfillment_instance = new Fulfillment($fulfillment_id);

    log_error( 'Fulfillment obj: ' . $fulfillment_id . PHP_EOL. print_r($fulfillment_instance,true) . PHP_EOL);

    $prev_delivery_status = $fulfillment_instance->delivery_status;

    $fulfillment_instance->tcheck = current_timestamp();

    log_error('fulfilment previous_delivery_status: ' . $prev_delivery_status . PHP_EOL);

    $is_update = false;

    if(
        trim($tracking_last_status_text) !== trim($fulfillment_instance->tracking_last_status_text) ||
        $prev_delivery_status !== $tracking_last_status
    ) $is_update = true;

    log_error('did tracking_last_status_text or delivery status change? ' . ($is_update?'YES':'NO').PHP_EOL);

    if($is_update)
    {
        $fulfillment_instance->tracking_last_date           = $tracking_last_date;
        $fulfillment_instance->tracking_last_status_text    = $tracking_last_status_text;
        $fulfillment_instance->delivery_status              = $tracking_last_status;

        $fulfillment_instance->tracking_country_from                 = $tracking_country_from;
        $fulfillment_instance->tracking_country_to                   = $tracking_country_to;
        $fulfillment_instance->tracking_carrier_from                 = $tracking_carrier_from;
        $fulfillment_instance->tracking_carrier_to                   = $tracking_carrier_to;

        log_error("##COMPARISON: previous status: " . $prev_delivery_status . " : tracking_last_status:" . $tracking_last_status.PHP_EOL);
        if($prev_delivery_status !== $tracking_last_status) {

            switch ($fulfillment_instance->delivery_status) {
                case DELIVERY_STATUS_DELIVERED:
                    $fulfillment_instance->status_delivered_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 0;
                    break;

                case DELIVERY_STATUS_CONFIRMED:
                    $fulfillment_instance->status_confirmed_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 1;
                    break;

                case DELIVERY_STATUS_IN_TRANSIT:
                    $fulfillment_instance->status_in_transit_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 1;
                    break;

                case DELIVERY_STATUS_OUT_FOR_DELIVERY:
                    $fulfillment_instance->status_out_for_delivery_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 1;
                    break;

                case DELIVERY_STATUS_FAILURE:
                    $fulfillment_instance->status_failure_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 1;
                    break;

                case DELIVERY_STATUS_NOT_FOUND:
                    $fulfillment_instance->status_not_found_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 1;
                    break;

                case DELIVERY_STATUS_PICKUP:
                    $fulfillment_instance->status_customer_pickup_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 1;
                    break;

                case DELIVERY_STATUS_ALERT:
                    $fulfillment_instance->status_alert_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 0;
                    break;

                case DELIVERY_STATUS_EXPIRED:
                    $fulfillment_instance->status_expired_tcreate = current_timestamp();
                    $fulfillment_instance->is_tracking = 0;
                    break;
            }

        }


        switch(intval($fulfillment_instance->delivery_status))
        {
            case DELIVERY_STATUS_IN_TRANSIT:
                if(time() - intval(strtotime($fulfillment_instance->status_in_transit_tcreate)) > (MAX_DAYS_IN_TRANSIT * 60 * 60 * 24))
                    $fulfillment_instance->alert_status = NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT;
                break;

            case DELIVERY_STATUS_NOT_FOUND:
                if(time() - intval(strtotime($fulfillment_instance->status_not_found_tcreate)) > (MAX_DAYS_NOT_FOUND * 60 * 60 * 24))
                    $fulfillment_instance->alert_status = NOTIFICATION_STATUS_EXTENDED_NOT_FOUND;
                break;

            case DELIVERY_STATUS_ALERT:
                    $fulfillment_instance->alert_status = NOTIFICATION_STATUS_ALERT_CUSTOMS;
                break;
        }
    }

    $fulfillment_instance->save();

    /** -----------
     *  Fetch Order
     *  -----------
     */

    $order_instance = new Order($order_id);

    log_error('Order Obj:' . $order_id . PHP_EOL . print_r($order_instance,true) . PHP_EOL);

    if($is_update)
    {
        $order_instance->delivery_status = $fulfillment_instance->delivery_status;
        /** --------------------------------
         *  Determine notification for order
         *  --------------------------------
         */

        log_error( 'order->deliver_status: ' . $tracking_last_status);

        $order_instance->alert_status = $fulfillment_instance->alert_status;
        
        $order_instance->save();
    }

    increment_api_counter($tracking_company_id,count($json_data));
    exit();
}

function increment_api_counter($tracking_api_id= TRACKING_COMPANY_CHINA_POST,$count=1)
{
    $_date = date("Y-m-d 00:00:00");
    $_ts = current_timestamp();
    $db_instance = new Database();
    $q = 'UPDATE api_stats SET call_count = call_count + ' . intval($count) .' WHERE call_tcreate="'. $_date .'" AND api_id=' . $tracking_api_id;
    $result = $db_instance->db_query($q,DATABASE_NAME);
    if(!$result) {
        $result = $db_instance->db_create('api_stats',Array(
            'api_id'=> TRACKING_COMPANY_CHINA_POST,
            'call_count'=>1,
            'call_tmodified'=>$_ts,
            'call_tcreate'=>$_date
        ));
    }
    unset($db_instance);
}

/*
function increment_api_counter($count)
{
    $db_instance = new Database();



    $q = 'UPDATE sys SET value = value + ' . $count .' WHERE `key` = "17TRACK_API_COUNT";';
    $result = $db_instance->db_query($q,DATABASE_NAME);
    unset($db_instance);
}*/