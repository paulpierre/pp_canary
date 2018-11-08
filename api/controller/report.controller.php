<?php
/** =====================
 *  report.controller.php
 *  =====================
 *  ------
 *  ABOUT:
 *  ------
 *  Sends report data
 */
global $controllerObject,$controllerFunction,$controllerID,$controllerData,$vendor_array;

include_once (API_PATH . 'sql_queries.php'); //Contains all our SQL queries

/** ----------
 *  DATE RANGE
 *  ---------- */

$date_start = isset($_GET['date_start']) && is_valid_date($_GET['date_start'])?urldecode($_GET['date_start']):false;
$date_end = isset($_GET['date_end']) && is_valid_date($_GET['date_end'])?urldecode($_GET['date_end']):false;

if($date_start && $date_end)
{
    if(strtotime($date_start) > strtotime($date_end))
        api_response(array(
            'code'=> RESPONSE_ERROR,
            'msg'=> 'The ending time in the range cannot be before the end time. Try again.',
        ));
}

//if no date was specified lets just use today
if($date_start === false && $date_end === false)
    $date_start = $date_end = date('Y-m-d');


/** ----------
 *  PAGINATION
 *  ---------- */

$page_limit = isset($_GET['limit'])?urldecode($_GET['limit']):false;
$page_index = isset($_GET['p'])?urldecode($_GET['p']):0;


/** -------
 *  SORTING
 *  ------- */
$sort_column = isset($_GET['sort_column'])?urldecode($_GET['sort_column']):false;
$sort_type = isset($_GET['sort_type'])?urldecode($_GET['sort_type']):false;

$vendor_id = (isset($_GET['vendor_id']) && !is_array($_GET['vendor_id']))?urldecode($_GET['vendor_id']):false;
$status_id = (isset($_GET['status_id']) && !is_array($_GET['vendor_id']))?urldecode($_GET['status_id']):false;

/** ----------
 *  FOR WIZARD
 *  ---------- */
/*
 * If $_GET variables are arrays
 * it means that they are dedicated
 * for Wizard
 */
$vendor_id          = (isset($_GET['vendor_id']) && is_array($_GET['vendor_id']))?$_GET['vendor_id']:$vendor_id;
$status_id          = (isset($_GET['order_delivery_status']) && is_array($_GET['order_delivery_status']))?$_GET['order_delivery_status']:$status_id;
$fulfillment_id     = (isset($_GET['fulfillment_id']) && is_array($_GET['fulfillment_id']))?$_GET['fulfillment_id']:false;
$order_is_refunded  = (isset($_GET['order_is_refunded']) && is_array($_GET['order_is_refunded']))?$_GET['order_is_refunded']:false;
$order_alert_status = (isset($_GET['order_alert_status']) && is_array($_GET['order_alert_status']))?$_GET['order_alert_status']:false;
$tables_columns     = (isset($_GET['tables_columns']) && is_array($_GET['tables_columns']))?$_GET['tables_columns']:false;
$wizard_sql;


$report_name = $controllerID;


