<?php
/** =====================
 *  cron.controller.php
 *  =====================
 *  ------
 *  ABOUT:
 *  ------
 *  Runs manual functions
 *
 */
global $controllerID,$controllerObject,$controllerFunction,$controllerData,$vendor_array;
header('Content-Type: text/html; charset=utf-8');

define('TRACKING_REGEX_CHINAPOST',"/[A-Z]{2,5}[0-9]{5,12}/");
define('ROW_SCAN_LIMIT',10);
define('IS_DEBUG',false);
define('DEBUG_ROW_LIMIT',10);

define('ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND',1);
define('ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND',2);
define('ERROR_SHEET_PARSING_ORDER_FULFILLMENT_NUMBER_NOT_FOUND',3);
define('ERROR_SHEET_FILE_ERROR',4);

define('SHEET_STATUS_UNKNOWN',0);
define('SHEET_STATUS_SUCCESS',1);
define('SHEET_STATUS_WARNING',2);
define('SHEET_STATUS_FAILURE',3);

//For vendor sheet uploads, condition flags
define('SHEET_MATCH_EXACT_MATCH',0);
define('SHEET_MATCH_CONTAINS',1);
define('SHEET_MATCH_REGEX',2);

//For vendor sheet uploads, which column to match conditions on
define('SHEET_COLUMN_ORDER_RECEIPT_ID',1);
define('SHEET_COLUMN_TRACKING_NUMBER',2);

/**
define('VENDOR_APR',1);
define('VENDOR_CHENXIAOWEI',2);
define('VENDOR_DANI',3);
define('VENDOR_EZ',4);
define('VENDOR_ROBIN',5);
 */

