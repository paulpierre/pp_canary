/** ===================================================================
 *  wizard_orders_fulfillment_items_by_vendor_id_and_delivery_status.js
 *  ===================================================================
 *  ------
 *  ABOUT:
 *  ------
 *      The wizard dynamically generates reports on the user's request.
 *      It allows you to use reports predefined by the administrator or customized by the user.
 *
 *  -----------------
 *  MODULES IN ORDER:
 *  -----------------
 *  
 *      VARIABLES
 *      ---------
 *          • GLOBAL VARIABLES
 *      
 *      PREDEFINED SETTINGS
 *      -------------------
 *          • DEFAULT CONTROLS SETTINGS
 *          • TABLES AND COLUMNS SETTINGS
 *          • PREDEFINED REPORTS SETTINGS
 *  
 *      FUNCTIONS
 *      ---------
 *          • NAV MENU
 *          • EXECUTE GET
 *          • EXECUTE REQUESTS
 *          • TABLE
 *          • TABLE EXPORT
 *          • PORTLETS
 *          • DATERANGEPICKER
 *          • QUERY BUILDER
 *          • TEMPLATES
 *          • LISTENERS
 *          • DESTROYER
 *          • PREDEFINED REPORTS HELP GENERATOR
 *          • GET HTTP PREDEFINED REPORTS
 *          • METHODS
 *          • DOCUMENT READY AND RUN DEFAULT SETTINGS
 *
 *  ---------
 *  SETTINGS:
 *  ---------
 *
 *      Script uses external script files:
 *      • \assets\snippets\js\settings.js
 *      • \assets\snippets\js\navigation.js
 *
 */

/** +-----------------------------------------------------------+
 *  | +-------------------------------------------------------+ |
 *  | |                       VARIABLES                       | |
 *  | +-------------------------------------------------------+ |
 *  +-----------------------------------------------------------+ */
 


/** +------------------+
 *  | GLOBAL VARIABLES |
 *  +------------------+
 *
 *  DESCRIPTION:
 *  Variables used in Wizard's functions and scripts
 *
 */

/** --------
 *  NAV MENU
 *  -------- */
var thisSite = 'wizard_orders_fulfillment_items_by_vendor_id_and_delivery_status';


/** -------------------
 *  AJAX REQUEST RESULT
 *  ------------------- */
var ajax_response;

/** --------------
 *  CURRENT REPORT
 *  -------------- */
var current_report;

/** --------------
 *  TABLE INSTANCE
 *  -------------- */
var grid;

/** ---------------------------------------------
 *  TABLE VARIABLES USED DURING GENERATING REPORT
 *  --------------------------------------------- */
var request_builder_array = [];
var request_string = '';

var grid_data = new Array();
var grid_columns = new Array();
var grid_columns_render = new Array();

var dataView;

/** --------------------------------------------
 *  TABLE VARIABLES USED DURING EXPORTING REPORT
 *  -------------------------------------------- */
var grid_headers_export = [];
var grid_export = [];


/** -------------------
 *  DATERANGEPICKER
 *  ------------------- */

var picker = $('#m_dashboard_daterangepicker');
var start = moment().subtract(1,'day');
var end = moment();


var picker_start_string = 'T00:00:00.000Z';
var picker_end_string = 'T23:59:59.999Z';

var picker_selected_predefined_period_name;

var picker_selected_start_date;
var picker_selected_start_name;
var picker_selected_start_value;

var picker_selected_end_date;
var picker_selected_start_name;
var picker_selected_start_value;

var picker_core_date_start;
var picker_core_date_end;

/** ------------------------
 *  CONST NAMES AND STATUSES
 *  ------------------------ */
var DELIVERY_STATUS_LEGEND_UNKNOWN                      = 'UNKNOWN';            // 0    color:  secondary
var DELIVERY_STATUS_LEGEND_CONFIRMED                    = 'CONFIRMED';          // 1    color:  info
var DELIVERY_STATUS_LEGEND_INTRANSIT                    = 'IN TRANSIT';         // 2    color:  info
var DELIVERY_STATUS_LEGEND_OUTFORDELIVERY               = 'OUT FOR DELIVERY';   // 3    color:  warning
var DELIVERY_STATUS_LEGEND_DELIVERED                    = 'DELIVERED';          // 4    color:  success
var DELIVERY_STATUS_LEGEND_FAILURE                      = 'FAILURE';            // 5    color:  danger
var DELIVERY_STATUS_LEGEND_NOTFOUND                     = 'NOT FOUND';          // 6    color:  danger
var DELIVERY_STATUS_LEGEND_PICKUP                       = 'PICKUP';             // 7    color:  warning
var DELIVERY_STATUS_LEGEND_ALERT                        = 'ALERT';              // 8    color:  warning
var DELIVERY_STATUS_LEGEND_EXPIRED                      = 'EXPIRED';            // 9    color:  danger

var ALERT_NOTIFICATION_STATUS_NONE 			= 'NONE'; 		// 0    color:  secondary
var ALERT_NOTIFICATION_STATUS_RESOLVED 			= 'RESOLVED'; 		// 1    color:  success
var ALERT_NOTIFICATION_STATUS_EXTENDED_NOT_FOUND	= 'NOT_FOUND'; 		// 2    color:  warning
var ALERT_NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT	= 'IN_TRANSIT'; 	// 3    color:  info
var ALERT_NOTIFICATION_STATUS_CUSTOMER_PICKUP		= 'CUSTOMER_PICKUP'; 	// 4    color:  warning
var ALERT_NOTIFICATION_STATUS_DELIVERY_FAILURE		= 'DELIVERY_FAILURE'; 	// 5    color:  danger
var ALERT_NOTIFICATION_STATUS_ALERT_CUSTOMS		= 'ALERT_CUSTOMS'; 	// 6    color:  danger

var REFUNDED_STATUS_NO_REFUND                           = 'NO REFUND';          // 0    color:  success
var REFUNDED_STATUS_FULL_REFUND                         = 'FULL REFUND';        // 1    color:  warning
var REFUNDED_STATUS_PARTIAL_REFUND                      = 'PARTIAL REFUND';     // 2    color:  danger

var FINANCIAL_STATUS_0 = 'NONE';
var FINANCIAL_STATUS_1 = 'PENDING';
var FINANCIAL_STATUS_2 = 'AUTHORIZED';
var FINANCIAL_STATUS_3 = 'PARTIALLY PAID';
var FINANCIAL_STATUS_4 = 'PAID';
var FINANCIAL_STATUS_5 = 'PARTIALLY REFUNDED';
var FINANCIAL_STATUS_6 = 'REFUNDED';
var FINANCIAL_STATUS_7 = 'VOIDED';

var VENDOR_ID_0                                         = 'Unknown';
var VENDOR_ID_1                                         = 'APR';
var VENDOR_ID_2                                         = 'ChenXiaoWei';
var VENDOR_ID_3                                         = 'Dani';
var VENDOR_ID_4                                         = 'EZ ';
var VENDOR_ID_5                                         = 'Robin';
var VENDOR_ID_6                                         = 'Eric';
var VENDOR_ID_7                                         = 'Dropified';

var TRACKING_REFRESH_STATUS_0                           = 'NO TRACKING NUMBER';
var TRACKING_REFRESH_STATUS_1                           = 'DURING REQUESTING';
var TRACKING_REFRESH_STATUS_2                           = 'STATUS THE SAME';
var TRACKING_REFRESH_STATUS_3                           = 'STATUS CHANGED';
var TRACKING_REFRESH_STATUS_4                           = 'NOT REFRESHED';

const_statuses = {};
const_statuses = {
                        order_delivery_status: {
                                                    0: DELIVERY_STATUS_LEGEND_UNKNOWN,
                                                    1: DELIVERY_STATUS_LEGEND_CONFIRMED,
                                                    2: DELIVERY_STATUS_LEGEND_INTRANSIT,
                                                    3: DELIVERY_STATUS_LEGEND_OUTFORDELIVERY,
                                                    4: DELIVERY_STATUS_LEGEND_DELIVERED,
                                                    5: DELIVERY_STATUS_LEGEND_FAILURE,
                                                    6: DELIVERY_STATUS_LEGEND_NOTFOUND,
                                                    7: DELIVERY_STATUS_LEGEND_PICKUP,
                                                    8: DELIVERY_STATUS_LEGEND_ALERT,
                                                    9: DELIVERY_STATUS_LEGEND_EXPIRED,
                                                },
                        order_alert_status: {
                                                    0: ALERT_NOTIFICATION_STATUS_NONE,
                                                    1: ALERT_NOTIFICATION_STATUS_RESOLVED,
                                                    2: ALERT_NOTIFICATION_STATUS_EXTENDED_NOT_FOUND,
                                                    3: ALERT_NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT,
                                                    4: ALERT_NOTIFICATION_STATUS_CUSTOMER_PICKUP,
                                                    5: ALERT_NOTIFICATION_STATUS_DELIVERY_FAILURE,
                                                    6: ALERT_NOTIFICATION_STATUS_ALERT_CUSTOMS,
                                            },
                        item_is_refunded:   {
                                                    0: REFUNDED_STATUS_NO_REFUND,
                                                    1: REFUNDED_STATUS_FULL_REFUND,
                                                    2: REFUNDED_STATUS_PARTIAL_REFUND,
                                            },
                        vendor_id: {
                                                    0: VENDOR_ID_0,
                                                    1: VENDOR_ID_1,
                                                    2: VENDOR_ID_2,
                                                    3: VENDOR_ID_3,
                                                    4: VENDOR_ID_4,
                                                    5: VENDOR_ID_5,
                                                    6: VENDOR_ID_6,
                                                    7: VENDOR_ID_7,
                                    },
                        tracking_status_receiving:{
                                                    0: TRACKING_REFRESH_STATUS_0,
                                                    1: TRACKING_REFRESH_STATUS_1,
                                                    2: TRACKING_REFRESH_STATUS_2,
                                                    3: TRACKING_REFRESH_STATUS_3,
                                                    4: TRACKING_REFRESH_STATUS_4,
                                                },
                        order_financial_status: {
                                                    0: FINANCIAL_STATUS_0,
                                                    1: FINANCIAL_STATUS_1,
                                                    2: FINANCIAL_STATUS_2,
                                                    3: FINANCIAL_STATUS_3,
                                                    4: FINANCIAL_STATUS_4,
                                                    5: FINANCIAL_STATUS_5,
                                                    6: FINANCIAL_STATUS_6,
                                                    7: FINANCIAL_STATUS_7,
                                            }
                    }

/** -----------------------------
 *  HTML ELEMENTS USED IN REPORTS
 *  ----------------------------- */
var bage_small              = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR}">{TEXT}</span>';
var bage_dot                = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR} m-badge--dot"></span>';
var bage_wide               = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR} m-badge--wide">{TEXT}</span>';
var bage_rounded            = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR} m-badge--wide m-badge--rounded">{TEXT}</span>';
var bage_small_rounded      = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR}">{VALUE2}</span>' + ' - ' + '<span data-value="{VALUE3}" class="m-badge m-badge--secondary m-badge--wide m-badge--rounded">{TEXT}</span>';

/** ----------------------------
 *  USED DURING RECEIVING REPORT
 *  ---------------------------- */
var limit = 2000;
var number_of_records = 0;
var total_number_of_records = 0;
var page = 0;

/** ---------------------------------------------
 *  USED DURING DISPLAY CURENT SQL-S AND REQUESTS
 *  --------------------------------------------- */
var sql = new Array();
var request = new Array();


/** +---------------------------------------------------------------------+
 *  | +-----------------------------------------------------------------+ |
 *  | |                       PREDEFINED SETTINGS                       | |
 *  | +-----------------------------------------------------------------+ |
 *  +---------------------------------------------------------------------+ */


/** +---------------------------+
 *  | DEFAULT CONTROLS SETTINGS |
 *  +---------------------------+
 *
 *  DESCRIPTION:
 *  Set checkboxes to default values
 *
 */

/** --------
 *  CRITERIA
 *  -------- */
