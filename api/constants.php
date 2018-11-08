<?php

/** ==============
 *  ERROR MESSAGES
 *  ==============
 */

define('ERROR_INVALID_PARAMETERS','Invalid parameters passed');
define('ERROR_INVALID_OBJECT','Invalid object');
define('ERROR_INVALID_USER_ID','Invalid ID for object');
define('ERROR_INVALID_FUNCTION','Invalid object function');
define('ERROR_NO_DATA_AVAILABLE','No data available for object');
define('ERROR_PARSING_DATA','An internal error occurred attempting to parse the data from the source');

/** =============
 *  MODEL CLASSES
 *  =============
 */
define('SERIALIZE_DATABASE',0);
define('SERIALIZE_JSON',1);

/** ==============
 *  RESPONSE CODES
 *  ==============
 */
define('RESPONSE_SUCCESS',1);
define('RESPONSE_ERROR',0);


/** =================
 *  CONSTANTS MAPPING
 *  =================
 */

//Gateway providers
define('GATEWAY_PROVIDER_UNKNOWN',0);
define('GATEWAY_PROVIDER_STRIPE',1);
define('GATEWAY_PROVIDER_PAYPAL',2);
define('GATEWAY_PROVIDER_SHOPIFY_PAYMENTS',3);

//Delivery status based on courier services
define('DELIVERY_STATUS_UNKNOWN',0);
define('DELIVERY_STATUS_CONFIRMED',1);
define('DELIVERY_STATUS_IN_TRANSIT',2);  // ChinaPost = 10
define('DELIVERY_STATUS_OUT_FOR_DELIVERY',3);
define('DELIVERY_STATUS_DELIVERED',4);  // ChinaPost = 40
define('DELIVERY_STATUS_FAILURE',5);     // ChinaPost = 35 (Undelivered)
define('DELIVERY_STATUS_NOT_FOUND',6); // ChinaPost = 00
define('DELIVERY_STATUS_PICKUP',7);  // ChinaPost = 30
define('DELIVERY_STATUS_ALERT',8);  // ChinaPost = 50
define('DELIVERY_STATUS_EXPIRED',9); // ChinaPost = 20


//Order status flags in the system
define('NOTIFICATION_STATUS_NONE',0);                //There is no issue
define('NOTIFICATION_STATUS_RESOLVED',1);            //Previously there was an issue, now it is resolved
define('NOTIFICATION_STATUS_EXTENDED_NOT_FOUND',2);  //Courier perhaps lost the package
define('NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT',3); //Item is likely stuck in customs
define('NOTIFICATION_STATUS_CUSTOMER_PICKUP',4);     //Email customers to pick up their item at the post office
define('NOTIFICATION_STATUS_DELIVERY_FAILURE',5);    //Email customers to call their local post office
define('NOTIFICATION_STATUS_ALERT_CUSTOMS',6);       //Inform suppliers that items was likely rejected by customs


/**
 * Added by Rafal - 2018-03-20
 */
// Financial Status of Order
define('FINANCIAL_STATUS_NONE',0);                  //
define('FINANCIAL_STATUS_PENDING',1);               // The finances are pending. Payment might fail in this state.
define('FINANCIAL_STATUS_AUTHORIZED',2);            // The finances have been authorized.
define('FINANCIAL_STATUS_PARTIALLY_PAID',3);        // The finances have been partially paid.
define('FINANCIAL_STATUS_PAID',4);                  // The finances have been paid.
define('FINANCIAL_STATUS_PARTIALLY_REFUNDED',5);    // The finances have been partially refunded.
define('FINANCIAL_STATUS_REFUNDED',6);              // The finances have been refunded.
define('FINANCIAL_STATUS_VOIDED',7);                // The finances have been voided.


/** ------------------
 *  TRACKING COMPANIES
 *  ------------------ */

define('TRACKING_COMPANY_UNKNOWN',0);
define('TRACKING_COMPANY_USPS',1);
define('TRACKING_COMPANY_CHINA_POST',2);


/** ---------------
 *  CRAWLING ERRORS
 *  --------------- */
define('CRAWLER_ERROR_UNKNOWN',0);
define('CRAWLER_SUCCESS',1);
define('CRAWLER_ERROR_PROXY_FAILURE',2);
define('CRAWLER_ERROR_JSON_MALFORMED',3);
define('CRAWLER_ERROR_SERVER_TIMEOUT',4);
define('CRAWLER_ERROR_NO_JSON_RETURNED',5);
define('CRAWLER_ERROR_SERVER_RATE_LIMITED',6);

/** ---------
 *  REPORTING
 *  --------- */

define('SQL_REPORT_MAX_ROWS_DEFAULT',100);

/** -------------------
 *  FULFILLMENT VENDORS
 *  ------------------- */
define('VENDOR_APR',1);
define('VENDOR_CHENXIAOWEI',2);
define('VENDOR_DANI',3);
define('VENDOR_EZ',4);
define('VENDOR_ROBIN',5);
/** VENDOR ERIC - ADDED BY RAFAL 2018-03-09 */
define('VENDOR_ERIC',6);
/** VENDOR DROPIFIED - ADDED BY RAFAL 2018-03-13 */
define('VENDOR_DROPIFIED',7);

$vendor_array = Array(
    VENDOR_APR=>
        Array(
        'file'=>'vendor_apr',
        'name'=>'APR'),

    VENDOR_CHENXIAOWEI=> Array(
        'file'=>'vendor_chenxiaowei',
        'name'=>'ChenXiaoWei'),

    VENDOR_DANI=>Array(
        'file'=>'vendor_dani',
        'name'=>'Dani'),

    VENDOR_EZ=>Array(
        'file'=>'vendor_ez',
        'name'=>'EZ'),

    VENDOR_ROBIN=>Array(
        'file'=>'vendor_robin',
        'name'=>'Robin'),
    
    /** VENDOR ERIC - ADDED BY RAFAL 2018-03-09 */
    VENDOR_ERIC=>Array(
        'file'=>'vendor_eric',
        'name'=>'Eric'),
    
    /** VENDOR DROPIFIED - ADDED BY RAFAL 2018-03-13 */
    VENDOR_DROPIFIED=>Array(
        'file'=>'vendor_dropified',
        'name'=>'Dropified')
);



define('SCRIPT_LATEST_VERSION',"1.1");


/** =========================
 *  3RD PARTY API CREDENTIALS
 *  ========================= */

//API Key secrets for Shopify
define('API_KEY','########');
define('API_SECRET_KEY','########');

//API Key for 17track.net
define('TRACKER_API_TOKEN','########');

/** ================
 *  GOOGLE ANALYTICS
 *  ================ */

define('APP_GOOGLE_ANALYTICS_ID','UA-xxxxxxx-1');



/** ==========================
 *  Shopify API response codes
 *  ========================== */
$shopify_response_status_code_array = Array(
    '200' => 'OK',
    '201' => 'Created',
    '202' => 'Accepted',
    '303' => 'See Other',
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '402' => 'Payment Required',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '406' => 'Not Acceptable',
    '422' => 'Unprocessable Entity',
    '429' => 'Too Many Requests',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '503' => 'Service Unavailable',
    '504' => 'Gateway Timeout',
);