switch($controllerFunction)
{
    case 'orders':
        define('ORDER_COUNT_LIMIT_MANUAL', 0);
        $api_start_time = date('c',(time()-(60*60)));
        $api_end_time = date('c',time());

        $_url = 'https://f1d486bdbc7147e7d601cda568c738d0:957268353e6ec273aa9883dd5d50e171@omg-true.myshopify.com/admin/orders.json?created_at_min=' . $api_start_time . '&created_at_max=' . $api_end_time . '&status=any&limit=250';
        
        $json = json_decode(file_get_page($_url),true);        
        //exit('result:<pre> '.print_r($json,true));
        process_orders($json['orders']);
    break;

    case 'order':
        
        $json = json_decode($_POST['order'],true);        
        //exit('result:<pre> '.print_r($json,true));
        process_orders($json);
    break;


    case 'vendor':

        require(LIB_PATH . 'php-excel-reader/excel_reader2.php');
        require(LIB_PATH . 'SpreadsheetReader.php');

        $db_instance = new Database();

        print '<pre>';
        if(empty($controllerID)) exit('Vendor ID not provided');

        $vendor_id = $controllerID;
        $file_path = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'] .'/';

        $files = scandir($file_path);

        $_count = 0;


        /** =====
         *  FILES
         *  ===== */
        foreach($files as $f)
        {
            $sheet_status = SHEET_STATUS_SUCCESS;

            if(substr($f,count($f)-6,5)=='.xlsx' ||
                substr($f,count($f)-5,4)=='.xls'
            )
           {
                try {

                    $file_name = $file_path . $f;
                    $excel = new SpreadsheetReader($file_name);
                    $sheets = $excel->Sheets();

                    print PHP_EOL . '---------------------' . PHP_EOL;
                    print 'Opening file: ' . $file_name;
                    print '---------------------' . PHP_EOL;

                    $sheet_result = Array();

                    /** ====================
                     *  SHEETS IN A WORKBOOK
                     *  ==================== */
                    

                    $file_total_count = 0;
                    $file_error_count = 0;
                    foreach($sheets as $sheet_index=>$sheet)
                    {
                        print 'Sheet: ' . $sheets[$sheet_index] . PHP_EOL;
                        $sheet_name = $sheets[$sheet_index];
                        $excel->ChangeSheet($sheet_index);

                      
                        $order_column_index =  get_order_receipt_id_column_index($excel);
                        if($order_column_index === false) {
                            print '#### Order Receipt ID NOT FOUND in file: ' . $file_name . ' SHEET: ' . $sheets[$sheet_index] . PHP_EOL;
                            $sheet_status = SHEET_STATUS_WARNING;
                            if(!isset($sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND]))
                                $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND] = 0;
                            $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND]++;
                            $sheet_result[$vendor_id][$f][$sheet_name]['status'] = $sheet_status;
                            $sheet_result_count++;
                            
                            continue;
                        }

                        $column_index = get_tracking_column_index($excel,TRACKING_REGEX_CHINAPOST);
                        if(!$column_index) {
                            print 'TRACKING NUMBER COLUMN INDEX NOT FOUND! Skipping..' . PHP_EOL;
                            $sheet_status = SHEET_STATUS_WARNING;
                            if(!isset($sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND]))
                                $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND] = 0;
                            $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND]++;
                            $sheet_result[$vendor_id][$f][$sheet_name]['status'] = $sheet_status;

                            continue;
                        }

                        print 'Tracking # index found: ' . $column_index . PHP_EOL;


                        $sheet_success_count = 0;
  
                        foreach ($excel as $k => $row) {

                            $sheet_total_count = count($excel);
                            //print PHP_EOL . 'data: ' . $row[$column_index];
                            if(!isset($row[$column_index])) continue;
                            $tracking_number = $row[$column_index];


                            if(stripos($row[$order_column_index],'omgt') > -1)
                            {
                                $order_receipt_id = $row[$order_column_index];
                            } else $order_receipt_id = '#OMGT6' . $row[$order_column_index];

                            if (empty($order_receipt_id)) {
                                print 'ORDER RECEIPT ID NOT FOUND IN COLUMN INDEX ' . $column_index . '  Skipping..' . PHP_EOL;
                                $sheet_status = SHEET_STATUS_WARNING;
                                if(!isset($sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND]))
                                    $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND] = 0;
                                $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_SHOP_ORDER_ID_NOT_FOUND]++;
                                $sheet_result[$vendor_id][$f][$sheet_name]['status'] = $sheet_status;
                                continue;
                            }

                            if (empty($tracking_number) || !preg_match(TRACKING_REGEX_CHINAPOST, $tracking_number)) {
                                print 'TRACKING NUMBER NOT FOUND IN COLUMN INDEX ' . $column_index . '  Skipping..' . PHP_EOL;
                                $sheet_status = SHEET_STATUS_WARNING;
                                if(!isset($sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND]))
                                    $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND] = 0;
                                $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND]++;
                                $sheet_result[$vendor_id][$f][$sheet_name]['status'] = $sheet_status;
                                continue;
                            }

                            

                            //print 'tracking_number: ' . $tracking_number . PHP_EOL;

                            $data = $db_instance->db_retrieve('fulfillments', Array('order_id', 'fulfillment_id'), Array('fulfillment_tracking_number' => $tracking_number));
                            //print 'mysql results: '. print_r($data,true);

                            $order_id = $fulfillment_id = null;

                            if (!empty($data)) {
                                $fulfillment_id = $data[0]['fulfillment_id'];
                                $order_id = $data[0]['order_id'];
                                $db_array = Array(
                                    'tracking_number' => $tracking_number,
                                    'fulfillment_id' => $fulfillment_id,
                                    'order_id' => $order_id,
                                    'vendor_id' => $vendor_id,
                                    'order_receipt_id'=>$order_receipt_id,
                                    'tracking_tcreate' => current_timestamp(),
                                    'tracking_tmodified'=>current_timestamp()
                                );
                                //print print_r($db_array, true) . PHP_EOL . PHP_EOL;
                                $result = $db_instance->db_create('vendor_tracking',$db_array);
                                
                            } else {

                                //print 'Tracking number not found. Looking up by order_receipt_id: ' . $order_receipt_id . PHP_EOL;
                                $q = 'SELECT O.order_id as order_id, F.fulfillment_id as fulfillment_id FROM orders O INNER JOIN fulfillments F ON F.order_id = O.order_id WHERE O.order_receipt_id ="' . $order_receipt_id .'";';
                                $result = $db_instance->db_query($q,DATABASE_NAME);
                                //error_log('query: '.$q.PHP_EOL. 'result: ' . print_r($result,true));
                                
                                //Successfully found matching fulfillment_id and order_id from database
                                if(!empty($result))
                                {
                                    $fulfillment_id = $result[0]['fulfillment_id'];
                                    $order_id = $result[0]['order_id'];

                                    $db_array = Array(
                                        'tracking_number' => $tracking_number,
                                        'fulfillment_id' => $fulfillment_id,
                                        'order_id' => $order_id,
                                        'vendor_id' => $vendor_id,
                                        'order_receipt_id'=>$order_receipt_id,
                                        'tracking_tcreate' => current_timestamp(),
                                        'tracking_tmodified'=>current_timestamp()
                                    );
                                } else {
                                    //If there is no matching fulfillment row, its likely the item was refunded
                                    $db_array = Array(
                                        'tracking_number' => $tracking_number,
                                        'vendor_id' => $vendor_id,
                                        'order_receipt_id'=>$order_receipt_id,
                                        'tracking_tcreate' => current_timestamp(),
                                        'tracking_tmodified'=>current_timestamp()
                                    );
                                    //print print_r($db_array, true) . PHP_EOL . PHP_EOL;
                                    print '### order_id and fulfillment_id not found in current orders!' . PHP_EOL;
                                    $sheet_status = SHEET_STATUS_WARNING;
                                    if(!isset($sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_ORDER_FULFILLMENT_NUMBER_NOT_FOUND]))
                                        $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_ORDER_FULFILLMENT_NUMBER_NOT_FOUND] = 0;
                                    $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_PARSING_ORDER_FULFILLMENT_NUMBER_NOT_FOUND]++;
                                    $sheet_result[$vendor_id][$f][$sheet_name]['status'] = $sheet_status;
                                }

                                $result = $db_instance->db_create('vendor_tracking',$db_array);
                            }
                            $sheet_success_count++;
                            print $tracking_number . ' - order_id:' . $order_id . ' fulfillment_id: ' . $fulfillment_id . ' receipt_id: ' . $order_receipt_id .  PHP_EOL;

                            $_count++;

                            if(IS_DEBUG && $_count >= DEBUG_ROW_LIMIT) exit(PHP_EOL . 'Spreadsheet scanning halted at row limit: ' . DEBUG_ROW_LIMIT);
                        }
                        $sheet_result[$vendor_id][$f][$sheet_name]['success'] = $sheet_success_count;
                        $sheet_result[$vendor_id][$f][$sheet_name]['total'] = $sheet_total_count;
                    }
                    unset($excel);
                } catch (Exception $E) {
                    $sheet_status = SHEET_STATUS_FAILURE;
                    if(!isset($sheet_name)) $sheet_name = 'ERROR_NO_SHEET_FOUND';
                    if(!isset($sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_FILE_ERROR]))
                        $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_FILE_ERROR] = 0;
                    $sheet_result[$vendor_id][$f][$sheet_name]['errors'][ERROR_SHEET_FILE_ERROR]++;
                    $sheet_result[$vendor_id][$f][$sheet_name]['status'] = $sheet_status;
                    echo $E->getMessage();
                }

                log_vendor_sheet_stats($sheet_result);


               $file_origin = $file_name;
               $_status = $sheet_result[$vendor_id][$f][$sheet_name]['status'];

               switch($_status)
               {
                   case SHEET_STATUS_FAILURE:
                       $file_dest = $file_path . 'error/' . $f;
                   break;

                   default:
                   case SHEET_STATUS_WARNING:
                   case SHEET_STATUS_SUCCESS:
                       $file_dest = $file_path . 'done/' . $f;
                   break;
               }

               rename($file_origin, $file_dest);


           }


        }
        unset($db_instance);
        exit(PHP_EOL .'Total rows processed: ' . $_count . PHP_EOL . 'report: ' . print_r($sheet_result,true));

    break;

    default:
        exit();
    break;
}