switch($controllerFunction)
{

    /** ==============
     *  VENDOR REPORTS
     *  ============== */

    case 'vendor':

        switch($report_name) //Lets see which refund report they want
        {
            /** +-------------------------------------------------+
             *  | SQL_REPORT_VENDOR_DELIVERY_SUMMARY_BY_VENDOR_ID |
             *  +-------------------------------------------------+
             *
             *  DESCRIPTION:
             *  Look up the count of the different delivery statuses of a single vendor
             *
             *  END-POINT:
             *  http://########/report/vendor/vendor_delivery_summary_by_vendor_id?
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *      vendor_id=3
             *      sort_column=order_tcreate
             *      sort_type=0 (0=desc 1=asc)
             *
             */

            case 'vendor_delivery_summary_by_vendor_id':

                /** ---------------------------------------
                 *  Ensure parameters are validated and set
                 *  --------------------------------------- */

                if ($vendor_id === false || !is_numeric($vendor_id)) {
                    api_response(array(
                        'code' => RESPONSE_ERROR,
                        'msg' => 'You must specify a valid vendor ID.',
                    ));
                }

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */
                
                $res = process_query(Array(
                    'q'             => SQL_REPORT_VENDOR_DELIVERY_SUMMARY_BY_VENDOR_ID,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'O.order_tcreate',
                    'query_id'      => $vendor_id,
                    'page_index'    => $page_index,
                    'page_limit'    => $page_limit,
                    'sort_column'   => $sort_column,
                    'sort_type'     => $sort_type
                ));
                
                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg' =>$message,
                    'data' => Array(
                        'date_start'    => $date_start,
                        'date_end'      => $date_end,
                        'vendor_id'     => $vendor_id,
                        'vendor_name'   => $vendor_array[$vendor_id]['name'],
                        'report'        => $res
                    )
                ));

            break;

            /** +--------------------------------------------------------------------+
             *  | SQL_REPORT_VENDOR_DELIVERY_STATUS_BY_VENDOR_ID_AND_DELIVERY_STATUS |
             *  +--------------------------------------------------------------------+
             *
             *  DESCRIPTION:
             *  Get a detailed list of order information specified by vendor ID and status you want to look up
             *
             *  END-POINT:
             *  http://########/report/vendor/vendor_order_status_list
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *      vendor_id=3
             *      status_id=4
             *          0 = unknown
             *          1 = confirmed
             *          2 = in transit
             *          3 = out for delivery
             *          4 = delivered
             *          5 = failure
             *          6 = not found
             *          7 = pickup
             *          8 = alert
             *          9 = expired
             */
            case 'vendor_order_status_list':

                /** ---------------------------------------
                 *  Ensure parameters are validated and set
                 *  --------------------------------------- */

                if ($vendor_id === false || !is_numeric($vendor_id)) {
                    api_response(array(
                        'code' => RESPONSE_ERROR,
                        'msg' => 'You must specify a valid vendor ID.',
                    ));
                }

                if ($status_id === false || !is_numeric($status_id)) {
                    api_response(array(
                        'code' => RESPONSE_ERROR,
                        'msg' => 'You must specify a valid delivery status.',
                    ));
                }


                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q'             => SQL_REPORT_VENDOR_DELIVERY_STATUS_BY_VENDOR_ID_AND_DELIVERY_STATUS,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'O.order_tcreate',
                    'query_id'      => $vendor_id,
                    'status_id'     => $status_id,
                    'page_limit'    => $page_limit,
                    'page_index'    =>  $page_index,
                    'sort_column'   => $sort_column,
                    'sort_type'     => $sort_type
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data'=> Array(
                        'start'=>$date_start,
                        'end'=>$date_end,
                        'status'=>$status_id,
                        'vendor_id'=>$vendor_id,
                        'vendor_name'=>$vendor_array[$vendor_id]['name'],
                        'report'=>$res
                    )
                ));

            break;

        /** +----------------------------------------------+
         *  | SQL_REPORT_DELIVERY_STATUS_SUMMARY_BY_VENDOR |
         *  +----------------------------------------------+
         *
         *  DESCRIPTION:
         *  Get a list of delivery statuses from ALL the vendors
         *
         *  END-POINT:
         *  http://########/report/vendor/vendor_delivery_summary
         *      date_start=2017-10-09
         *      date_end=2017-11-10
         */
            case 'vendor_delivery_summary':

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q'             => SQL_REPORT_DELIVERY_STATUS_SUMMARY_BY_VENDOR,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'O.order_tcreate',
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_res[] = Array(
                        'vendor_id'         =>!empty($o['vendor_id'])?$o['vendor_id']:0,
                        'vendor_name'       =>!empty($o['vendor_id'])?$vendor_array[$o['vendor_id']]['name']:'Unknown',
                        'delivery_status'   =>$o['status'],
                        'count'             =>$o['count'],
                    );
                }
                $res = $_res;

                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data'=> Array(
                        'start'=>$date_start,
                        'end'=>$date_end,
                        'report'=>$res
                    )
                ));

            break;



            /** +--------------------------------------------+
             *  | SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDOR |
             *  +--------------------------------------------+
             *
             *  DESCRIPTION:
             *  Get a list of delivery statuses from ALL the vendors
             *
             *  END-POINT:
             *  http://########/report/vendor/vendor_avg_delivery_time
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             */
            case 'vendor_avg_delivery_time':

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q'             => SQL_REPORT_DELIVERY_STATUS_SUMMARY_BY_VENDOR,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'O.order_tcreate',
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_res[] = Array(
                        'vendor_id'         =>!empty($o['vendor_id'])?$o['vendor_id']:0,
                        'vendor_name'       =>!empty($o['vendor_id'])?$vendor_array[$o['vendor_id']]['name']:'Unknown',
                        'delivery_status'   =>$o['status'],
                        'count'             =>$o['count'],
                    );
                }
                $res = $_res;

                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data'=> Array(
                        'start'=>$date_start,
                        'end'=>$date_end,
                        'report'=>$res
                    )
                ));
            break;

            /** +--------------------------------------------------------+
             *  | SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDORS_BY_COUNTRY |
             *  +--------------------------------------------------------+
             *
             *  DESCRIPTION:
             *  Get a list of delivery statuses from ALL the vendors
             *
             *  END-POINT:
             *  http://########/report/vendor/vendor_avg_delivery_time_by_country
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'vendor_avg_delivery_time_by_country':

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q'             => SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDORS_BY_COUNTRY,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'O.order_tcreate',
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_res[] = Array(
                        'vendor_id'         =>!empty($o['vendor_id'])?$o['vendor_id']:0,
                        'vendor_name'       =>!empty($o['vendor_id'])?$vendor_array[$o['vendor_id']]['name']:'Unknown',
                        'country'           =>$o['country'],
                        'count'             =>$o['order_count'],
                        'days'              =>number_format($o['days'],0)
                    );
                }
                $res = $_res;

                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data'=> Array(
                        'start'=>$date_start,
                        'end'=>$date_end,
                        'report'=>$res
                    )
                ));
                break;

            default:
                api_response(array(
                    'code'=> RESPONSE_ERROR,
                    'data'=> array('message'=>ERROR_INVALID_FUNCTION)
                ));
            break;


        }
    break;

    
    /** ==============
     *  REFUND REPORTS
     *  ============== */

    case 'refund':

        /** +-------------------------------------+
         *  | SQL_REPORT_REFUNDS_BY_VENDOR_BY_GEO |
         *  +-------------------------------------+
         *
         *  DESCRIPTION:
         *  Refunds broken down by customer country and by vendor
         *
         *  END-POINT:
         *  http://########/report/refund/refunds_by_country
         *      date_start=2017-10-09
         *      date_end=2017-11-10
         *
         */
        switch($report_name) //Lets see which refund report they want
        {
            case 'refunds_by_country':

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q' => SQL_REPORT_REFUNDS_BY_VENDOR_BY_GEO,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'date_column' => 'O.order_tcreate',

                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_res[] = Array(
                        'vendor_id'             =>!empty($o['vendor_id'])?$o['vendor_id']:0,
                        'vendor_name'           =>!empty($o['vendor_id'])?$vendor_array[$o['vendor_id']]['name']:'Unknown',
                        'item_name'             =>$o['item_name'],
                        'item_sku'              =>$o['item_sku'],
                        'order_customer_country'=>$o['order_customer_country'],
                        'cost'                  =>number_format($o['cost'],2),
                        'quantity'              =>$o['quantity']
                    );
                }
                $res = $_res;

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res
                    )
                ));
            break;

            /** +------------------------------------------+
             *  | SQL_REPORT_REFUNDS_BY_VENDOR_BY_ITEM_SKU |
             *  +------------------------------------------+
             *
             *  DESCRIPTION:
             *  Refunds broken down by customer country and by vendor
             *
             *  END-POINT:
             *  http://########/report/refund/refunds_by_sku
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'refunds_by_sku':

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q' => SQL_REPORT_REFUNDS_BY_VENDOR_BY_ITEM_SKU,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'date_column' => 'O.order_tcreate',

                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_res[] = Array(
                        'vendor_id'             =>!empty($o['vendor_id'])?$o['vendor_id']:0,
                        'vendor_name'           =>!empty($o['vendor_id'])?$vendor_array[$o['vendor_id']]['name']:'Unknown',
                        'item_name'             =>$o['item_name'],
                        'item_sku'              =>$o['item_sku'],
                        'cost'                  =>number_format($o['cost'],2),
                        'quantity'              =>$o['quantity']
                    );
                }
                $res = $_res;

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res
                    )
                ));

            break;

            /** +----------------------------------+
             *  | SQL_REPORT_VENDOR_REFUND_SUMMARY |
             *  +----------------------------------+
             *
             *  DESCRIPTION:
             *  Refunds broken down by customer country and by vendor
             *
             *  END-POINT:
             *  http://########/report/refund/vendor_refund_summary
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'vendor_refund_summary':

                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q' => SQL_REPORT_VENDOR_REFUND_SUMMARY,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'date_column' => 'O.order_tcreate',

                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_res[] = Array(
                        'vendor_id'         =>!empty($o['vendor_id'])?$o['vendor_id']:0,
                        'vendor_name'       =>!empty($o['vendor_id'])?$vendor_array[$o['vendor_id']]['name']:'Unknown',
                        'cost'              =>number_format($o['cost'],2),
                        'count'             =>$o['count'],
                    );
                }
                $res = $_res;

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res
                    )
                ));
            break;


            /** +---------------------------------+
             *  | SQL_REPORT_REFUNDS_BY_VENDOR_ID |
             *  +---------------------------------+
             *
             *  DESCRIPTION:
             *  Get a detailed list of order information specified by vendor ID and status you want to look up
             *
             *  END-POINT:
             *  http://########/report/refund/vendor_order_status_list?date_start=2017-10-09&date_end=2017-11-10&vendor_id=3&limit=10=p1&sort_column=order_tcreate&sort_type=0
             *
             */
            case 'refunds_by_vendor':

                /** ---------------------------------------
                 *  Ensure parameters are validated and set
                 *  --------------------------------------- */

                if ($vendor_id === false || !is_numeric($vendor_id)) {
                    api_response(array(
                        'code' => RESPONSE_ERROR,
                        'msg' => 'You must specify a valid vendor ID.',
                    ));
                }


                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q'             => SQL_REPORT_REFUNDS_BY_VENDOR_ID,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'O.order_tcreate',
                    'query_id'      => $vendor_id,
                    'page_limit'    => $page_limit,
                    'page_index'    => $page_index,
                    'sort_column'   => $sort_column,
                    'sort_type'     => $sort_type
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data'=> Array(
                        'start'=>$date_start,
                        'end'=>$date_end,
                        'vendor_id'=>$vendor_id,
                        'vendor_name'=>$vendor_array[$vendor_id]['name'],
                        'report'=>$res
                    )
                ));

                break;

            default:
                api_response(array(
                    'code'=> RESPONSE_ERROR,
                    'data'=> array('message'=>ERROR_INVALID_FUNCTION)
                ));
            break;

        }
    break;
    
    /** ======
     *  WIZARD
     *  ====== */

    case 'wizard':
       
        switch($report_name) //Lets see which wizard report they want
        {            
            /** +----------------------------------------------------------------------+
             *  | SQL_WIZARD_ORDERS_FULFILLMENT_ITEMS_BY_VENDOR_ID_AND_DELIVERY_STATUS |
             *  +----------------------------------------------------------------------+
             *
             *  DESCRIPTION:
             *  Wizard for creating requests
             *
             *  END-POINT:
             *  http://########/report/wizard/wizard_orders_fulfillment_items_by_vendor_id_and_delivery_status
             *      table[]=orders.order_id
             *      vendor_id[]=1
             *      status_id[]=1
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'wizard_orders_fulfillment_items_by_vendor_id_and_delivery_status':
                
                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */
                
                $records = 0;
                $pages = 1;
                                
                $date_start = $date_start . ' 00:00:00';
                $date_end = $date_end . ' 23:59:59';
                                
                $res = process_query(Array(
                    'q' => SQL_WIZARD_ORDERS_FULFILLMENT_ITEMS_BY_VENDOR_ID_AND_DELIVERY_STATUS,
                    'date_start'                => $date_start,
                    'date_end'                  => $date_end,
                    'date_column'               => 'orders.order_tcreate',
                    'query_id'                  => $vendor_id,
                    'status_id'                 => $status_id,
                    'order_is_refunded'         => $order_is_refunded,
                    'order_alert_status'        => $order_alert_status,
                    'tables_columns'            => $tables_columns,
                    'page_limit'                => $page_limit,
                    'page_index'                => $page_index,
                ));

                if(empty($res)){
                    $number_of_records = 0;
                    $page_index = 0;
                } else {                
                    $_res = Array();
                    foreach($res as $o)
                    {
                        $_items = Array();
                        foreach($o as $k=>$v)
                        {
                            //if($k=='vendor_id' && $v == 'null'){
                            //    $_items[$k] = "0";
                            //} else {
                                $_items[$k] = $v;
                            //}
                        };                    
                        $_res[] = $_items;
                    }
                    $res = $_res;
                    $number_of_records = sizeof($res);
                }
                $_message = Array(
                                    'number_of_records'         => $number_of_records,
                                    'page'                      => $page_index,
                                    'sql' => $wizard_sql,
                                );                
                $message = json_encode($_message);                
                global $wizard_sql;
                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res,
                    )
                ));
            break;
            
            /** +---------------------------------+
             *  | SQL_WIZARD_17TRACK_FULFILLMENTS |
             *  +---------------------------------+
             *
             *  DESCRIPTION:
             *  Wizard for refreshing order_delivery_status
             *
             *  END-POINT:
             *  http://########/report/wizard/wizard_17track_fulfillments/?fulfillment_id[]=1&fulfillment_id[]=2
             *  http://########/canary/canary/index.php/report/wizard/wizard_17track_fulfillments/?fulfillment_id[]=1&fulfillment_id[]=2
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'wizard_17track_fulfillments':
                
                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */
                $result = [];
                $res = [];
                foreach($fulfillment_id as $_fulfillment_id){
                    $result = process_query(Array(
                        'q' => SQL_WIZARD_17TRACK_FULFILLMENTS,
                        'query_id'    => "`fulfillment_id`='$_fulfillment_id'",
                    ));
                    //echo print_r($result) . "\n";
                    $res[]=$result[0];
                }
                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_items = Array();
                    foreach($o as $k=>$v)
                    {
                        $_items[$k] = $v;
                    };                    
                    $_res[] = $_items;
                }
                $res = $_res;

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'report' => $res
                    )
                ));
            break;
            
            /** +------------------------+
             *  | SQL_WIZARD_TEST_ORDERS |
             *  +------------------------+
             *
             *  DESCRIPTION:
             *  Wizard for creating requests
             *
             *  END-POINT:
             *  http://########/report/wizard/wizard_test_orders?date_start=2017-10-09&date_end=2017-11-10&limit=10
             *  http://########/canary/canary/index.php/report/wizard/wizard_test_orders?date_start=2018-01-01&date_end=2018-01-06&limit=10
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'wizard_test_orders':
                
                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q' => SQL_WIZARD_TEST_ORDERS,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'orders.order_tcreate',
                    'page_limit'    => $page_limit,
                    'page_index'    => $page_index,
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_items = Array();
                    foreach($o as $k=>$v)
                    {
                        $_items[$k] = $v;
                    };                    
                    $_res[] = $_items;
                }
                $res = $_res;

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res
                    )
                ));
            break;
            
            /** +------------------------------+
             *  | SQL_WIZARD_TEST_FULFILLMENTS |
             *  +------------------------------+
             *
             *  DESCRIPTION:
             *  Wizard for creating requests
             *
             *  END-POINT:
             *  http://########/report/wizard/wizard_test_fulfillments?date_start=2017-10-09&date_end=2017-11-10&limit=10
             *  http://########/canary/canary/index.php/report/wizard/wizard_test_fulfillments?date_start=2018-01-01&date_end=2018-01-07&limit=10
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'wizard_test_fulfillments':
                
                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */

                $res = process_query(Array(
                    'q' => SQL_WIZARD_TEST_FULFILLMENTS,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'fulfillments.fulfillment_tcreate',
                    'page_limit'    => $page_limit,
                    'page_index'    => $page_index,
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_items = Array();
                    foreach($o as $k=>$v)
                    {
                        if(mb_detect_encoding($v) == "UTF-8"){
                            //$v = iconv("UNICODE","UTF-8",$v);
                            $v = mb_convert_encoding($v, "UTF-8", "UNICODE");
                        }
                        $_items[$k] =  $v;
                    };                    
                    $_res[] = $_items;
                }
                $res = $_res;

                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res
                    )
                ));
            break;
            
            /** +---------------------------------+
             *  | SQL_WIZARD_TEST_VENDOR_TRACKING |
             *  +---------------------------------+
             *
             *  DESCRIPTION:
             *  Wizard for creating requests
             *
             *  END-POINT:
             *  http://########/report/wizard/wizard_test_vendor_tracking?date_start=2017-10-09&date_end=2017-11-10&limit=10
             *  http://########/canary/canary/index.php/report/wizard/wizard_test_vendor_tracking?date_start=2018-01-01&date_end=2018-01-07&limit=10
             *      date_start=2017-10-09
             *      date_end=2017-11-10
             *
             */
            case 'wizard_test_vendor_tracking':
                
                /** ---------------------------------------------
                 *  Pass parameters and specific query to process
                 *  --------------------------------------------- */
                
                $res = process_query(Array(
                    'q' => SQL_WIZARD_TEST_VENDOR_TRACKING,
                    'date_start'    => $date_start,
                    'date_end'      => $date_end,
                    'date_column'   => 'vendor_tracking.tracking_tcreate',
                    'page_limit'    => $page_limit,
                    'page_index'    => $page_index,
                ));

                if(empty($res)) $message = 'No data could be found for the selected period.';
                else $message = '';

                $_res = Array();
                foreach($res as $o)
                {
                    $_items = Array();
                    foreach($o as $k=>$v)
                    {
                        if(mb_detect_encoding($v) == "UTF-8"){
                            $v = $v;
                        }
                        $_items[$k] =  $v;
                    };                    
                    $_res[] = $_items;
                }
                $res = $_res;
                
                api_response(array(
                    'code' => RESPONSE_SUCCESS,
                    'msg'=>$message,
                    'data' => Array(
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'report' => $res
                    )
                ));
            break;
            
            default:
                api_response(array(
                    'code'=> RESPONSE_ERROR,
                    'data'=> array('message'=>ERROR_INVALID_FUNCTION)
                ));
            break;

        }
    break;

    default:
        api_response(array(
            'code'=> RESPONSE_ERROR,
            'data'=> array('message'=>ERROR_INVALID_FUNCTION)
        ));
    break;
}