var default_checkbox_criteria = [
                                    ['criteria_id','1',     'criteria',     'vendor_id',                                                true],
                                    ['criteria_id','2',     'criteria',     'vendor_id',                                                true],
                                    ['criteria_id','3',     'criteria',     'vendor_id',                                                true],
                                    ['criteria_id','4',     'criteria',     'vendor_id',                                                true],
                                    ['criteria_id','5',     'criteria',     'vendor_id',                                                true],
                                    ['criteria_id','6',     'criteria',     'vendor_id',                                                true],
                                    ['criteria_id','0',     'criteria',     'vendor_id',                                                true],

                                    ['criteria_id','0',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','2',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','3',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','4',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','5',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','6',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','7',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','8',     'criteria',     'order_delivery_status',                                    true],
                                    ['criteria_id','9',     'criteria',     'order_delivery_status',                                    true],

                                    ['criteria_id','0',     'criteria',     'order_is_refunded',                                        true],
                                    ['criteria_id','1',     'criteria',     'order_is_refunded',                                        true],
                                    ['criteria_id','2',     'criteria',     'order_is_refunded',                                        true],

                                    ['criteria_id','0',     'criteria',     'order_alert_status',                                       true],
                                    ['criteria_id','1',     'criteria',     'order_alert_status',                                       true],
                                    ['criteria_id','2',     'criteria',     'order_alert_status',                                       true],
                                    ['criteria_id','3',     'criteria',     'order_alert_status',                                       true],
                                    ['criteria_id','4',     'criteria',     'order_alert_status',                                       true],
                                    ['criteria_id','5',     'criteria',     'order_alert_status',                                       true],
                                    ['criteria_id','6',     'criteria',     'order_alert_status',                                       true],
							
                        ];

/** ------
 *  TABLES
 *  ------ */
var default_checkbox_tables = [							
                                    ['table',   'orders',      'column',    'order_id',                                                 true],
                                    ['table',   'orders',      'column',    'order_receipt_id',                                         true],
                                    ['table',   'orders',      'column',    'order_shopify_id',                                         true],
                                    ['table',   'orders',      'column',    'order_fulfillment_status',                                 true],
                                    ['table',   'orders',      'column',    'order_delivery_status',                                    true],
                                    ['table',   'orders',      'column',    'order_alert_status',                                       true],
                                    ['table',   'orders',      'column',    'order_is_ocu',                                             true],
                                    ['table',   'orders',      'column',    'order_is_refunded',                                        true],
                                    ['table',   'orders',      'column',    'order_total_cost',                                         true],
                                    ['table',   'orders',      'column',    'order_tags',                                       false],
                                    ['table',   'orders',      'column',    'order_gateway',                                    false],
                                    ['table',   'orders',      'column',    'order_customer_email',                                     true],
                                    ['table',   'orders',      'column',    'order_customer_fn',                                        true],
                                    ['table',   'orders',      'column',    'order_customer_ln',                                        true],
                                    ['table',   'orders',      'column',    'order_customer_address1',                          false],
                                    ['table',   'orders',      'column',    'order_customer_address2',                          false],
                                    ['table',   'orders',      'column',    'order_customer_city',                              false],
                                    ['table',   'orders',      'column',    'order_customer_province',                          false],
                                    ['table',   'orders',      'column',    'order_customer_zip',                               false],
                                    ['table',   'orders',      'column',    'order_currency',                                   false],
                                    ['table',   'orders',      'column',    'order_customer_country',                           false],
                                    ['table',   'orders',      'column',    'order_customer_phone',                             false],
                                    ['table',   'orders',      'column',    'order_topen',                                              true],
                                    ['table',   'orders',      'column',    'order_tclose',                                             true],
                                    ['table',   'orders',      'column',    'order_tmodified',                                  false],
                                    ['table',   'orders',      'column',    'order_tcreate',                                    false],


                                    ['table',   'fulfillments', 'column',   'fulfillment_id',                                           true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_shopify_id',                          false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_shipment_status',                     false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_delivery_status',                              true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_is_tracking',                                  true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_alert_status',                                 true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_number',                              true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_vendor_id',                           false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_number_tcreate',             false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_company',                    false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_url',                        false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_last_status_text',                    true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_last_date',                           true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_country_from',                        true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_country_to',                          true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_carrier_from',               false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tracking_carrier_to',                 false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_delivered_tcreate',            false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_confirmed_tcreate',            false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_in_transit_tcreate',           false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_out_for_delivery_tcreate',     false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_failure_tcreate',              false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_not_found_tcreate',            false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_customer_pickup_tcreate',      false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_alert_tcreate',                false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_status_expired_tcreate',              false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_topen',                               false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tcheck',                                       true],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tmodified',                           false],
                                    ['table',   'fulfillments', 'column',   'fulfillment_tcreate',                             false],

                                    ['table',   'items',        'column',    'item_id',                                                 true],
                                    ['table',   'items',        'column',    'item_shopify_id',                                false],
                                    ['table',   'items',        'column',    'item_quantity',                                  false],
                                    ['table',   'items',        'column',    'item_sku',                                       false],
                                    ['table',   'items',        'column',    'item_shopify_product_id',                        false],
                                    ['table',   'items',        'column',    'item_shopify_variant_id',                        false],
                                    ['table',   'items',        'column',    'item_name',                                               true],
                                    ['table',   'items',        'column',    'item_price',                                     false],
                                    ['table',   'items',        'column',    'item_is_fulfilled',                              false],
                                    ['table',   'items',        'column',    'item_is_refunded',                               false],
                                    ['table',   'items',        'column',    'item_refund_tcreate',                            false],
                                    ['table',   'items',        'column',    'item_tmodified',                                 false],
                                    ['table',   'items',        'column',    'item_tcreate',                                   false],
                                    
                                    ['table',   'vendor_tracking',  'column',   'tracking_number',                                      true],
                                    ['table',   'vendor_tracking',  'column',   'vendor_id',                                            true],
                                    ['table',   'vendor_tracking',  'column',   'order_receipt_id',                             false],
                                    ['table',   'vendor_tracking',  'column',   'tracking_status',                              false],
                                    ['table',   'vendor_tracking',  'column',   'tracking_tmodified',                           false],
                                    ['table',   'vendor_tracking',  'column',   'tracking_tcreate',                             false],
							
                        ];


/** +-----------------------------+
 *  | TABLES AND COLUMNS SETTINGS |
 *  +-----------------------------+
 *
 *  DESCRIPTION:
 *  Settings and const values for each table and column used in script
 *  
 *  SRTUCTURE:
 *  - column_name                   / name of column in database
 *              | - name            / name is used in table headers
 *              | - description     / description used in help popup
 *              | - values          / in case of predefined values different than values in database
 *              | - style           / in case of using html code in table cell
 *              | - label           / descriptions of values used in help elements
 *
 */
var columns_settings = {
                        order_id: {
                                                                    name:   'Order Id',
                                                                    description: 'This is the table index ID for the row',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_receipt_id: {
                                                                    name:   'Order Receipt Id',
                                                                    description: 'This is the user-friendly order identifier in shopify also known as receipt ex.: #OMGT229490',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_shopify_id: {
                                                                    name:   'Order Shopify Id',
                                                                    description: 'This is the numeric identifier for the order within shopify, when you look at an order in the admin, you well see this number in the URL bar',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_fulfillment_status: {
                                                                    name:   'Order Fulfillment Status',
                                                                    description: 'whether an order has been fulfilled, e.g. a corresponding fulfillment row has been added',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_delivery_status: {
                                                                    name:   'Order Delivery Status',
                                                                    description: 'The deliver status of a particular order, based on courier services',
                                                                    values: {
                                                                                0: DELIVERY_STATUS_LEGEND_UNKNOWN,
                                                                                1: DELIVERY_STATUS_LEGEND_CONFIRMED,
                                                                                2: DELIVERY_STATUS_LEGEND_INTRANSIT,
                                                                                3: DELIVERY_STATUS_LEGEND_OUTFORDELIVERY,
                                                                                4: DELIVERY_STATUS_LEGEND_DELIVERED,
                                                                                5: DELIVERY_STATUS_LEGEND_FAILURE,
                                                                                6: DELIVERY_STATUS_LEGEND_NOTFOUND,
                                                                                7: DELIVERY_STATUS_LEGEND_PICKUP,
                                                                                8: DELIVERY_STATUS_LEGEND_ALERT,
                                                                                9: DELIVERY_STATUS_LEGEND_EXPIRED,
                                                                            },
                                                                    style:  {
                                                                                0: [bage_wide,"secondary"],
                                                                                1: [bage_wide,"info"],
                                                                                2: [bage_wide,"info"],
                                                                                3: [bage_wide,"warning"],
                                                                                4: [bage_wide,"success"],
                                                                                5: [bage_wide,"danger"],
                                                                                6: [bage_wide,"danger"],
                                                                                7: [bage_wide,"warning"],
                                                                                8: [bage_wide,"warning"],
                                                                                9: [bage_wide,"danger"],
                                                                            },
                                                                    label:  {
                                                                                0: 'Unknown',
                                                                                1: 'Confirmed',
                                                                                2: 'In Transit',
                                                                                3: 'Out Of Delivery',
                                                                                4: 'Delivered',
                                                                                5: 'Failure',
                                                                                6: 'Not Found',
                                                                                7: 'Pick Up',
                                                                                8: 'Alert',
                                                                                9: 'Expired',
                                                                            },
                        },
                        order_alert_status: {
                                                                    name:   'Order Alert Status',
                                                                    description: 'When we interpret an delivery status for an order/fulfillment, we check conditions to see whether we should flag this particular order',
                                                                    values: {
                                                                                0: ALERT_NOTIFICATION_STATUS_NONE,
                                                                                1: ALERT_NOTIFICATION_STATUS_RESOLVED,
                                                                                2: ALERT_NOTIFICATION_STATUS_EXTENDED_NOT_FOUND,
                                                                                3: ALERT_NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT,
                                                                                4: ALERT_NOTIFICATION_STATUS_CUSTOMER_PICKUP,
                                                                                5: ALERT_NOTIFICATION_STATUS_DELIVERY_FAILURE,
                                                                                6: ALERT_NOTIFICATION_STATUS_ALERT_CUSTOMS,
                                                                            },
                                                                    style:  {
                                                                                0: [bage_wide,"secondary"],
                                                                                1: [bage_wide,"success"],
                                                                                2: [bage_wide,"warning"],
                                                                                3: [bage_wide,"info"],
                                                                                4: [bage_wide,"warning"],
                                                                                5: [bage_wide,"danger"],
                                                                                6: [bage_wide,"danger"],
                                                                            },
                                                                    label:  {
                                                                                0: 'None',
                                                                                1: 'Resolved',
                                                                                2: 'Extended Not Found',
                                                                                3: 'Extended In Transit',
                                                                                4: 'Customer Pickup',
                                                                                5: 'Delivery Failure',
                                                                                6: 'Alert Customs',
                                                                            },
                                                                    help_t: '`order_alert_status` in `orders`.',
                                                                    help_c:  {
                                                                                0: 'There is no issue',
                                                                                1: 'Previously there was an issue, now it is resolved',
                                                                                2: 'Courier perhaps lost the package',
                                                                                3: 'Item is likely stuck in customs',
                                                                                4: 'Email customers to pick up their item at the post office',
                                                                                5: 'Email customers to call their local post office',
                                                                                6: 'Inform suppliers that items was likely rejected by customs',
                                                                            },
                        },
                        order_is_ocu: {
                                                                    name:   'Order Is Ocu',
                                                                    description: 'This is not important, but this keeps track if a user is (1) or not (0) an order from a Shopify app called one click upsell',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_is_refunded: {
                                                                    name:   'Order Is Refunded',
                                                                    description: 'Whether an order is refunded, we get this information from Shopify',
                                                                    values: {
                                                                                0: REFUNDED_STATUS_NO_REFUND,
                                                                                1: REFUNDED_STATUS_FULL_REFUND,
                                                                                2: REFUNDED_STATUS_PARTIAL_REFUND,
                                                                            },
                                                                    style:  {
                                                                                0: [bage_wide,"secondary"],
                                                                                1: [bage_wide,"danger"],
                                                                                2: [bage_wide,"warning"],
                                                                            },
                                                                    label:  {
                                                                                0: 'No Refund ',
                                                                                1: 'Full Refund',
                                                                                2: 'Partial Refund',
                                                                            },
                        },
                        order_total_cost: {
                                                                    name:   'Order Total Cost',
                                                                    description: 'The cost of all the items put together',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_tags: {
                                                                    name:   'Order Tags',
                                                                    description: 'Tags for this order, ignore not necessary for us to use for now',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_gateway: {
                                                                    name:   'Order Gateway',
                                                                    description: 'The payment gateway that was used',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_email: {
                                                                    name:   'Order Customer Email',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_fn: {
                                                                    name:   'Order Customer Fn',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_ln: {
                                                                    name:   'Order Customer Ln',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_address1: {
                                                                    name:   'Order Customer Address1',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_address2: {
                                                                    name:   'Order Customer Address2',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_city: {
                                                                    name:   'Order Customer City',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_province: {
                                                                    name:   'Order Customer Province',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_zip: {
                                                                    name:   'Order Customer Zip',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_currency: {
                                                                    name:   'Order Currency',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_country: {
                                                                    name:   'Order Customer Country',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_customer_phone: {
                                                                    name:   'Order Customer Phone',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_topen: {
                                                                    name:   'Order Time Open',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_tclose: {
                                                                    name:   'Order Time Close',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_tmodified: {
                                                                    name:   'Order Time Modified',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        order_tcreate: {
                                                                    name:   'Order Time Create',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        
                        item_id: {
                                                                    name:   'Item Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_shopify_id: {
                                                                    name:   'Item Shopify Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_quantity: {
                                                                    name:   'Item Quantity',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_sku: {
                                                                    name:   'Item Sku',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_shopify_product_id: {
                                                                    name:   'Item Shopify Product Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_shopify_variant_id: {
                                                                    name:   'Item Shopify Variant Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_name: {
                                                                    name:   'Item Name',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_price: {
                                                                    name:   'Item Price',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_is_fulfilled: {
                                                                    name:   'Item Is Fulfilled',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_is_refunded: {
                                                                    name:   'Item Is Refunded',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_refund_tcreate: {
                                                                    name:   'Item Refund Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_tmodified: {
                                                                    name:   'Item Time Modified',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        item_tcreate: {
                                                                    name:   'Item Time Create',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        
                        fulfillment_id: {
                                                                    name:   'Fulfillment Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_shopify_id: {
                                                                    name:   'Fulfillment Shopify Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_shipment_status: {
                                                                    name:   'Fulfillment Shipment Status',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_delivery_status: {
                                                                    name:   'Fulfillment Delivery Status',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_is_tracking: {
                                                                    name:   'Fulfillment Is Tracking',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_alert_status: {
                                                                    name:   'Fulfillment Alert Status',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_vendor_id: {
                                                                    name:   'Fulfillment Vendor Id',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_number: {
                                                                    name:   'Fulfillment Tracking Number',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_number_tcreate: {
                                                                    name:   'Fulfillment Tracking Number Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_company: {
                                                                    name:   'Fulfillment Tracking Company',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_url: {
                                                                    name:   'Fulfillment Tracking Url',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_last_status_text: {
                                                                    name:   'Fulfillment Tracking Last Status Text',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_last_date: {
                                                                    name:   'Fulfillment Tracking Last Date',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_country_from: {
                                                                    name:   'Fulfillment Tracking Country From',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_country_to: {
                                                                    name:   'Fulfillment Tracking Country To',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_carrier_from: {
                                                                    name:   'Fulfillment Tracking Carrier From',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tracking_carrier_to: {
                                                                    name:   'Fulfillment Tracking Carrier To',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_delivered_tcreate: {
                                                                    name:   'Fulfillment Status Delivered Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_confirmed_tcreate: {
                                                                    name:   'Fulfillment Status Confirmed Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_in_transit_tcreate: {
                                                                    name:   'Fulfillment Status In Transit Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_out_for_delivery_tcreate: {
                                                                    name:   'Fulfillment Status Out For Delivery Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_failure_tcreate: {
                                                                    name:   'Fulfillment Status Failure Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_not_found_tcreate: {
                                                                    name:   'Fulfillment Status Not Found Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_customer_pickup_tcreate: {
                                                                    name:   'Fulfillment Status Customer Pickup Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_alert_tcreate: {
                                                                    name:   'Fulfillment Status Alert Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_status_expired_tcreate: {
                                                                    name:   'Fulfillment Status Expired Tcreate',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_topen: {
                                                                    name:   'Fulfillment Time Open',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tcheck: {
                                                                    name:   'Fulfillment Time Check',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tmodified: {
                                                                    name:   'Fulfillment Time Modified',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        fulfillment_tcreate: {
                                                                    name:   'Fulfillment Time Create',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        
                        vendor_id: {
                                                                    name:   'Vendor Tracking Vendor Id',
                                                                    description: '',
                                                                    values: {
                                                                                0: VENDOR_ID_0,
                                                                                1: VENDOR_ID_1,
                                                                                2: VENDOR_ID_2,
                                                                                3: VENDOR_ID_3,
                                                                                4: VENDOR_ID_4,
                                                                                5: VENDOR_ID_5,
                                                                                6: VENDOR_ID_6,
                                                                                7: VENDOR_ID_7,
                                                                            },
                                                                    style:  {
                                                                                0: [bage_small_rounded,"danger"],
                                                                                1: [bage_small_rounded,"info"],
                                                                                2: [bage_small_rounded,"info"],
                                                                                3: [bage_small_rounded,"info"],
                                                                                4: [bage_small_rounded,"info"],
                                                                                5: [bage_small_rounded,"info"],
                                                                                6: [bage_small_rounded,"info"],
                                                                            },
                                                                    label:  {
                                                                                0: 'Unknown',
                                                                                1: 'APR',
                                                                                2: 'ChenXiaoWei',
                                                                                3: 'Dani',
                                                                                4: 'EZ',
                                                                                5: 'Robin',
                                                                                6: 'Eric',
                                                                                7: 'Dropified',
                                                                            },
                                                                    help_t: '`vendor_id` in `vendor_tracking`.',
                                                                    help_c:  {
                                                                                0: 'Unknown - if no data',
                                                                                1: 'APR - id = 1',
                                                                                2: 'ChenXiaoWei - id = 2',
                                                                                3: 'Dani - id = 3',
                                                                                4: 'EZ - id = 4',
                                                                                5: 'Robin - id = 5',
                                                                                6: 'Eric - id = 6',
                                                                                7: 'Dropified - id = 7',
                                                                                8: 'Unknown - id = 8',
                                                                            },
                                                                            
                        },
                        tracking_number: {
                                                                    name:   'Tracking Number',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        tracking_status: {
                                                                    name:   'Tracking Status',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        tracking_tmodified: {
                                                                    name:   'Tracking Time Modified',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                        tracking_tcreate: {
                                                                    name:   'Tracking Time Create',
                                                                    description: '',
                                                                    values: [],
                                                                    style:  [],
                        },
                    };

/** +-----------------------------+
 *  | PREDEFINED REPORTS SETTINGS |
 *  +-----------------------------+
 *
 *  DESCRIPTION:
 *  Here are saved settings for predefined reports
 *  
 *  SRTUCTURE:
 *  report_name                                 / raport name
 *              | - tables_columns              / tables and columns used for generating report results
 *              | - vendor_id                   / condition
 *              | - order_delivery_status       / condition
 *              | - order_is_refunded           / condition
 *              | - order_alert_status          / condition
 *              | - date_start                  / start date of report as variable or function
 *              | - date_end                    / end date of report as variable or function
 *              | - columns_order               / order of columns in report
 *              
 *              
 *  report_name_add                             / additional informations for report what isn't generated automatically
 *                 | - date_start               / start date name of variable or function
 *                 | - date_end                 / end date name of variable or function
 *                 | - description              / description is used in help tip
 *
 *
 */

/** -------------------------------
 *  DYNAMIC VARIABLES FOR SLICKGRID
 *  ------------------------------- */
var warnings_ids = {};
var warning_current_id;

function avgTotalsFormatter(totals, columnDef) {
    var val = totals.avg && totals.avg[columnDef.field];
    if (val != null) {
        return "avg: " + Math.round(val) + "%";
    }
    return "";
}

function sumTotalsFormatter(totals, columnDef) {
    var val = totals.sum && totals.sum[columnDef.field];
    if (val != null) {
        return "total: " + ((Math.round(parseFloat(val)*100)/100));
    }
    return "";
}

function countTotalsFormatter(totals, columnDef) {
  var val = totals.count && totals.count[columnDef.field];
  if (val != null) {
        return "total: " + ((Math.round(parseFloat(val)*100)/100));
  }
  return "";
}

function checkItemsCountFraudAlert(totals, columnDef) {
  var val = totals.count && totals.count[columnDef.field];
  if (val != null) {
        var value = ((Math.round(parseFloat(val)*100)/100));
        var res;
        if(value>3){
            warnings_ids[warning_current_id]=warning_current_id;
            res = '<span data-value="' + value + '" class="m-badge m-badge--danger m-badge--wide">ITEMS:'+value+'</span>';
        } else {
            res = '';
        }
        return res;
  }
  return "";
}

function checkItemsvaluesAndGateways(totals, columnDef) {
  var val = totals.sum_vga && totals.sum_vga[columnDef.field];
  if (val != null) {
        var value = ((Math.round(parseFloat(val)*100)/100));
        var res;
        if(value>0){
            warnings_ids[warning_current_id]=warning_current_id;
            res = '<span data-value="' + value + '" class="m-badge m-badge--danger m-badge--wide">'+value+'</span>';
        } else {
            res = value;
        }
        return res;
  }
  return "";
}

function compareStrAlert(totals, columnDef) {
  var val = totals.comp && totals.comp[columnDef.field];
  if (val != null) {
        var value = ((Math.round(parseFloat(val)*100)/100));        
        var res;
        if(value>0){
            res = '<span data-value="' + value + '" class="m-badge m-badge--danger m-badge--wide">STRING COMP.</span>';
        } else {
            res = '';
        }
        return res;
  }  
  return "";
}

function compareDateAlert(totals, columnDef) {
  var val = totals.comp && totals.comp[columnDef.field];
  if (val != null) {
        var value = ((Math.round(parseFloat(val)*100)/100));        
        var res;
        if(value>0){
            res = '<span data-value="' + value + '" class="m-badge m-badge--danger m-badge--wide">DATE COMP.</span>';
        } else {
            res = '';
        }
        return res;
  }  
  return "";
}

function checkPartialAlert(totals, columnDef) {
  
  var val = totals.check && totals.check[columnDef.field];
  if (val != null) {
        var value = ((Math.round(parseFloat(val)*100)/100));        
        var res;
        
        if(value == 1){
            res = '<span data-value="' + value + '" class="m-badge m-badge--warning m-badge--wide">PARTIAL</span>';
        } else if(value == 2){
            res = '<span data-value="' + value + '" class="m-badge m-badge--danger m-badge--wide">ALL</span>';
        } else {
            res = '';
        }
        return res;
  }  
  return "";
}

var checkboxSelector = new Slick.CheckboxSelectColumn({
  cssClass: "slick-cell-checkboxsel"
});

/** --------
 *  REPORT 1
 *  -------- */
var predefined_report_fraud_detection_order = {
                            "tables_columns": [
						"orders.order_id",
						"orders.order_receipt_id",
						"orders.order_delivery_status",
						"orders.order_gateway",
                                                "orders.order_customer_email",
						"orders.order_customer_fn",
						"orders.order_customer_ln",
						"orders.order_customer_address1",
						"orders.order_customer_address2",
						"orders.order_customer_city",
						"orders.order_customer_province",
						"orders.order_customer_zip",
						"orders.order_customer_country",,
						"orders.order_customer_billing_fn",
						"orders.order_customer_billing_ln",
						"orders.order_customer_billing_address1",
						"orders.order_customer_billing_address2",
						"orders.order_customer_billing_city",
						"orders.order_customer_billing_province",
						"orders.order_customer_billing_zip",
						"orders.order_customer_billing_country",
						"orders.order_customer_phone",
						"orders.order_tcreate",
						"items.item_is_refunded",
						"items.item_quantity",
						"items.item_price",
						"items.item_sku",
						"items.item_shopify_variant_id",
						"items.item_name",
                                                "fulfillments.fulfillment_id",
                                                "fulfillments.fulfillment_tracking_number",
                                                "fulfillments.fulfillment_tcheck",
						"orders.order_financial_status",
						"orders.order_topen",
						"orders.order_tcancel",
						"orders.order_tclose",
                                            ],
                            "vendor_id": [
						1,
						2,
						3,
						4,
						5,
						6,
                                                7,
						0
                                            ],
                            "order_delivery_status": [
								0,
								2,
								3,
								4,
								5,
								6,
								7,
								8,
								9
                                                    ],
                            "order_is_refunded": [
								0,
								1,
								2
                                                    ],
                            "order_alert_status": [
								0,
								1,
								2,
								3,
								4,
								5,
								6
                                                    ],
                            "date_start":   '',
                            "date_end":     '',
                            "columns_order": [
                                                checkboxSelector.getColumnDefinition(),
						{id:"order_id",                         name:"Order Id",                field:"order_id",                           cssClass:"cell-title",          sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},
						{id:"order_receipt_id",                 name:"Receipt Id",              field:"order_receipt_id",                   cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"fulfillment_tracking_number",      name:"Tracking Number",         field:"fulfillment_tracking_number",        cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_status",                     name:"Order Status",            field:"order_status",                       cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.OrderStatus},	
                                                {id:"order_financial_status",           name:"Financial Status",        field:"order_financial_status",             cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.FinancialStatus},
                                                {id:"order_tcreate",                    name:"Order Tcreate",           field:"order_tcreate",                      cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_delivery_status",            name:"Delivery Status",         field:"order_delivery_status",              cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.DeliveryStatus},	
                                                {id:"item_is_refunded",                 name:"Is Refunded",             field:"item_is_refunded",                   cssClass:"cell-align-center",   sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.IsItemRefunded},	
                                                {id:"item_name",                        name:"Item Name",               field:"item_name",                          cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"item_shopify_variant_id",          name:"Item Variant Id",         field:"item_shopify_variant_id",            cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"item_sku",                         name:"Sku",                     field:"item_sku",                           cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_gateway",                    name:"Payment Gateway",         field:"order_gateway",                      cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.PaymentGateway},		
                                                {id:"item_quantity",                    name:"Quantity",                field:"item_quantity",                      cssClass:"cell-align-right",    sortable:true,  width:120,  resizable:true,  formatter:Slick.Formatters.Value,          groupTotalsFormatter: checkItemsCountFraudAlert},		
                                                {id:"item_price",                       name:"Price",                   field:"item_price",                         cssClass:"cell-align-right",    sortable:true,  width:120,  resizable:true,  formatter:Slick.Formatters.Value},		
                                                {id:"item_value",                       name:"Value",                   field:"item_value",                         cssClass:"cell-align-right",    sortable:true,  width:120,  resizable:true,  formatter:Slick.Formatters.ItemValue,      groupTotalsFormatter: checkItemsvaluesAndGateways},
                                                {id:"order_customer_fn",                name:"Order: First Name",       field:"order_customer_fn",                  cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},		
                                                {id:"order_customer_ln",                name:"Order: Last Name",        field:"order_customer_ln",                  cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},
                                                {id:"order_customer_address1",          name:"Order: Address1",         field:"order_customer_address1",            cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},	
                                                {id:"order_customer_address2",          name:"Order: Address2",         field:"order_customer_address2",            cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},
                                                {id:"order_customer_province",          name:"Order: Province",         field:"order_customer_province",            cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},
                                                {id:"order_customer_city",              name:"Order: City",             field:"order_customer_city",                cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},
                                                {id:"order_customer_country",           name:"Order: Country",          field:"order_customer_country",             cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},
                                                {id:"order_customer_zip",               name:"Order: Zip",              field:"order_customer_zip",                 cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},
                                                {id:"order_customer_billing_fn",        name:"Billing: First Name",     field:"order_customer_billing_fn",          cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_customer_billing_ln",        name:"Billing: Last Name",      field:"order_customer_billing_ln",          cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_billing_address1",  name:"Billing: Address1",       field:"order_customer_billing_address1",    cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_billing_address2",  name:"Billing: Address2",       field:"order_customer_billing_address2",    cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_billing_province",  name:"Billing: Province",       field:"order_customer_billing_province",    cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_billing_city",      name:"Billing: City",           field:"order_customer_billing_city",        cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_billing_country",   name:"Billing: Country",        field:"order_customer_billing_country",     cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_billing_zip",       name:"Billing: Zip",            field:"order_customer_billing_zip",         cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_phone",             name:"Customer Phone",          field:"order_customer_phone",               cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_email",             name:"Customer Email",          field:"order_customer_email",               cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"fulfillment_id",                   name:"Fulfillment Id",          field:"fulfillment_id",                     cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"fulfillment_tcheck",               name:"Last Check Time",         field:"fulfillment_tcheck",                 cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_topen",                      name:"Order Open Time",         field:"order_topen",                        cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_tcancel",                    name:"Order Cancel Time",       field:"order_tcancel",                      cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_tclose",                     name:"Order Close Time",        field:"order_tclose",                       cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                            ]
                                    };
var predefined_report_fraud_detection_order_add = {
                                    date_start: 'CUSTOM',
                                    date_end: 'CUSTOM',
                                    description: 'This is a \'Fraud Detection Order\' report.'
                            };

/** --------
 *  REPORT 2
 *  -------- */
var predefined_report_bulk_order = {
                            "tables_columns": [
						"orders.order_id",
						"orders.order_delivery_status",
						"orders.order_is_refunded",
						"orders.order_customer_fn",
						"orders.order_customer_ln",
						"orders.order_customer_address1",
						"orders.order_customer_address2",
						"orders.order_customer_city",
						"orders.order_customer_province",
						"orders.order_customer_zip",
						"orders.order_customer_country",
						"orders.order_customer_phone",
						"orders.order_tcreate",
						"items.item_quantity",
						"items.item_sku",
						"items.item_shopify_variant_id",
						"items.item_name",
						"items.item_is_refunded",
                                                "vendor_tracking.tracking_number",
						"orders.order_financial_status",
						"orders.order_topen",
						"orders.order_tcancel",
						"orders.order_tclose",
                                            ],
                            "vendor_id": [
						1,
						2,
						3,
						4,
						5,
						6,
                                                7,
						0
                                            ],
                            "order_delivery_status": [
								0,
								2,
								3,
								4,
								5,
								6,
								7,
								8,
								9
                                                    ],
                            "order_is_refunded": [
								0,
								1,
								2
                                                    ],
                            "order_alert_status": [
								0,
								1,
								2,
								3,
								4,
								5,
								6
                                                    ],
                            "date_start":   '',
                            "date_end":     '',
                            "columns_order": [
						{id:"order_id",                         name:"Order Id",                field:"order_id",                           cssClass:"cell-title",          sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_status",                     name:"Order Status",            field:"order_status",                       cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.OrderStatus},
                                                {id:"order_financial_status",           name:"Financial Status",        field:"order_financial_status",             cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.FinancialStatus},
                                                {id:"order_tcreate",                    name:"Order Tcreate",           field:"order_tcreate",                      cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"item_name",                        name:"Item Name",               field:"item_name",                          cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"item_shopify_variant_id",          name:"Item Variant Id",         field:"item_shopify_variant_id",            cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"item_sku",                         name:"Sku",                     field:"item_sku",                           cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_delivery_status",            name:"Delivery Status",         field:"order_delivery_status",              cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.DeliveryStatus},	
                                                {id:"item_quantity",                    name:"Quantity",                field:"item_quantity",                      cssClass:"cell-align-right",    sortable:true,  width:120,  resizable:true,  formatter:Slick.Formatters.Value},		
                                                {id:"order_customer_fn",                name:"Order: First Name",       field:"order_customer_fn",                  cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},		
                                                {id:"order_customer_ln",                name:"Order: Last Name",        field:"order_customer_ln",                  cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_customer_address1",          name:"Order: Address1",         field:"order_customer_address1",            cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_address2",          name:"Order: Address2",         field:"order_customer_address2",            cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_customer_province",          name:"Order: Province",         field:"order_customer_province",            cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_customer_city",              name:"Order: City",             field:"order_customer_city",                cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_customer_country",           name:"Order: Country",          field:"order_customer_country",             cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"order_customer_zip",               name:"Order: Zip",              field:"order_customer_zip",                 cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_phone",             name:"Customer Phone",          field:"order_customer_phone",               cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_customer_email",             name:"Customer Email",          field:"order_customer_email",               cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"fulfillment_tracking_number",      name:"Tracking Number",         field:"fulfillment_tracking_number",        cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"item_is_refunded",                 name:"Is Refunded",             field:"item_is_refunded",                   cssClass:"cell-align-center",   sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.IsItemRefunded},	
                                                {id:"order_topen",                      name:"Order Open Time",         field:"order_topen",                        cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_tcancel",                    name:"Order Cancel Time",       field:"order_tcancel",                      cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"order_tclose",                     name:"Order Close Time",        field:"order_tclose",                       cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                            ]
};

var predefined_report_bulk_order_add = {
                                    "date_start": 'CUSTOM',
                                    "date_end": 'CUSTOM',
                                    description: 'This is a \'Bulk Order\' report.'
                            };

/** --------
 *  REPORT 3
 *  -------- */
var predefined_report_donalds_report = {
                            "tables_columns": [                                                
                                                "orders.order_id",
                                                "orders.order_receipt_id", 
                                                "orders.order_delivery_status", 
                                                "orders.order_customer_email", 
                                                "orders.order_customer_fn", 
                                                "orders.order_customer_ln", 
                                                
                                                
                                                "fulfillments.fulfillment_id",
                                                "fulfillments.fulfillment_tracking_last_status_text", 
                                                "fulfillments.fulfillment_tcheck",
                                                
                                                "items.item_sku", 
                                                "items.item_name", 
                                                "items.item_is_refunded", 
                                                "items.item_quantity", 
                                                "items.item_price",
                                                                                                
                                                "vendor_tracking.vendor_id",
                                                "vendor_tracking.tracking_number",
                                                "vendor_tracking.tracking_tmodified",
                                                "vendor_tracking.tracking_tcreate",
                                                
                                                "orders.order_tcreate",
                                                "fulfillments.fulfillment_tracking_last_date",
                                                
                                                "fulfillments.fulfillment_tracking_country_to",
                                                "orders.order_customer_country"
                                            ],
                            "vendor_id": [
						1,
						2,
						3,
						4,
						5,
						6,
                                                7,
						0
                                            ],
                            "order_delivery_status": [
								0,
								2,
								3,
								4,
								5,
								6,
								7,
								8,
								9
                                                    ],
                            "order_is_refunded": [
								0,
								1,
								2
                                                    ],
                            "order_alert_status": [
								0,
								1,
								2,
								3,
								4,
								5,
								6
                                                    ],
                            "date_start":   '',
                            "date_end":     '',
                            "columns_order": [
						{id:"order_id",                                 name:"Order Id",                    field:"order_id",                               cssClass:"cell-title",          sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},
						{id:"order_receipt_id",                         name:"Order Receipt Id",            field:"order_receipt_id",                       cssClass:"cell-title",          sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"fulfillment_id",                           name:"Fulfillment Id",              field:"fulfillment_id",                         cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"vendor_id",                                name:"Vendor Id",                   field:"vendor_id",                              cssClass:"cell-align-center",   sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Vendor},
                                                {id:"tracking_number",                          name:"Tracking Number",             field:"tracking_number",                        cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                		
                                                {id:"order_tcreate",                            name:"Order Time Created",          field:"order_tcreate",                          cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareDateAlert},		
                                                {id:"fulfillment_tracking_last_date",           name:"Fulfillment Last Time",       field:"fulfillment_tracking_last_date",         cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                
                                                {id:"order_customer_country",                   name:"Order Client Country",        field:"order_customer_country",                cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value,          groupTotalsFormatter: compareStrAlert},		
                                                {id:"fulfillment_tracking_country_to",          name:"Fulfillment Country To",      field:"fulfillment_tracking_country_to",       cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                
                                                {id:"item_is_refunded",                         name:"Is Refunded",                 field:"item_is_refunded",                       cssClass:"cell-align-center",   sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.IsItemRefunded, groupTotalsFormatter: checkPartialAlert},
                                                
                                                {id:"order_delivery_status",                    name:"Delivery Status",             field:"order_delivery_status",                  cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.DeliveryStatus},	
                                                {id:"order_customer_email",                     name:"Customer Email",              field:"order_customer_email",                   cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},		
                                                {id:"order_customer_fn",                        name:"First Name",                  field:"order_customer_fn",                      cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},		
                                                {id:"order_customer_ln",                        name:"Last Name",                   field:"order_customer_ln",                      cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                		
                                                {id:"fulfillment_tracking_last_status_text",    name:"Fulfillment Last Text",       field:"fulfillment_tracking_last_status_text",  cssClass:"",                    sortable:true,  width:360,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"fulfillment_tcheck",                       name:"Fulfillment Time Check",      field:"fulfillment_tcheck",                     cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                	
                                                {id:"item_sku",                                 name:"Sku",                         field:"item_sku",                               cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"item_name",                                name:"Item Name",                   field:"item_name",                              cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},	
                                                {id:"item_quantity",                            name:"Quantity",                    field:"item_quantity",                          cssClass:"cell-align-right",    sortable:true,  width:120,  resizable:true,  formatter:Slick.Formatters.Value},	
                                                {id:"item_price",                               name:"Price",                       field:"item_price",                             cssClass:"cell-align-right",    sortable:true,  width:120,  resizable:true,  formatter:Slick.Formatters.Value},
                                                
                                                {id:"tracking_tmodified",                       name:"Tracking Time Create",        field:"tracking_tmodified",                     cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                                {id:"tracking_tcreate",                         name:"Tracking Time Modified",      field:"tracking_tcreate",                       cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                                            ]
};

var predefined_report_donalds_order_add = {
                                    "date_start": 'CUSTOM',
                                    "date_end": 'CUSTOM',
                                    description: 'This is a \'Donald\'s\' Report.'
                            };




/** +-----------------------------------------------------------+
 *  | +-------------------------------------------------------+ |
 *  | |                       FUNCTIONS                       | |
 *  | +-------------------------------------------------------+ |
 *  +-----------------------------------------------------------+ */




/** +----------+
 *  | NAV MENU |
 *  +----------+
 *
 *  DESCRIPTION:
 *  Create site's Navigation Menu
 *
 */
setHeaderMenu(thisSite);

/** +-------------+
 *  | EXECUTE GET |
 *  +-------------+
 *
 *  DESCRIPTION:
 *  Send and receive GET requests to server
 *
 */
function executeGET()
{
    var MyResult = $.ajax({
        type: "GET",
        url: request_string,        
        async: false,
        processData:'text',
        complete: function(response)
        {
            ajax_response = response;
        }
    });
    return MyResult;
}
/** +------------------+
 *  | EXECUTE REQUESTS |
 *  +------------------+
 *
 *  DESCRIPTION:
 *  - get arrays setting for build requests
 *  - open modal
 *  - send array to build request string
 *  - send request as a string to execute
 *  - controll pagination
 *  - refresh modal messages
 *  - close modal
 *  - allow to create table with report result
 *
 */
function cleanDisplay() {
    var c = document.createElement('div');
    c.innerHTML = 'x';
    c.style.visibility = 'hidden';
    c.style.height = '1px';
    document.body.insertBefore(c, document.body.firstChild);
    window.setTimeout(function() {document.body.removeChild(c)}, 1);
}

function executeRequest(request){
    clearReportCheckbox();
    setReportCheckbox(request);
    
    grid_data = [];
    number_of_records = 0;
    total_number_of_records = 0;
    page = 0;
    
    function showModal(){
        $("#modal_sm_title").empty();
        $("#modal_sm_message").empty();
        $("#modal_sm_footer").empty();
        $("#modal_sm_title").append('Receiving records...');
        //$("#modal_sm_message").append('<h6 class="text-center">Started receiving records.</h6><br><div class="text-center"><i class="fa fa-spinner fa-spin m--font-info" style="font-size:36px;"></i></div><br><p class="text-center">I\'m receiving data for <br><span class="m--font-info" style="font-size:1.3em">' + (parseInt(page) + 1) + '</span><br> page.</p>');
        $("#modal_sm_message").append('<h6 class="text-center">I\'m receiving records.</h6><br><div class="text-center"><i class="fa fa-spinner fa-spin m--font-info" style="font-size:36px;"></i></div><br><p class="text-center">It can takes some seconds</p>');
        $("#modal_sm").modal('show');
    }
    function doRequests(){
        
        function refreshModal(){
            $("#modal_sm_message").empty();            
            $("#modal_sm_message").html('<h6 class="text-center">Received <br><span class="m--font-info" style="font-size:1.3em">' + total_number_of_records + '</span><br> records.</h6><br><div class="text-center"><i class="fa fa-spinner fa-spin m--font-info" style="font-size:36px;"></i></div><br><p class="text-center">I\'m receiving data for <br><span class="m--font-info" style="font-size:1.3em">' + (parseInt(page) + 1) + '</span><br> page.</p>');
            cleanDisplay();
            return 'ok';
        }
        
        function executeRequests(){
            
            createReportRequest(request);        
            executeGET();
            
            var obj     = {};
            var json    = ajax_response.responseText;
            obj         = JSON.parse(json);
            
            var tmp_msg = JSON.parse(obj.msg);
            page = parseInt(tmp_msg['page'])+1;
            number_of_records = tmp_msg['number_of_records'];
            total_number_of_records += number_of_records;
            sql.push(tmp_msg['sql']);
            
            var result  = new Array();
            result  = obj.data.report;
            
            result.forEach(function(value){
                grid_data.push(value);
            });
        }
        
        do{
            executeRequests();
            if(parseInt(number_of_records) == limit){
                refreshModal();
            };
        }
        while(parseInt(number_of_records) == limit);
        $("#wizard-table-title-small").html('<span class="m--font-boldest">'+total_number_of_records.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</span> records, from <span class="m--font-boldest">'+picker_core_date_start+'</span> to <span class="m--font-boldest">'+picker_core_date_end+'</span>');
        $("#table_portlet").show();
    }
    
    function showFormatingModal(){
    
        $("#records_number_container").append('<p>Total: ' + total_number_of_records + ' records</p>');

        $("#modal_sm_title").empty();
        $("#modal_sm_message").empty();
        $("#modal_sm_title").append('Formating...');
        $("#modal_sm_message").append('<h6 class="text-center">Formating received <br><span class="m--font-success" style="font-size:1.3em">' + total_number_of_records + '</span><br> records.</h6><br><div class="text-center"><i class="fa fa-spinner fa-spin m--font-success" style="font-size:36px;"></i></div>');
    }
    
    function setResults(){
        /*
         * Set Results
         */  
        var result = grid_data;
        var order = request_builder_array.columns_order;
        grid_data = [];

        /*
         * Columns Order
         */    
        var result_tmp = new Array();
        var i=0;
        result.forEach(function(row){
            var row_tmp = {};
            row_tmp.id = i;
            i++;
            order.forEach(function(o){
               var key;
               for(key in row){
                    if(key == o.field){                        
                        row_tmp[key] = row[key];
                    }
               };
            });
            result_tmp.push(row_tmp);
        });
        grid_data = null;
        grid_data = result_tmp;
    }

    function decideNextStep(){
        /*
         * Take Decision If Create Table
         */
        if(grid_data.length > 0){
            $("#modal_sm").modal('hide');
            $('#settings_portlet_colapse').click();
            doTable();
        } else {
            $("#modal_sm_title").empty();
            $("#modal_sm_message").empty();
            $("#modal_sm_footer").empty();
            $("#modal_sm_title").append('There no records');
            $("#modal_sm_message").append('Change parameters and try again.');
        }
    };
        
    showModal();
    setTimeout(function() {
        doRequests();
        showFormatingModal();
        setResults();
        decideNextStep();
    }, 2000);
};

/** +-------+
 *  | TABLE |
 *  +-------+
 *
 *  DESCRIPTION:
 *  - declare and run table instance
 *
 */

/** -------- 
 *  DO TABLE
 *  -------- */
function doTable(){
    
    var sortcol = "order_id";
    var sortdir = 1;
    var options = {
        enableCellNavigation: true,
        editable: true,
        autoHeight:false,
        forceFitColumns: false,
        multiColumnSort: true
    };
    
    /*
     * Grouping setings
     */
    if(current_report == 'fraud_detection_order') {
        new Slick.Data.Aggregators.ClearIndexes();
        function groupBy() {
            dataView.setGrouping([
                {
                    getter: "order_id",
                    aggregators: [
                            new Slick.Data.Aggregators.CountIndex("item_quantity","morethan",3),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_fn"),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_ln"),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_address1"),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_address2"),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_city"),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_country"),
                            new Slick.Data.Aggregators.StrCompIndex("order_customer_zip"),
                            new Slick.Data.Aggregators.SumItemValueGateway("item_value")
                    ],
                    formatter: function (g) {
                        return "Order Id:  " + g.value;
                    },
                    collapsed: true,
                    aggregateCollapsed: true,
                    lazyTotalsCalculation: false
                }
            ]);
        }
    }
    
    if(current_report == 'bulk_order') {
        new Slick.Data.Aggregators.ClearIndexes();
        function groupBy() {
            dataView.setGrouping([
                {
                    getter: "order_id",
                    aggregators: [
                    ],
                    formatter: function (g) {
                        return "Order Id:  " + g.value;
                    },
                    collapsed: true,
                    aggregateCollapsed: true,
                    lazyTotalsCalculation: false
                }
            ]);
        }
    }
    
    if(current_report == 'donalds_report') {
        new Slick.Data.Aggregators.ClearIndexes();
        function groupBy() {
            dataView.setGrouping([
                {
                    getter: "order_id",
                    aggregators: [
                        new Slick.Data.Aggregators.StrCompEmptyIndex("order_customer_country"),
                        new Slick.Data.Aggregators.DateCompIndex("order_tcreate"),
                        new Slick.Data.Aggregators.CheckPartialIndex("item_is_refunded")
                    ],
                    formatter: function (g) {
                        return "Order Id:  " + g.value;
                    },
                    collapsed: true,
                    aggregateCollapsed: true,
                    lazyTotalsCalculation: false
                }
            ]);
        }
    }
    
    /*
     * Filter by dataView record id
     */
    function myFilterId(item, args) { 
        if(args != null && args !== ""){
            if(args.id.includes(item.id)){
                return true;
            } else {
                return false;
            };
        } else {
            return true;
        };        
    }
    
    /*
     * Filter by order id 
     */
    function myFilterOrderId(item, args) { 
        if(args != null && args !== ""){
            if(args.order_id.includes(item.order_id)){
                return true;
            } else {
                return false;
            };
        } else {
            return true;
        };        
    }
    
    /*
     * Start initialize
     */

    var initialize = 0;
    if(typeof dataView == 'undefined'){
        initialize = 1;
    }
    
    /*
    * INITIALIZE GROUPING
    * -------------------
    */
    var groupItemMetadataProvider = new Slick.Data.GroupItemMetadataProvider({ checkboxSelect: true, checkboxSelectPlugin: checkboxSelector });

    /*
    * INITIALIZE DATAVIEW
    * -------------------
    */
    dataView = new Slick.Data.DataView({
            groupItemMetadataProvider: groupItemMetadataProvider,
            inlineFilters: true
    });


    /*
     * INITIALIZE GRID
     * ---------------
     */   
    grid = new Slick.Grid("#wizard_table", dataView, grid_columns, options);

    /*
     * REGISTER PLUGINS
     */
    grid.registerPlugin(groupItemMetadataProvider);
    grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));    
    grid.registerPlugin(checkboxSelector);
    //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
    var columnpicker = new Slick.Controls.ColumnPicker(grid_columns, grid, options);

    /*
     * GRID AND DATAVIEW SYNCHRONISE
     * -----------------------------
     */
    
    grid.invalidate();
    grid.render();
    dataView.syncGridSelection(grid, true, true);

    
    /*
     * LISTENERS
     */
    grid.onSort.subscribe(function (e, args) {
        var cols = args.sortCols;
        dataView.sort(function (dataRow1, dataRow2) {
            for (var i = 0, l = cols.length; i < l; i++) {
                var field = cols[i].sortCol.field;
                var sign = cols[i].sortAsc ? 1 : -1;
                var value1 = dataRow1[field], value2 = dataRow2[field];
                var result = (value1 == value2 ? 0 : (value1 > value2 ? 1 : -1)) * sign;
                if (result != 0) {
                    return result;
                }
            }
          return 0;
        });
        grid.invalidate();
        grid.render();
    });

    dataView.onRowCountChanged.subscribe(function (e, args) {
            grid.updateRowCount();
    grid.render();
    });

    dataView.onRowsChanged.subscribe(function (e, args) {
            grid.invalidateRows(args.rows);
    grid.render();
    });



    /*
     * UPDATE RESULTS
     * --------------
     */
    function updateResults(){
        dataView.beginUpdate();
        grid.setSelectedRows([]);
        dataView.setItems([]);
        dataView.setItems(grid_data);
        unsetFilter();
        groupBy();
        dataView.endUpdate();
    }
    updateResults();
    
    function sendToRefresh(){
        
        /*
         * Get all selected records
         */        
        var sel = grid.getSelectedRows();
        
        /*
         * If records are selected
         */
        if (sel.length === 0) {
            alert('Nothing selected to refreshing\n\nSelect some items please');
        }
        else {
            
            /*
             * 1. Add Column
             * 2. Build objects
             *      • x - object to build request
             *      • y - object to manage responses
             *      • z - object with records without fulfillment_id
             * 3. Insert y,z to results controll - status 0 for z, status 1 for y
             * 4. Send request to crawler.controller.php
             * 
             * 5. 5 times [t] every 5 seconds
             *      • send requests to new report wizard/refresh17track for x records
             *      • received results:
             *          ▪ if received date > current date
             *              - change status 2 if status delivery is the same - remove record from y
             *          ▪ else
             *              - if times [t] < 5 than again send request
             *              - else change status 3
             * 6. After 5 times change status 4 for y
             * 
             * A. Statuses:
             *      • '' - ''
             *      •  0 - no fullfilment id -> NO FULFILLMENT ID / danger
             *      •  1 - during requesting -> yellow spinner
             *      •  2 - correct result - delivery status the same -> STATUS THE SAME / info
             *      •  3 - correct result - delivery status changed -> STATUS CHANGED / success
             *      •  4 - not received result - NOT REFRESHED / warning
             *      
             */
            
            
            /** ----------
             *  ADD COLUMN
             *  ---------- */
            if(grid.getColumnIndex("tracking_status_receiving") == undefined){
                var new_column_definition = {id:"tracking_status_receiving", name:"Receiving Status", field:"tracking_status_receiving", cssClass:"cell-align-center", sortable:true, width:180, resizable:true, formatter:Slick.Formatters.LoadSpinner};
                dataView.beginUpdate();
                grid_columns.splice(4, 0, new_column_definition);
                grid.setColumns(grid_columns);
                dataView.endUpdate();
            }
            /** -------------
             *  BUILD OBJECTS
             *  ------------- */
            var ids = $.map(sel, function(e,i) {return grid.getDataItem(e).id; });
            var x = [];           
            var y = [];           
            var z = [];
            var key;
            for(key in ids){
                var _a = {};
                var fulfillment_tracking_number = dataView.getItemByIdx(ids[key])['fulfillment_tracking_number'];
                var order_id = dataView.getItemByIdx(ids[key])['order_id'];
                var fulfillment_id = dataView.getItemByIdx(ids[key])['fulfillment_id'];
                var order_delivery_status = dataView.getItemByIdx(ids[key])['order_delivery_status'];
                var fulfillment_tcheck = dataView.getItemByIdx(ids[key])['fulfillment_tcheck'];
                
                if(fulfillment_id != '' && fulfillment_id != null && fulfillment_id != undefined){
                    _a['t'] = fulfillment_tracking_number;
                    _a['o'] = order_id;
                    _a['f'] = fulfillment_id;
                    
                    // x - object to build request
                    x.push(_a);
                    _a['d'] = order_delivery_status;
                    _a['i'] = ids[key];
                    _a['c'] = fulfillment_tcheck;
                    // y - object to manage responses
                    y.push(_a);
                } else {
                    _a['d'] = order_delivery_status;
                    _a['i'] = ids[key];
                    //z - object with records without fulfillment_id
                    z.push(_a);
                }
            };
            
            /** -----------------------------
             *  INSERT CURRENT PROCESS STATUS
             *  ----------------------------- */
            dataView.beginUpdate();
            var key;
            for(key in y){
                var item = dataView.getItem(dataView.getRowById(y[key]['i']));
                item.tracking_status_receiving = 1;
                grid.updateCell(parseInt(y[key]['i']),4);                
            };
            var key;
            for(key in z){
                var item = dataView.getItem(dataView.getRowById(z[key]['i']));
                item.tracking_status_receiving = 0;
                grid.updateCell(parseInt(z[key]['i']),4);                
            };
            dataView.endUpdate();
            
            if(y.length){
                /** --------------------------------------
                 *  SEND REQUEST TO CRAWLER.CONTROLLER.PHP
                 *  -------------------------------------- */
                
                var TRACKING_ID = 2;
                var key;
                for(key in x){
                    var _x = [];
                    _x.push(x[key]);
                    var url = API_URL + 'crawler/' + TRACKING_ID + '/' + JSON.stringify(_x);
                     $.ajax({
                        type: "GET",
                        url: url,        
                        async: true,
                        processData:'text',
                        complete: function(){
                        }
                    });
                }
                
                
                /** -----------------------------------
                 *  5 TIMES EVERY 5 SECOND CHECK STATUS
                 *  ----------------------------------- */
                var getTrackingCurrentStatusRevolverRoute = 0
                getTrackingCurrentStatusRevolver();
                
                function getTrackingCurrentStatus()
                {
                    var key;
                    var get_str = '';
                    var separator = "";
                    for(key in y){
                        var fulfillment_id = y[key]['f'];
                        var str = separator + 'fulfillment_id[]=' + fulfillment_id;
                        get_str += str;
                        separator = '&';
                    };

                    var url = API_URL + 'report/wizard/wizard_17track_fulfillments/?' + get_str;
                    
                    $.ajax({
                        type: "GET",
                        url: url,        
                        async: false,
                        complete: function(response){
                            var obj     = {};
                            var json    = response.responseText;
                            obj         = JSON.parse(json);
                            dataView.beginUpdate();
                            var res = obj.data.report;                            
                            var key;
                            for(key in y){
                                var k;
                                for(k in res){
                                    if(y[key]['f'] == res[k]['fulfillment_id']){
                                        if(y[key]['c'] < res[k]['fulfillment_tcheck']){
                                            var item = dataView.getItem(dataView.getRowById(y[key]['i']));
                                            if(y[key]['d'] == res[k]['order_delivery_status']){ 
                                                item.tracking_status_receiving = 2;
                                                grid.updateCell(parseInt(y[key]['i']),grid.getColumnIndex("tracking_status_receiving"));
                                                item.fulfillment_tcheck = res[k]['fulfillment_tcheck'];
                                                grid.updateCell(parseInt(y[key]['i']),grid.getColumnIndex("fulfillment_tcheck"));
                                            } else {
                                                item.tracking_status_receiving = 4;
                                                grid.updateCell(parseInt(y[key]['i']),grid.getColumnIndex("tracking_status_receiving"));
                                                item.order_delivery_status = res[k]['order_delivery_status'];
                                                grid.updateCell(parseInt(y[key]['i']),grid.getColumnIndex("order_delivery_status"));
                                                item.fulfillment_tcheck = res[k]['fulfillment_tcheck'];
                                                grid.updateCell(parseInt(y[key]['i']),grid.getColumnIndex("fulfillment_tcheck"));
                                            }  
                                            y.splice(key, 1);
                                        }
                                    }
                                }
                            }
                            dataView.endUpdate();
                            getTrackingCurrentStatusRevolver();
                            
                        }
                    });
                }

                function getTrackingCurrentStatusRevolver(){
                    if(y.length){
                        if(getTrackingCurrentStatusRevolverRoute<5){
                            setTimeout(function(){
                                getTrackingCurrentStatus();
                                getTrackingCurrentStatusRevolverRoute++;
                            },5000);
                        } else {
                            dataView.beginUpdate();
                            var key;
                            for(key in y){ 
                                var item = dataView.getItem(dataView.getRowById(y[key]['i']));
                                item.tracking_status_receiving = 4;
                                grid.updateCell(parseInt(y[key]['i']),4);
                            }
                            dataView.endUpdate();
                        }
                    }
                }
                grid.invalidate();
                grid.render();
            };
        };
    };
    
    function getWarningsFilter(){
        var ids = Slick.Data.getWarningsId(); 
        var arr = [];
        var key;
        for(key in ids){
            arr.push(ids[key]);
        };
        
        
        dataView.setRefreshHints({
            ignoreDiffsBefore: 0,
            ignoreDiffsAfter: 0,
        });
        
        dataView.setFilterArgs({order_id:arr});
        dataView.setFilter(myFilterOrderId);
        dataView.refresh();
    };
    
    function getSelectedFilter(){
        //var renderedRange = grid.getRenderedRange();
        
        var sel = grid.getSelectedRows();
        var ids = $.map(sel, function(e,i) {return grid.getDataItem(e).id; });            
        var arr = [];
        var key;
        for(key in ids){
            var id = ids[key];
            arr.push(id);
        };
        
        dataView.setRefreshHints({
            ignoreDiffsBefore: 0,
            ignoreDiffsAfter: 0,
        });
        
        dataView.setFilterArgs({id:arr});
        dataView.setFilter(myFilterId);
        dataView.refresh();
    };
    
    
    function unsetFilter(){
        dataView.setFilter(function(item){
            return true;
        });
    };
    
    /*
     * Finish and show portlets
     */
    $("#table_portlet").show();    
    $("#wizard_sql_text").append(setSqlText());
    
    $("#filter_warnings_groups").prop("checked", false);
    $("#filter_selected_groups").prop("checked", false);
    $("#group_groups").prop("checked", true);
    $("#collapse_groups").prop("checked", true);
    
    
    if(current_report == 'fraud_detection_order') {
        
        $("#tools_buttons_container_1").show();
        $("#tools_buttons_container_2").show();
        
        $("#group_groups_container").show();
        $("#collapse_groups_container").show();
        
        $("#filter_selected_groups_container").show();
        $("#filter_warnings_groups_container").show();
        
        $("#refresh_order_status").show();
    }

    if(current_report == 'bulk_order') {
        
        $("#tools_buttons_container_1").show();
        $("#tools_buttons_container_2").show();
        
        $("#group_groups_container").show();
        $("#collapse_groups_container").show();

        $("#filter_selected_groups_container").hide();
        $("#filter_warnings_groups_container").hide();
        
        $("#refresh_order_status").hide();
    }

    if(current_report == 'donalds_report') {
        
        $("#tools_buttons_container_1").show();
        $("#tools_buttons_container_2").show();
        
        $("#group_groups_container").show();
        $("#collapse_groups_container").show();

        $("#filter_selected_groups_container").hide();
        $("#filter_warnings_groups_container").show();
        
        $("#refresh_order_status").hide();
    }

    if(initialize == 1){
        initialize = 0;
        
    
        /*
         * LISTENERS
         */
        $("#filter_warnings_groups").click(function(){
            if($(this).is(':checked')){
                unsetFilter();
                getWarningsFilter();
            } else {
                unsetFilter();
                if($("#filter_selected_groups").prop("checked")){
                    getSelectedFilter();
                }
            }
        });

        $("#filter_selected_groups").click(function(){
            if($(this).is(':checked')){
                unsetFilter();
                getSelectedFilter();
            } else {
                unsetFilter();
                if($("#filter_warnings_groups").prop("checked")){
                    getWarningsFilter();
                }
            }
        });
        $("#group_groups").click(function(){
            if($(this).is(':checked')){
                $("#collapse_groups").prop("disabled", false);
                groupBy();
            } else {
                if(!$("#collapse_groups").prop("checked")){
                    $("#collapse_groups").prop("checked", true);
                }
                $("#collapse_groups").prop("disabled", true);
                dataView.setGrouping([]);
            }
        });
        $("#collapse_groups").click(function(){
            if($(this).is(':checked')){
                dataView.collapseAllGroups();
            } else {
                dataView.expandAllGroups();
            }
        });
        $("#refresh_order_status").click(function(){
            sendToRefresh();
        });
    };
    
};



/** +--------------+
 *  | TABLE EXPORT |
 *  +--------------+
 *
 *  DESCRIPTION:
 *  - export to csv
 *
 */
function h_csv_export() {    
    /*
    $("#modal_sm_title").empty();
    $("#modal_sm_message").empty();
    $("#modal_sm_footer").empty();
    $("#modal_sm_title").append('exporting to CSV...');
    $("#modal_sm_message").append('<div class="text-center"><i class="fa fa-spinner fa-spin m--font-info" style="font-size:36px;"></i></div>');
    $("#modal_sm").modal('show');
    */
   
    var separator = ',';
    var t,s;
    var matrix = [];
    var i,j;
    grid_headers_export = [];
    grid_export = [];
    
    var cols = grid.getColumns();
    t = '';
    s = '';
    for(j=0;j<cols.length;j++){
        if(cols[j]['id'] != "_checkbox_selector"){
            t += s;        
            t += '"' + cols[j]['name'].replace(/"/g, '\"\"') + '"';
            matrix.push(cols[j]['id']);
            s = separator;
        }
    };
    t += "\n";
    grid_headers_export = t;
    var items = dataView.getFilteredItems();
    var k;
    for(k in items){
        var row =  items[k];
        t = '';
        s = '';
        
        for(j=0;j<matrix.length;j++){
            t += s; 
            //console.log (j + " -> " + cols[j]['id'] + " -> " + row[matrix[j]]);
            //var t_val = ($("<div>").html(row[matrix[j]]).text()).replace(/"/g, '\"\"');
            var t_val = row[matrix[j]];
            if(matrix[j] in row){
                if(matrix[j] in const_statuses){
                    t += const_statuses[matrix[j]][t_val];
                } else {
                    t += t_val;
                }
                s = separator;
            } else {    
                
                if(matrix[j] == 'item_value'){
                    var item_value = row.item_quantity * row.item_price;
                    t += item_value;
                    s = separator;
                } else if(matrix[j] == 'order_status'){
                    var open = row.order_topen == '0000-00-00 00:00:00'?0:Date.parse(row.order_topen);
                    var close = row.order_tclose == '0000-00-00 00:00:00'?0:Date.parse(row.order_tclose);
                    var cancel = row.order_tcancel == '0000-00-00 00:00:00'?0:Date.parse(row.order_tcancel);

                    if(open > close && open > cancel){
                        item_value = 'OPENED';
                    } else if(close > cancel){
                        item_value = 'CLOSED';
                    } else if(cancel > open){
                        item_value = 'CANCELLED';
                    } else {
                        item_value = 'UNKNOWN';
                    }
                    t += item_value;
                    s = separator;
                } else {
                    t += '';
                    s = separator;
                }
            }
        };         
        t += "\n";
        grid_export += t;
    };
    /*
    $("#modal_sm").modal('hide');
    $("#modal_sm_title").empty();
    $("#modal_sm_message").empty();
    $("#modal_sm_footer").empty();
    */
   
    var csv = grid_headers_export + grid_export;
    var ts = 'wizard_' + DATE_TIME_NOW_FILE;
    
    /* This version not works on Chrome
     * 
    var uri = 'data:text/csv;charset=utf-8,%EF%BB%BF' + encodeURIComponent(csv);
    var download_link = document.createElement('a');
    download_link.href = uri;
    
    
    
    download_link.download = ts+".csv";
    document.body.appendChild(download_link);
    download_link.click();
    document.body.removeChild(download_link);
    */
    
    var download_link = document.createElement('a');
    csvData = new Blob([csv], { type: 'application/csv;charset=utf-8;' }); 
    var csvUrl = URL.createObjectURL(csvData);
    download_link.href =  csvUrl;
    download_link.download = ts + ".csv";
    download_link.click();    
    document.body.removeChild(download_link);
};


/** +----------+
 *  | PORTLETS |
 *  +----------+
 *
 *  DESCRIPTION:
 *  Declare portlets
 *
 */

/** -------------
 *  TOOLS PORTLET
 *  ------------- */
var settings_portlet = $('#settings_portlet').mPortlet();

/** -------------
 *  TABLE PORTLET
 *  ------------- */
var table_portlet = $('#table_portlet').mPortlet();

//wizard_sql_portlet

/** ------------------
 *  WIZARD SQL PORTLET
 *  ------------------ */
var sql_portlet = $('#sql_portlet').mPortlet();

/** +-----------------+
 *  | DATERANGEPICKER |
 *  +-----------------+
 *
 *  DESCRIPTION:
 *  Declare and get default settings for Date Range Picker
 *
 */

/** -------------------
 *  RUN DATERANGEPICKER
 *  ------------------- */
function cb(start, end, label) {
    var Title = '';
    var Range = '';

    if ((end - start) < 100) {
        Title = 'Today:';
        Range = start.format('YYYY-MM-DD');
    } else if (label == 'Yesterday') {
        Title = 'Yesterday:';
        Range = start.format('YYYY-MM-DD');
    } else {
        Range = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
    }
    picker.find('.m-subheader__daterange-date').html(Range);
    picker.find('.m-subheader__daterange-title').html(Title);
    
}

/** ------------------------------------------------------------------------
 *  VARIABLES AND FUNCTIONS FOR SELECT PREDEFINED PERIODS IN DATERANGEPICKER
 *  ------------------------------------------------------------------------ */

function picker_selected_today(){
    picker_selected_predefined_period_name = 'picker_selected_today';
    picker_core_date_start = getDateToday();
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_yesterday(){
    picker_selected_predefined_period_name = 'picker_selected_yesterday';
    picker_core_date_start = getDateMinusDays(1);
    picker_core_date_end = getDateMinusDays(1);
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_2_days(){
    picker_selected_predefined_period_name = 'picker_selected_last_2_days';
    picker_core_date_start = getDateMinusDays(1);
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_7_days(){
    picker_selected_predefined_period_name = 'picker_selected_last_7_days';
    picker_core_date_start = getDateMinusDays(6);
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_30_days(){
    picker_selected_predefined_period_name = 'picker_selected_last_30_days';
    picker_core_date_start = getDateMinusDays(29);
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_60_days(){
    picker_selected_predefined_period_name = 'picker_selected_last_60_days';
    picker_core_date_start = getDateMinusDays(59);
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_90_days(){
    picker_selected_predefined_period_name = 'picker_selected_last_90_days';
    picker_core_date_start = getDateMinusDays(89);
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_this_week(){
    picker_selected_predefined_period_name = 'picker_selected_this_week';
    picker_core_date_start = getDateFirstDayOfThisWeek();
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_week(){
    picker_selected_predefined_period_name = 'picker_selected_last_week';
    picker_core_date_start = getDateFirstDayOfWeekMinustWeeks(1);
    picker_core_date_end = getDateLastDayOfLastWeek();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_this_month(){
    picker_selected_predefined_period_name = 'picker_selected_this_month';
    picker_core_date_start = getDateFirstDayOfThisMonth();
    picker_core_date_end = getDateToday();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_last_month(){
    picker_selected_predefined_period_name = 'picker_selected_last_month';
    picker_core_date_start = getDateFirstDayOfMonthMinusMonths(1);
    picker_core_date_end = getDateLastDayOfLastMonth();
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_start_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = GET_DATE_SELECTED_PERIOD_NAME;
    picker_selected_end_value = GET_DATE_SELECTED_PERIOD_VALUE;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

function picker_selected_fixed_date(start_date,end_date){
    picker_selected_predefined_period_name = 'picker_selected_last_month';
    picker_core_date_start = start_date ;
    picker_core_date_end = end_date;
    
    picker_selected_start_date = picker_core_date_start + picker_start_string;
    picker_selected_start_name = 'fixed_start';
    picker_selected_start_value = 0;
    
    
    picker_selected_end_date = picker_core_date_end + picker_end_string;
    picker_selected_end_name = 'fixed_end';
    picker_selected_end_value = 0;
    
    return [picker_selected_start_date,picker_selected_end_date];    
}

/** --------------------------------
 *  DEFAULT DATE FOR DATERANGEPICKER
 *  -------------------------------- */
picker_selected_last_2_days();

setTimeout(function(){
    $('[data-range-key="Last 2 Days"]').click();
    current_report = 'fraud_detection_order';
},1000);
/** -------------------
 *  RUN DATERANGEPICKER
 *  ------------------- */


picker.daterangepicker({
    showWeekNumbers: true,
    opens: 'right',
    ranges: {
        'Today':            picker_selected_today(),
        'Yesterday':        picker_selected_yesterday(),
        'Last 2 Days':      picker_selected_last_2_days(),
        'Last 7 Days':      picker_selected_last_7_days(),
        'Last 30 Days':     picker_selected_last_30_days(),
        'Last 60 Days':     picker_selected_last_60_days(),
        'Last 90 Days':     picker_selected_last_90_days(),
        'This Week':        picker_selected_this_week(),
        'Last Week':        picker_selected_last_week(),
        'This Month':       picker_selected_this_month(),
        'Last Month':       picker_selected_last_month(),
    },
    locale: {
                format: 'YYYY-MM-DD',
        },
    function (start) {
        startdate = start.format('YYYY-MM-DD')
    },
    startDate:  picker_selected_start_date,
    endDate:    picker_selected_end_date,
    alwaysShowCalendars: true,
}, cb);

cb(start, end, '');



/** +---------------+
 *  | QUERY BUILDER |
 *  +---------------+
 *
 *  DESCRIPTION:
 *  These functions build query requests
 *      • setCustomReportArrayRequest - create array for custom reports (for predefined are set aleready)
 *      • createRequest - create requests string for custom and predefined requests
 *      
 */

/** ----------------------------------------
 *  CREATE ARRAY REQUESTS FOR CUSTOM REPORTS
 *  ---------------------------------------- */
function setCustomReportArrayRequest()
{
    request_builder_array                               = [];
    var request_builder_array_table_column              = [];
    var request_builder_array_vendor_id                 = [];
    var request_builder_array_order_delivery_status     = [];
    var request_builder_array_order_is_refunded         = [];
    var request_builder_array_order_alert_status        = [];
    var request_builder_array_period                    = [];
    var request_builder_array_columns_order             = [];
        
    $("[data-type='table']").each(function(){
        if($(this).prop('checked')){            
            request_builder_array_table_column.push($(this).data('table') + '.' + $(this).data('column'));
            request_builder_array_columns_order.push($(this).data('column'));
        }
    });
    
    $("[data-type='criteria']").each(function(){
        if($(this).prop('checked')){
            if($(this).data('criteria') == 'vendor_id'){
                request_builder_array_vendor_id.push($(this).data('criteria_id'));
            };
            if($(this).data('criteria') == 'order_delivery_status'){
                request_builder_array_order_delivery_status.push($(this).data('criteria_id'));
            };
            if($(this).data('criteria') == 'order_is_refunded'){
                request_builder_array_order_is_refunded.push($(this).data('criteria_id'));
            };
            if($(this).data('criteria') == 'order_alert_status'){
                request_builder_array_order_alert_status.push($(this).data('criteria_id'));
            };
        }
    });
    
    var date_start = document.getElementsByName('daterangepicker_start')[0].value;
    var date_end = document.getElementsByName('daterangepicker_end')[0].value;
    request_builder_array = {
                                tables_columns:             request_builder_array_table_column,
                                vendor_id:                  request_builder_array_vendor_id,
                                order_delivery_status:      request_builder_array_order_delivery_status,
                                order_is_refunded:          request_builder_array_order_is_refunded,
                                order_alert_status:         request_builder_array_order_alert_status,
                                date_start:                 date_start,
                                date_end:                   date_end,
                                columns_order:              request_builder_array_columns_order
                            };
                           
    return;
}
function encodeRequestData(key,data) {
   let ret = [];
   for (let d in data)
     ret.push(key + '[]=' + encodeURIComponent(data[d]));
   return ret.join('&');
}

/** --------------------------
 *  CREATE STRING FOR REQUESTS
 *  -------------------------- */
function createReportRequest(data)
{
    request_string = API_URL + 'report/wizard/' + thisSite + '?';
    var tables_columns              = data.tables_columns;
    var vendor_id                   = data.vendor_id;
    var order_delivery_status       = data.order_delivery_status;
    var order_is_refunded           = data.order_is_refunded;
    var order_alert_status          = data.order_alert_status;
    var date_start                  = data.date_start;
    var date_end                    = data.date_end;
    
    request_string += encodeRequestData('tables_columns',tables_columns);
    request_string += '&' + encodeRequestData('vendor_id',vendor_id);
    request_string += '&' + encodeRequestData('order_delivery_status',order_delivery_status);
    request_string += '&' + encodeRequestData('order_is_refunded',order_is_refunded);
    request_string += '&' + encodeRequestData('order_alert_status',order_alert_status);
    request_string += '&limit=' + limit;
    request_string += '&p=' + page;
    request_string += '&date_start=' + date_start;
    request_string += '&date_end=' + date_end;
}


/** +-----------+
 *  | LISTENERS |
 *  +-----------+
 *
 *  DESCRIPTION:
 *      • buttons for actions in custom criteria
 *      • export to csv
 *      • export report template
 *      • display SQL query string
 *      • display request string
 *      • generate report GET array
 *      • run custom reports
 *      • run predefined reports
 *      • diisplay predefined reports help      
 *      
 */

/** ------------------
 *  CUSTOMIZE CRITERIA
 *  ------------------ */
$("[data-action]").click(function(){
    var action          = $(this).data("action");
    var action_type     = $(this).data("action-type");
    var target_key      = $(this).data("target-key");
    var target_value    = $(this).data("target-value");
    
    $("[data-" + target_key + "='" + target_value +"']").each(function(){
        if(action == 'select'){
            if(action_type == 'select_all'){
                $(this).prop('checked', true);
            }            
            if(action_type == 'unselect_all'){
                $(this).prop('checked', false);
            }          
            if(action_type == 'reverse'){
                if($(this).prop('checked')){
                    $(this).prop('checked', false);
                } else {
                    $(this).prop('checked', true);
                }
            }
        }
    })
});


/** ----------
 *  EXPORT CSV
 *  ---------- */
$("#h_csv_export").click(function(){    
    h_csv_export();    
});


/** ----------------------------------
 *  DISPLAY PREDEFINED REPORT TEMPLATE
 *  ---------------------------------- */
$("#export_template").click(function(){
    $("#modal_long_title").empty();
    $("#modal_long_body").empty();
    
    $("#modal_long_title").append('Copy and send content to Rafal.');
    $("#modal_long_body").append(JSON.stringify(request_builder_array).replace(/,/g, ',<br>').replace(/{/g, '{<br>').replace(/}/g, '<br>}').replace(/],/g, '<br>],'));
    $("#modal_long").modal('show');
});


/** ------------------
 *  DISPLAY REPORT SQL
 *  ------------------ */
$("#export_sql").click(function(){
    var i = 1;
    var str = '';
    sql.forEach(function(v){
        str += '<h6>Query '+i+'</h6><code>' + v + '</code><br>';
        i++;
    });
    
    $("#modal_long_title").empty();
    $("#modal_long_body").empty();
    
    $("#modal_long_title").append('SQL');
    $("#modal_long_body").append(str);
    $("#modal_long").modal('show');
});

/** ----------------------
 *  DISPLAY REQUEST STRING
 *  ---------------------- */
$("#export_requests").click(function(){
    
    var str = setSqlText();
    
    $("#modal_long_title").empty();
    $("#modal_long_body").empty();
    
    $("#modal_long_title").append('Request');
    $("#modal_long_body").append(str);
    $("#modal_long").modal('show');
});

/** -------------------------
 *  GENERATE REPORT GET ARRAY
 *  ------------------------- */

$("#export_predefined").click(function(){
    encodeHttpGet(picker_selected_predefined_period_name,picker_core_date_start,picker_core_date_end);
});


/** ------------------
 *  RUN CUSTOM REPORT
 *  ------------------ */
$("#submit_custom_report").click(function(){
    unsetTable();
    request_builder_array = [];
    limit = 100000;
    current_report = 'custom';
    
    setCustomReportArrayRequest();
    executeRequest(request_builder_array);
});

/** --------------------
 *  CANCEL CUSTOM REPORT
 *  -------------------- */
$("#cancel_custom_report").click(function(){
    $(".wizard_row_advanced").hide();
    $(".wizard_row_table").show();
    $(".wizard_row_tool").show();
    
});

/** --------------
 *  ASIDE LISTENER
 *  -------------- */
$(".aside_run_predefined").click(function(){
    $(".wizard_row_advanced").hide();
    $(".wizard_row_table").show();
    $(".wizard_row_tool").show();
    
});

$(".aside_run_advanced").click(function(){
    $(".wizard_row_advanced").show();
    $(".wizard_row_table").hide();
    $(".wizard_row_tool").hide();
    
});

/** ----------------------
 *  RUN PREDEFINED REPORTS
 *  ---------------------- */
    
    
    
/*
 * Fraud Detection Order
 */
$("[data-predefined_report='fraud_detection_order']").click(function(){
    doPredefinedReportFraudDetectionOrder();    
});
function doPredefinedReportFraudDetectionOrder(){
    unsetTable();
    request_builder_array = [];
    limit = 250000;
    current_report = 'fraud_detection_order';
    
    var date_start  = picker_selected_start_date.split('T')[0];
    var date_end    = picker_selected_end_date.split('T')[0];
    
    grid_columns = predefined_report_fraud_detection_order.columns_order;
    
    request_builder_array = predefined_report_fraud_detection_order;
    request_builder_array['date_start'] = date_start;
    request_builder_array['date_end'] = date_end;
    predefined_report_fraud_detection_order['date_start'] = date_start;
    predefined_report_fraud_detection_order['date_end'] = date_end;
    
    executeRequest(predefined_report_fraud_detection_order);
    $("#wizard-table-title-big").empty();
    $("#wizard-table-title-small").empty();
    $("#wizard-table-title-big").html('Fraud Detection Order');
}

/*
 * Bulk Order
 */
$("[data-predefined_report='bulk_order']").click(function(){    
    doPredefinedReportBulkOrder();
});

function doPredefinedReportBulkOrder(){
    
    unsetTable();
    request_builder_array = [];
    limit = 250000;
    current_report = 'bulk_order';
    
    var date_start  = picker_selected_start_date.split('T')[0];
    var date_end    = picker_selected_end_date.split('T')[0];
    
    grid_columns = predefined_report_bulk_order.columns_order;
        
    request_builder_array = predefined_report_bulk_order;
    request_builder_array['date_start'] = date_start;
    request_builder_array['date_end'] = date_end;
    predefined_report_bulk_order['date_start'] = date_start;
    predefined_report_bulk_order['date_end'] = date_end;
    
    executeRequest(predefined_report_bulk_order);
    $("#wizard-table-title-big").empty();
    $("#wizard-table-title-small").empty();
    $("#wizard-table-title-big").html('Bulk Order');
}


/*
 * Donalds Report
 */
$("[data-predefined_report='donalds_report']").click(function(){    
    doPredefinedReportDonaldsReport();
});

function doPredefinedReportDonaldsReport(){
    
    unsetTable();
    request_builder_array = [];
    limit = 250000;
    current_report = 'donalds_report';
    
    var date_start  = picker_selected_start_date.split('T')[0];
    var date_end    = picker_selected_end_date.split('T')[0];
    
    grid_columns = predefined_report_donalds_report.columns_order;
        
    request_builder_array = predefined_report_donalds_report;
    request_builder_array['date_start'] = date_start;
    request_builder_array['date_end'] = date_end;
    predefined_report_donalds_report['date_start'] = date_start;
    predefined_report_donalds_report['date_end'] = date_end;
    
    executeRequest(predefined_report_donalds_report);
    $("#wizard-table-title-big").empty();
    $("#wizard-table-title-small").empty();
    $("#wizard-table-title-big").html('Donald\'s Report');
}

/** -------------------------------
 *  DISPLAY PREDEFINED REPORTS HELP
 *  ------------------------------- */
$('[data-predefined-report-help]').click(function() {
        var predefined_report = $(this).data('predefined-report');
        var predefined_report_name = $(this).data('predefined-report-help');
        setPredefinedReportHelp(predefined_report,predefined_report_name);
});

/** -------------------
 *  CHANGE PICKER VALUE
 *  ------------------- */

$(".daterangepicker .ranges ul li").on('click',function(){
    window['picker_selected_' + $(this).text().toLowerCase().replace(/ /g,"_")]();
    refreshReportPeriod();
});

$(".range_inputs .applyBtn").click(function(){
    var start_date = document.getElementsByName("daterangepicker_start")[0].value;
    var end_date = document.getElementsByName("daterangepicker_end")[0].value;
    
    picker_selected_fixed_date(start_date,end_date);
    refreshReportPeriod();
});

function refreshReportPeriod(){
    if(current_report == 'bulk_order'){doPredefinedReportBulkOrder();};
    if(current_report == 'donalds_report'){doPredefinedReportDonaldsReport();};
    if(current_report == 'fraud_detection_order'){doPredefinedReportFraudDetectionOrder();};
}

/** +-----------+
 *  | DESTROYER |
 *  +-----------+
 *
 *  DESCRIPTION:
 *  Clear all variables and destroy curent table instance
 *      
 */
function unsetTable(){
    $("#records_number_container").empty(); 
    $("#wizard_sql_text").empty(); 
        
    
    if(!jQuery.isEmptyObject(grid)){
        //$('#table_portlet_colapse').click();
        grid.destroy();
        grid_export = [];
        Slick.Data.warnings_id = {};
        request_builder_array = [];
        request_string = '';
        grid_data = new Array();
        grid_columns = new Array();
        grid_columns_render = new Array();        
        number_of_records = 0;
        total_number_of_records = 0;
        page = 0;
        sql = new Array();
        request = new Array();        
        $("#wizard_table_container").empty();
        $("#wizard_table_container").append('<div id="wizard_table" style="width:100%; height: 80vh; overflow: hidden; border-right: 1px solid #B8B8B8; border-bottom: 1px solid #B8B8B8;"></div>');
    }
}

/** +-----------------------------------+
 *  | PREDEFINED REPORTS HELP GENERATOR |
 *  +-----------------------------------+
 *
 *  DESCRIPTION:
 *  Create help tips for predefined reports
 *      
 */
var predefined_report_help_body = '\n\
<div class="row">\n\
	<div class="col-md-4"><p class="m--font-boldest">Description:</p></div>\n\
	<div class="col-md-8"><p id="report-help-description"></p></div>\n\
</div>\n\
<div class="row">\n\
	<div class="col-md-4"><p class="m--font-boldest">Columns:</p></div>\n\
	<div class="col-md-8"><p id="report-help-columns"></p></div>\n\
</div>\n\
<div class="row">\n\
	<div class="col-md-12"><p class="m--font-boldest">Criteria:</p></div>\n\
	<div class="col-md-4"><p>Vendor - </p></div>\n\
	<div class="col-md-8"><p id="report-criteria-vendor"></p></div>\n\
	<div class="col-md-4"><p>Delivery Status - </p></div>\n\
	<div class="col-md-8"><p id="report-criteria-order_delivery_status"></p></div>\n\
	<div class="col-md-4"><p>Order Is Refunded - </p></div>\n\
	<div class="col-md-8"><p id="report-criteria-order_is_refunded"></p></div>\n\
	<div class="col-md-4"><p>Fulfillment Alert Status - </p></div>\n\
	<div class="col-md-8"><p id="report-criteria-order_alert_status"></p></div>\n\
</div>\n\
<div class="row">\n\
	<div class="col-md-12"><p class="m--font-boldest">Period:</p></div>\n\
	<div class="col-md-4"><p>Date Start - </p></div>\n\
	<div class="col-md-8"><p id="report-criteria-date_start"></p></div>\n\
	<div class="col-md-4"><p>Date End - </p></div>\n\
	<div class="col-md-8"><p id="report-criteria-date_end"></p></div>\n\
</div>';

function setPredefinedReportHelp(predefined_report,predefined_report_name) {
    
        $("#modal_lg_title").empty();
        $("#modal_lg_body").empty();
        $("#modal_lg_title").append('');
        $("#modal_lg_body").append(predefined_report_help_body);
        $("#modal_lg").modal('show');
        
        /*
         * Insert Title
         */
        var t = $('[data-predefined_report="' + predefined_report + '"]').text();
        $("#modal_lg_title").append(t);
        
        
        /*
         * Call To Predefined Report
         */
        
        /*
         * Insert Columns Names
         */
        var c = window[predefined_report_name].columns_order;
        var c_string = '';
        var s = '';
        c.forEach(function(v){
            var value = columns_settings[v]['name'];
            c_string += s + value;
            s = ', ';
        });
        $("#report-help-columns").append(c_string);
        
        
        /*
         * Insert Vendor Criteria
         */
        var c = window[predefined_report_name].vendor_id;
        var c_string = '';
        var s = '';
        c.forEach(function(v){
            var value = columns_settings['vendor_id']['label'][v];
            c_string += s + value;
            s = ', ';
        });
        $("#report-criteria-vendor").append(c_string);
        
        /*
         * Insert Delivery Status Criteria
         */
        var c = window[predefined_report_name].order_delivery_status;
        var c_string = '';
        var s = '';
        c.forEach(function(v){
            var value = columns_settings['order_delivery_status']['label'][v];
            c_string += s + value;
            s = ', ';
        });
        $("#report-criteria-order_delivery_status").append(c_string);
        
        
        /*
         * Insert Refund Criteria
         */
        var c = window[predefined_report_name].order_is_refunded;
        var c_string = '';
        var s = '';
        c.forEach(function(v){
            var value = columns_settings['order_is_refunded']['label'][v];
            c_string += s + value;
            s = ', ';
        });
        $("#report-criteria-order_is_refunded").append(c_string);
        
        /*
         * Insert Order Alerts Criteria
         */
        var c = window[predefined_report_name].order_alert_status;
        var c_string = '';
        var s = '';
        c.forEach(function(v){
            var value = columns_settings['order_alert_status']['label'][v];
            c_string += s + value;
            s = ', ';
        });
        $("#report-criteria-order_alert_status").append(c_string);
                
        /*
         * Check if additional information are added
         */
        if (typeof window[predefined_report_name + '_add'] !== 'undefined') {
            /*
            * Insert Date Start Criteria
            */
           var c = window[predefined_report_name + '_add'].date_start;
           $("#report-criteria-date_start").append(c);
           
           /*
            * Insert Date End Criteria
            */
           var c = window[predefined_report_name + '_add'].date_end;
           $("#report-criteria-date_end").append(c);
           /*
            * Insert Description
            */
           var c = window[predefined_report_name + '_add'].description;
           $("#report-help-description").append(c);
        }
        
};

/** +-----------------------------+
 *  | GET HTTP PREDEFINED REPORTS |
 *  +-----------------------------+
 *
 *  DESCRIPTION:
 *  Allowed to add reports to favorites
 *      
 */
function checkHttpGet(){
    if(typeof window.location.toString().split("?")[1] !== 'undefined' ){
        decodeHttpGet();
        return 1;
    } else {
        return 0;
    }
    
}

function decodeHttpGet() {
    var c = {};
    var get_url = decodeURIComponent(window.location.toString().split("?")[0]);
    var get_params = decodeURIComponent(window.location.toString().split("?")[1]);
    
    var a = get_params.split("&");
    $.each(a,function(k,v){        
        var b = v.split("=");
        
        c[b[0]] = b[1].split(",");
    });
    
    var t_tables_columns            = c['tables_columns'];
    var t_vendor_id                 = c['vendor_id'];
    var t_order_delivery_status     = c['order_delivery_status'];
    var t_order_is_refunded         = c['order_is_refunded'];
    var t_order_alert_status        = c['order_alert_status'];
    var t_columns_order             = c['columns_order'];
    var t_range_type                = c['range_type'];
    var t_range_value_start         = c['range_value_start'];
    var t_range_value_end           = c['range_value_end'];
    
    var dates       = getPredefinedPeriods(t_range_type,t_range_value_start,t_range_value_end);
    var t_start     = dates['t_start'];
    var t_end       = dates['t_end'];
    
    $('#m_dashboard_daterangepicker').data('daterangepicker').setStartDate(t_start);
    $('#m_dashboard_daterangepicker').data('daterangepicker').setEndDate(t_end);
    
    
    var predefined_report_get = {
                                    "tables_columns": t_tables_columns,
                                    "vendor_id": t_vendor_id,
                                    "order_delivery_status": t_order_delivery_status,
                                    "order_is_refunded": t_order_is_refunded,
                                    "order_alert_status": t_order_alert_status,
                                    "date_start": t_start,
                                    "date_end": t_end,
                                    "columns_order": t_columns_order
                                };
    unsetTable();
    request_builder_array = [];
    request_builder_array = predefined_report_get;
    executeRequest(predefined_report_get); 
}

function encodeHttpGet(range_type,range_value_start,range_value_end){
    var get_url = window.location.toString().split("?")[0];
    var get_tables_columns          = request_builder_array['tables_columns'];
    var get_vendor_id               = request_builder_array['vendor_id'];
    var get_order_delivery_status   = request_builder_array['order_delivery_status'];
    var get_order_is_refunded       = request_builder_array['order_is_refunded'];
    var get_order_alert_status      = request_builder_array['order_alert_status'];
    var get_range_type              = range_type;
    var get_range_value_start       = range_value_start;
    var get_range_value_end         = range_value_end;
    var get_tmp_columns_order       = grid.getColHeader();
    var get_columns_order           = [];
    
    get_tmp_columns_order.forEach(function(tmp_v){
        var k = 0;
        $.each(columns_settings,function(k,v){
            if(tmp_v == v['name']){
                get_columns_order.push(k);
            }
        });
    });
    var get =   '?tables_columns='          +get_tables_columns+
                '&vendor_id='               +get_vendor_id+
                '&order_delivery_status='   +get_order_delivery_status+
                '&order_is_refunded='       +get_order_is_refunded+
                '&order_alert_status='      +get_order_alert_status+
                '&columns_order='           +get_columns_order+
                '&range_type='              +get_range_type+
                '&range_value_start='       +get_range_value_start+
                '&range_value_end='         +get_range_value_end;
    var url = get_url+get;
    history.pushState({},"New Report",url)
}

/** +---------+
 *  | METHODS |
 *  +---------+
 *
 *  DESCRIPTION:
 *  Often used functions
 *      • DATE PERIOD FOR PREDEFINED REPORTS
 *      • SQL TEXT
 *      
 */

/** ----------------------------------
 *  DATE PERIOD FOR PREDEFINED REPORTS
 *  ---------------------------------- */

function getPredefinedPeriods(t_range_type,t_range_value_start,t_range_value_end){
            
    var t_start;
    var t_end;
    
    if(t_range_type == 'picker_selected_fixed_date'){
        t_start = getDateMinusDays(t_range_value_start);
        t_end = getDateMinusDays(t_range_value_end);
    } else {
        window[t_range_type]();
        t_start = picker_selected_start_date;
        t_end = picker_selected_end_date;
    }    
    
    return {
                't_start':t_start,
                't_end':t_end
            };    
}

/** --------
 *  SQL TEXT
 *  -------- */

function setSqlText(){
    var i = 1;
    var str = '';
    sql.forEach(function(v){
        str += '<h6>Query '+i+'</h6><code>' + v + '</code><br>';
        i++;
    });
    
    return str;
}

/** +-----------------------------------------+
 *  | DOCUMENT READY AND RUN DEFAULT SETTINGS |
 *  +-----------------------------------------+
 *
 *  DESCRIPTION:
 *  Run default settings and other functions when document ready
 *      
 */

/*
 * Execute controlls default setings
 */

$('.m-checkbox').each(function(){
    var el = $(this);
    $('[data-type]',el).each(function(){         
        var type = $(this).data('type');
        
        if(type == 'criteria'){            
            var criteria = $(this).data('criteria');
            var criteria_id = $(this).data('criteria_id');

            if (typeof columns_settings[criteria]['help_c'] !== 'undefined' && columns_settings[criteria]['help_c'] !== null) {
                var content = columns_settings[criteria]['help_c'][criteria_id];
                var title = columns_settings[criteria]['help_t'];
                var c_help = '<button id="help_' + type + '_' + criteria + '_' + criteria_id + '" style="margin-left:20px; height:1.5em; width:1.5em" class="btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill btn-sm" data-toggle="m-popover" data-trigger="focus" title="" data-html="true" data-content="' + content + '" data-original-title="' + title + '">\n\
                                <i class="fa fa-question" style="font-size:1em"></i>\n\
                </button>';            
                el.append(c_help);
            };
        };
        
        
        
        if(type == 'table'){
            var table = $(this).data('table');
            var column = $(this).data('column');

            if (typeof columns_settings[column]['description'] !== 'undefined' && columns_settings[column]['description'] !== '') {
                var content = columns_settings[column]['description'];
                var title = columns_settings[column]['name'];
                var c_help = '<button id="help_' + type + '_' + table + '_' + column + '" style="margin-left:20px; height:1.5em; width:1.5em" class="btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill btn-sm" data-toggle="m-popover" data-trigger="focus" title="" data-html="true" data-content="' + content + '" data-original-title="' + title + '">\n\
                                <i class="fa fa-question" style="font-size:1em"></i>\n\
                </button>';            
                el.append(c_help);
            };
        };
    });
});

/*
 * Seting default statuses of checkboxes.
 */
function setDefaultCheckbox(){
    default_checkbox_criteria.forEach(function(item){
        $("[data-" + item[0] + "='" + item[1] + "'][data-" + item[2] + "='" + item[3] + "']").prop('checked', item[4]);
    });
    default_checkbox_tables.forEach(function(item){
        $("[data-" + item[0] + "='" + item[1] + "'][data-" + item[2] + "='" + item[3] + "']").prop('checked', item[4]);
    });
}

function clearReportCheckbox(){
    $('[data-type="criteria"]').each(function(){
        $(this).prop('checked',false);
    });
    $('[data-type="table"]').each(function(){
        $(this).prop('checked',false);
    });
}

function setReportCheckbox(request){
    request.vendor_id.forEach(function(v){
        $('[data-criteria_id="' + v +'"][data-criteria="vendor_id"]').prop('checked', true);
    });
    request.order_delivery_status.forEach(function(v){
        $('[data-criteria_id="' + v +'"][data-criteria="order_delivery_status"]').prop('checked', true);
    });
    request.order_is_refunded.forEach(function(v){
        $('[data-criteria_id="' + v +'"][data-criteria="order_is_refunded"]').prop('checked', true);
    });
    request.order_alert_status.forEach(function(v){
        $('[data-criteria_id="' + v +'"][data-criteria="order_alert_status"]').prop('checked', true);
    });
    request.tables_columns.forEach(function(v){
        var i = v.split(".");
        $('[data-table="' + i[0] +'"][data-column="' + i[1] + '"][data-type="table"]').prop('checked', true);
    });
}

/** -----------------------------
 *  RUN DEFAULT PREDEFINED REPORT
 *  ----------------------------- */

/*
 * Run Fraud Order as Default
 */
function runDefaultReport(){
    unsetTable();
    request_builder_array = [];
    
    var dates = {'t_start':getDateMinusDays(1),'t_end':DATE_TODAY};
    var date_start  = dates['t_start'];
    var date_end    = dates['t_end'];
    
    grid_columns = predefined_report_fraud_detection_order.columns_order;
    
    request_builder_array = predefined_report_fraud_detection_order;
    request_builder_array['date_start'] = date_start;
    request_builder_array['date_end'] = date_end;
    predefined_report_fraud_detection_order['date_start'] = date_start;
    predefined_report_fraud_detection_order['date_end'] = date_end;
    
    executeRequest(predefined_report_fraud_detection_order);
    $("#wizard-table-title-big").empty();
    $("#wizard-table-title-small").empty();
    $("#wizard-table-title-big").html('Fraud Detection Order');  
    
};

/*
 * Document ready.
 */
$( document ).ready(function() {    
    //setDefaultCheckbox();
    clearReportCheckbox();
    if(checkHttpGet() === 0){  
        runDefaultReport();
    };
});