function log_vendor_sheet_stats($data)
{
    $sheet_count = 0;
    $sheet_name = '';
    $success_count = 0;
    $sheet_status = 0;

    print PHP_EOL . print_r($data) . PHP_EOL;
    foreach($data as $_vendor_id=>$vendor_data)
    {
        $vendor_id = $_vendor_id;
        print 'vendor_id: ' . $vendor_id . PHP_EOL;

        //Vendor File Level
        foreach($vendor_data as $f=>$sheet_data)
        {
            $file_name = $f;
            print 'file_name: ' . $file_name . PHP_EOL;
            //print 'f: ' . $f . ' o: ' . print_r($sh,true);

            $file_json_data = json_encode($vendor_data);

            //Vendor Sheet Level
            foreach($sheet_data as $i => $o)
            {
                if(isset($o['status']) && intval($o['status'])> $sheet_status) $sheet_status = intval($o['status']);
                if(isset($o['success']) && intval($o['success']) > 0) $success_count += intval($o['success']);
                if(isset($o['total']) && intval($o['total']) > 0) $sheet_count += intval($o['total']);
            }

            $db_instance = new Database();

            $vendor_array = Array(
                'vendor_id'=>$vendor_id,
                'file_name'=>$file_name,
                'file_status'=>$sheet_status,
                'file_success_count'=>$success_count,
                'file_total_rows'=>$sheet_count,
                'file_error_rows'=>$sheet_count - $success_count,
                'file_error_json'=>$file_json_data,
                'file_tcreate'=>current_timestamp(),
                'file_tmodified'=>current_timestamp(),
            );
            $result = $db_instance->db_create('vendor_file_stats',$vendor_array);
            if(!$result)
            {
                print('DATABASE ERROR: ' . print_r($result,true) . PHP_EOL);
            }

        }

    }
}