function process_query($o) //$_q,$_date, $query_id='',$status_id='',$limit=''
{
    global $report_name;

    $date_start = $o['date_start'];
    $date_end = $o['date_end'];
    $date_column = $o['date_column'];

    $query_id = isset($o['query_id'])?$o['query_id']:'';
    $query =  isset($o['q'])?$o['q']:false;
    $status_id =  isset($o['status_id'])?$o['status_id']:'';
    
    
    /** ------
     *  WIZARD
     *  ------ */
    $tables_columns = $o['tables_columns'];
    $order_is_refunded = $o['order_is_refunded'];
    $order_alert_status = $o['order_alert_status'];
    
    /**
     * Build columns for query
     */
    $_tables_columns = '';
    if(is_array($tables_columns) && sizeof($tables_columns) > 0){
        $separator = '';
        foreach ($tables_columns as $v){
            $_tables_columns .= $separator . $v;
            $separator = ', ';
        }
    }
    $tables_columns = $_tables_columns;
    
    /**
     * If 'query_id' is an array and isn't empty 
     * and not contain all possible conditions
     * then build query condition for Wizard
     * 
     * If 'status_id' is empty or contain all possible
     * conditions we don't need set condition 
     */
    $_query_id = '';
    if(is_array($query_id) && sizeof($query_id) > 0 && sizeof($query_id) < 6){
        $separator = '';
        $_query_id = ' AND (';
        foreach ($query_id as $v){
            if($v == 0){
                $_query_id .= $separator . "vendor_tracking.vendor_id IS NULL";
            } else {
                $_query_id .= $separator . "vendor_tracking.vendor_id='" . $v . "'";
            }
            $separator = ' OR ';
        }
        $_query_id .= ') ';
    }
    if(is_array($query_id)){
        $query_id = $_query_id;
    };
    /**
     * If 'status_id' is an array and isn't empty 
     * and not contain all possible conditions
     * then build query condition for Wizard
     * 
     * If 'status_id' is empty or contain all possible
     * conditions we don't need set condition 
     */
    $_status_id = '';
    if(is_array($status_id) && sizeof($status_id) > 0 && sizeof($status_id) < 9){
        $separator = '';
        $_status_id = ' AND (';
        foreach ($status_id as $v){
            $_status_id .= $separator . "orders.order_delivery_status='" . $v . "'";
            $separator = ' OR ';
        }
        $_status_id .= ') ';        
    }
    if(is_array($status_id)){
        $status_id = $_status_id;
    }
    
    /**
     * If 'order_is_refunded' is an array and isn't empty 
     * and not contain all possible conditions
     * then build query condition for Wizard
     * 
     * If 'status_id' is empty or contain all possible
     * conditions we don't need set condition 
     */
    $_order_is_refunded = '';
    if(is_array($order_is_refunded) && sizeof($order_is_refunded) > 0 && sizeof($order_is_refunded) < 3){
        $separator = '';
        $_order_is_refunded = ' AND (';
        foreach ($order_is_refunded as $v){
            $_order_is_refunded .= $separator . "orders.order_is_refunded='" . $v . "'";
            $separator = ' OR ';
        }
        $_order_is_refunded .= ') ';
    }
    $order_is_refunded = $_order_is_refunded;
    
    /**
     * If 'order_alert_status' is an array and isn't empty 
     * and not contain all possible conditions
     * then build query condition for Wizard
     * 
     * If 'status_id' is empty or contain all possible
     * conditions we don't need set condition 
     */
    $_order_alert_status = '';
    if(is_array($order_alert_status) && sizeof($order_alert_status) > 0 && sizeof($order_alert_status) < 7){
        $separator = '';
        $_order_alert_status = ' AND (';
        foreach ($order_alert_status as $v){
            $_order_alert_status .= $separator . "orders.order_alert_status='" . $v . "'";
            $separator = ' OR ';
        }
        $_order_alert_status .= ') ';
    }
    $order_alert_status = $_order_alert_status;
    
    /*
     * END WIZARD
     */
    
    $limit_index = isset($o['page_index'])?$o['page_index']--:false;
    $limit_max = isset($o['page_limit'])?$o['page_limit']:false;
    $limit = '';
    
    if($limit_max)
    {
        $limit = ' LIMIT ' . $limit_max . ' OFFSET ' . ($limit_max * $limit_index);
    }

    $sort = '';
    $sort_column = isset($o['sort_column'])?$o['sort_column']:false;
    $sort_type  = isset($o['sort_type'])?$o['sort_type']:false;

    if($sort_column)
    {
        $sort = ' ORDER BY ' . $sort_column . ' ' . (($sort_type == 0)?'ASC':'DESC');
    }

    //Lets process any date range
    $q = replace_strings($query,Array(
        '{DATE_RANGE}'      => ' AND ' . $date_column . ' BETWEEN "' . $date_start . '" AND "' . $date_end . '"',
        '{ID}'              => $query_id,
        '{STATUS}'          => $status_id,
        '{REFUND}'          => $order_is_refunded,
        '{ALERT}'           => $order_alert_status,
        '{TABLES_COLUMNS}'  => $tables_columns,
        '{LIMIT}'           => $limit,
        '{SORT}'            => $sort
    ));
    global $wizard_sql;
    $wizard_sql = $q;
    //echo $q;
    //exit;
    //exit('<pre>'.$q);

    $db_instance = new Database();
    $result = $db_instance->db_query($q,DATABASE_NAME);

    unset($db_instance);

    log_error('report_query: ' . PHP_EOL . $q . PHP_EOL . print_r($result,true));
    if($result === false)
    {
        api_response(array(
            'code'=> RESPONSE_ERROR,
            'msg'=>'There was an error running report: ' . $report_name,
        ));
    } else
    return $result;
}


function is_valid_date($date)
{
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",$date))
    return true;
     else
        return false;
}