function get_order_receipt_id_column_index($sheet)
{
    //店铺单号
    $row_count = 0;
    foreach ($sheet as $row) {

        $_count = 0;
        foreach ($row as $o) {
            //print $_count . ' - checking: ' . $o . PHP_EOL;
            if (
                $o == '编码' ||
                $o == '店铺单号'||
                $o == '订单编号'||
                $o == '订单号' ||
                stripos($o, 'order #') > -1 ||
                stripos($o,'店铺单号') > -1  ||
                stripos($o,'order num') > -1
            ){
                print 'MATCH "' . $o . '" FOUND ON ' . $_count . ' COLUMN INDEX!!!' . PHP_EOL;
                return $_count;
            }
            $_count++;
        }
        if($row_count >= ROW_SCAN_LIMIT) return false;
        $row_count++;

    }
    //print 'NOT FOUND!!' . print_r($sheet,true) . PHP_EOL;
    return false;

}

function get_tracking_column_index($sheet,$regex)
{
    $row_count = 0;
    foreach ($sheet as $row) {

        $_count = 0;
        foreach ($row as $o) {
            //print $_count . ' - checking: ' . $o . PHP_EOL;
            if (preg_match($regex, $o)) {
                //print 'MATCH FOUND ON ' . $_count . ' COLUMN INDEX!!!';
                return $_count;
            }
            $_count++;
        }
        if($row_count >= ROW_SCAN_LIMIT) return false;
        $row_count++;

    }

    return false;
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
        $order_count++;
        /** ------------
         *  PARSE ORDERS
         *  ------------ */
        $order_shopify_id = $o['id'];
        log_error('order_shopify_id: ' . $order_shopify_id);

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
            if (stripos($o['gateway'], 'stripe') > -1) $order_gateway = GATEWAY_PROVIDER_STRIPE;
            elseif (stripos($o['gateway'],  'paypal') > -1 ) $order_gateway = GATEWAY_PROVIDER_PAYPAL;
            elseif (stripos($o['gateway'],  'shopify_payments') > -1 ) $order_gateway = GATEWAY_PROVIDER_PAYPAL;
            else $order_gateway = GATEWAY_PROVIDER_UNKNOWN;
        }

        $order_fulfillment_status = $o['fulfillment_status'];
        $order_is_dropified = (!empty($o['note']) && stripos($o['note'], 'dropified') > -1 );
        
        /**
         * Check if note has 'Aliexpress' or 'Dropified'.
         * 
         * If has then: 
         * - get events related to order
         * - check if Dropified created fulfillments
         * - return array of ids of fulfillments created by Dropified
         * 
         * If not then:
         * - return empty array
         * 
         * Added by Rafal - 2018-03-13
         */
        
        $dropified_fulfillmets_ids = (stripos($o['note'], 'dropified') > -1 || stripos($o['note'], 'aliexpress') > -1)?getDropifiedEvents(getOrderEvents($order_shopify_id)):array();
        
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


        $order_is_ocu = (stripos($order_tags,'ocu') > -1)?1:0;

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
        
        /** ----------------------------------------
         *  _ORDER_ARRAY - ADDED BY RAFAL 2018-03-20
         * 
         * This is added to update only order if 
         * is cancel order status
         *  --------------------------------------- */
        

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
            $_fulfillment_status = $p['fulfillment_status'];
            switch(strtolower($_fulfillment_status))
            {
                case 'fulfilled': $item_fulfillment_status = 1;break;
                case 'partial': $item_fulfillment_status = 2; break;
                default: case null: $item_fulfillment_status = 0;break;
            }
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
                    //print 'FOUND REFUND!';
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
                'item_is_fulfilled'=>$item_fulfillment_status
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
                $fulfillment_shipment_status = strtolower(trim($fulfillment['shipment_status']));
                $fulfillment_topen = date("Y-m-d H:i:s", strtotime($fulfillment['created_at']));
                //$fulfillment_tmodified = date("Y-m-d H:i:s", strtotime($fulfillment['updated_at']));
                
                $fulfillment_tracking_company = strtolower($fulfillment['tracking_company']);
                                
                $fulfillment_tracking_number = $fulfillment['tracking_number'];
                $fulfillment_tracking_url = $fulfillment['tracking_url'];

                $order_is_fulfilled = true;


                $fulfillment_array = Array(
                    'order_id' => $order_id,
                    'order_shopify_id' => $order_shopify_id,
                    'fulfillment_shipment_status' => trim(strtolower($fulfillment_shipment_status)),
                    'fulfillment_topen'=>$fulfillment_topen,
                    'fulfillment_shopify_id' => $fulfillment_shopify_id,
                    'fulfillment_tracking_number' => $fulfillment_tracking_number,
                    'fulfillment_tracking_company' => $fulfillment_tracking_company,
                    'fulfillment_tracking_url' => $fulfillment_tracking_url,
                    'order_is_fulfilled' => $order_is_fulfilled,
                );
                
                if ($fulfillment_tracking_company !== 'usps') $is_tracking = true;
                else $is_tracking = false;

                $order_delivery_status = false;

                //Normally USPS is the only courier that updates this appropriately. If its delivered set status
                switch ($fulfillment_shipment_status) {
                    case 'delivered':
                        $order_delivery_status = DELIVERY_STATUS_DELIVERED;
                        $order_is_delivered = true;
                        $fulfillment_array['fulfillment_delivery_status'] = $order_delivery_status;
                        $order_array['order_delivery_status'] = $order_delivery_status;
                        $fulfillment_array['fulfillment_status_delivered_tcreate'] = current_timestamp();
                        if($is_tracking) $fulfillment_array['fulfillment_is_tracking'] = 0;
                        break;

                    case 'confirmed':
                        $order_delivery_status = DELIVERY_STATUS_CONFIRMED;
                        $fulfillment_array['fulfillment_delivery_status'] = $order_delivery_status;
                        $order_array['order_delivery_status'] = $order_delivery_status;
                        $fulfillment_array['fulfillment_status_confirmed_tcreate'] = current_timestamp();
                        if($is_tracking) $fulfillment_array['fulfillment_is_tracking'] = 1;
                        break;

                    case 'in_transit':
                        $order_delivery_status = DELIVERY_STATUS_IN_TRANSIT;
                        $fulfillment_array['fulfillment_delivery_status'] = $order_delivery_status;
                        $order_array['order_delivery_status'] = $order_delivery_status;
                        $fulfillment_array['fulfillment_status_in_transit_tcreate'] = current_timestamp();
                        if($is_tracking) $fulfillment_array['fulfillment_is_tracking'] = 1;
                        break;

                    case 'out_for_delivery':
                        $order_delivery_status = DELIVERY_STATUS_OUT_FOR_DELIVERY;
                        $fulfillment_array['fulfillment_delivery_status'] = $order_delivery_status;
                        $order_array['order_delivery_status'] = $order_delivery_status;
                        $fulfillment_array['fulfillment_status_out_for_delivery_tcreate'] = current_timestamp();
                        if($is_tracking) $fulfillment_array['fulfillment_is_tracking'] = 1;
                        break;

                    case 'failure':
                        $order_delivery_status = DELIVERY_STATUS_FAILURE;
                        $fulfillment_array['fulfillment_delivery_status'] = $order_delivery_status;
                        $order_array['order_delivery_status'] = $order_delivery_status;
                        $fulfillment_array['fulfillment_status_failure_tcreate'] = current_timestamp();
                        if($is_tracking) $fulfillment_array['fulfillment_is_tracking'] = 0;
                        $order_array['order_alert_status'] = DELIVERY_STATUS_FAILURE;
                        $fulfillment_array['fulfillment_alert_status'] = DELIVERY_STATUS_FAILURE;
                        break;

                    default:
                        $order_delivery_status = DELIVERY_STATUS_UNKNOWN;
                        $fulfillment_array['fulfillment_delivery_status'] = $order_delivery_status;
                        if($is_tracking) $fulfillment_array['fulfillment_is_tracking'] = 1;

                        break;
                }
/*
                if ($order_delivery_status)
                    $order_array['delivery_status'] = $order_delivery_status;*/
                
                
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
                    $fulfillment_array['fulfillment_tracking_number_tcreate'] = current_timestamp();
                    $fulfillment_object = new Fulfillment($fulfillment_array);
                    $fulfillment_object->save();

                } else {

                    if($fulfillment_object->tracking_number == "" || $fulfillment_object->tracking_number == null)
                        $fulfillment_array['fulfillment_tracking_number_tcreate'] = current_timestamp();

                    $fulfillment_object->save($fulfillment_array);
                }
                
                $order_object->order_fulfillments[] = $fulfillment_object;
                
                
                /** Added by Rafal - 2018-03-13 */
                if(sizeof($dropified_fulfillmets_ids) > 0 && in_array($fulfillment_shopify_id,$dropified_fulfillmets_ids) === true){
                    $dropified_array = Array(
                                        'tracking_number' => $fulfillment_tracking_number,
                                        'fulfillment_shopify_id' => $fulfillment_shopify_id,
                                        'order_id' => $order_id,
                                        'vendor_id' => VENDOR_DROPIFIED,
                                        'order_receipt_id'=>$order_receipt_id,
                                        'tracking_tcreate' => current_timestamp(),
                                        'tracking_tmodified'=>current_timestamp()
                                    );
                    set_dropified_tracking($dropified_array);
                }
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

        if ($order_count >= ORDER_COUNT_LIMIT_MANUAL && ORDER_COUNT_LIMIT_MANUAL != 0) exit('ORIGINAL DATA: ' . PHP_EOL . print_r($order_count, true));

    }
    log_error('total orders: ' . $order_count);


}


function file_get_page($url)
{
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    $contents = curl_exec($ch);
    if (curl_errno($ch)) {
       return false;
    } else {
        curl_close($ch);
    }

    if (!is_string($contents) || !strlen($contents)) {
        return false;
    }
    return $contents;
}


/**
 * Get Order All Events
 * Added by Rafal 2018-03-13
 * 
 */

/**
 * Get Order All Events
 * and return it as array
 * 
 * Added by Rafal 2018-03-13
 * 
 * @param type string $order_id
 * @return type array
 */
function getOrderEvents($order_shopify_id){
    $_url = 'https://f1d486bdbc7147e7d601cda568c738d0:957268353e6ec273aa9883dd5d50e171@omg-true.myshopify.com/admin/orders/' . $order_shopify_id . '/events.json';
    return json_decode(file_get_page($_url),true);
}

/**
 * Select events added by Dropified
 * and return array of fulfillments ids
 * 
 * Added by Rafal 2018-03-13
 * 
 * @param type array $order_events
 */
function getDropifiedEvents($order_events){
    
    $fulfillments_ids = array();
    foreach($order_events['events'] as $event){
        if($event['subject_type'] == 'Order' &&
            $event['author'] == 'Dropified (formerly Shopified App)'&& 
            $event['verb'] == 'fulfillment_success'){
            array_push($fulfillments_ids,$event['arguments'][0]);
        }
    }
    return $fulfillments_ids;
}

/**
 * Add new or update existing trecking
 * 
 * Added by Rafal 2018-03-14
 * 
 * @param type array $dropified_array
 */
function set_dropified_tracking($_dropified_array){
    
    $db_instance = new Database();
    
    $ids = $db_instance->db_retrieve('fulfillments', Array('fulfillment_id'), Array('fulfillment_shopify_id'=>$_dropified_array['fulfillment_shopify_id']));
    
    foreach($ids as $id){
        
        $data = Array();
        $db_conditions = Array(
                                'tracking_number' => $_dropified_array['tracking_number'],
                                'fulfillment_id' => $id['fulfillment_id']
                            );
        $data = $db_instance->db_retrieve('vendor_tracking', Array('tracking_number', 'fulfillment_id'), $db_conditions);
        
        if(sizeof($data) > 0){

            $dropified_array = Array(
                                        'vendor_id' => $_dropified_array['vendor_id'],
                                        'tracking_tmodified'=>$_dropified_array['tracking_tmodified']
                                    );
            $db_instance->db_update('vendor_tracking',$dropified_array,$db_conditions);

        } else {

            $dropified_array = Array(
                                        'tracking_number' => $_dropified_array['tracking_number'],
                                        'fulfillment_id' => $id['fulfillment_id'],
                                        'order_id' => $_dropified_array['order_id'],
                                        'vendor_id' => $_dropified_array['vendor_id'],
                                        'order_receipt_id'=>$_dropified_array['order_receipt_id'],
                                        'tracking_tcreate' => $_dropified_array['tracking_tcreate'],
                                        'tracking_tmodified'=>$_dropified_array['tracking_tmodified']
                                    );
            $result = $db_instance->db_create('vendor_tracking',$dropified_array);

        }
    }
    unset($db_instance);
    
    return;
}