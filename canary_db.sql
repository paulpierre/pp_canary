
/** +-----------------------------------------------------------+
 *  | Canary - Shipments Tracker for Shopify by paul@pixel6.net |
 *  +-----------------------------------------------------------+
  by Paul Pierre


databasename - canary_db
 */

DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `fulfillments`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `crawls`;
DROP TABLE IF EXISTS `proxies`;
DROP TABLE IF EXISTS `sys`;
DROP TABLE IF EXISTS `tracking`;
DROP TABLE IF EXISTS `api_stats`;



/*======
  orders
  ======
*/

CREATE TABLE `orders`(
 `order_id` int(10) NOT NULL AUTO_INCREMENT, /* This is the table index ID for the row */
 `order_receipt_id` varchar(255) NOT NULL, /* This is the user-friendly order identifier in shopify also known as receipt ex.: #OMGT229490   */
 `order_shopify_id` varchar(255) NOT NULL, /* This is the numeric identifier for the order within shopify, when you look at an order in the admin, you well see this number in the URL bar */

 `order_fulfillment_status` varchar(255) NOT NULL, /* whether an order has been fulfilled, e.g. a corresponding fulfillment row has been added */
 `order_delivery_status` int(3) NOT NULL, /* the deliver status of a particular order. check constants.php to know what these are
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


 */
 `order_alert_status` int(3) NOT NULL, /* when we interpret an delivery status for an order/fulfillment, we check conditions
  to see whether we should flag this particular order so Fy is aware of any issues:

  //Order status flags in the system
define('NOTIFICATION_STATUS_NONE',0);                //There is no issue
define('NOTIFICATION_STATUS_RESOLVED',1);            //Previously there was an issue, now it is resolved
define('NOTIFICATION_STATUS_EXTENDED_NOT_FOUND',2);  //Courier perhaps lost the package
define('NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT',3); //Item is likely stuck in customs
define('NOTIFICATION_STATUS_CUSTOMER_PICKUP',4);     //Email customers to pick up their item at the post office
define('NOTIFICATION_STATUS_DELIVERY_FAILURE',5);    //Email customers to call their local post office
define('NOTIFICATION_STATUS_ALERT_CUSTOMS',6);       //Inform suppliers that items was likely rejected by customs
  */
 `order_is_ocu` int(1) NOT NULL, /* This is not important, but this keeps track if a user is (1) or not (0) an order from a shopify
  app called one click upsell. */
 `order_is_refunded` int(1) NOT NULL, /* Whether an order is refunded, we get this information from shopify */
 `order_total_cost` float(5,2) NOT NULL, /* the cost of all the items put together */
 `order_tags` VARCHAR(500) NOT NULL, /* tags for this order, ignore not necessary for us to use for now */

 `order_gateway` int(3) NOT NULL, /* not important but the payment gateway that was used, determined by constants.php:
  //Gateway providers
define('GATEWAY_PROVIDER_UNKNOWN',0);
define('GATEWAY_PROVIDER_STRIPE',1);
define('GATEWAY_PROVIDER_PAYPAL',2);
define('GATEWAY_PROVIDER_SHOPIFY_PAYMENTS',3);
*/

 /*==== THE REST ARE SELF-EXPLANTORY. ==== */
 `order_customer_email` varchar(255) NOT NULL,
 `order_customer_fn` varchar(255) NOT NULL, /* first name */
 `order_customer_ln` varchar(255) NOT NULL, /* last name */
 `order_customer_address1` varchar(255) NOT NULL,
 `order_customer_address2` varchar(255) NOT NULL,
 `order_customer_city` varchar(255) NOT NULL,
 `order_customer_province` varchar(255) NOT NULL,
 `order_customer_zip` varchar(10) NOT NULL,
 `order_currency` varchar(5) NOT NULL,
 `order_customer_country` varchar(255) NOT NULL,
 `order_customer_phone` varchar(255) NOT NULL,

 /*== NEW 02-06-2018 (ADDING BILLING) ==*/

 `order_customer_billing_fn` varchar(255) NOT NULL, /* first name */
 `order_customer_billing_ln` varchar(255) NOT NULL, /* last name */
 `order_customer_billing_address1` varchar(255) NOT NULL,
 `order_customer_billing_address2` varchar(255) NOT NULL,
 `order_customer_billing_city` varchar(255) NOT NULL,
 `order_customer_billing_province` varchar(255) NOT NULL,
 `order_customer_billing_zip` varchar(10) NOT NULL,
 `order_customer_billing_country` varchar(255) NOT NULL,
 `order_customer_billing_phone` varchar(255) NOT NULL,

 /*====================*/
 `order_topen` DATETIME NOT NULL,
 `order_tclose` DATETIME NOT NULL,
 `order_tmodified` DATETIME NOT NULL,
 `order_tcreate` DATETIME NOT NULL,
 PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*============
  fulfillments
  ============ */

CREATE TABLE `fulfillments`(
 `fulfillment_id` int(10) NOT NULL AUTO_INCREMENT,
 `order_id` int(10) NOT NULL,
 `order_shopify_id` varchar(255) NOT NULL,
 `fulfillment_shopify_id` varchar(255)  NOT NULL,

 `fulfillment_shipment_status` varchar(255) NOT NULL,
 `fulfillment_delivery_status` int(3) NOT NULL,
 `fulfillment_is_tracking` int(1) NOT NULL,
 `fulfillment_alert_status` int(3) NOT NULL,
 `fulfillment_vendor_id` int(3) NOT NULL,


 `fulfillment_tracking_number` varchar(255) NOT NULL,
 `fulfillment_tracking_number_tcreate` DATETIME NOT NULL,
 `fulfillment_tracking_company` varchar(255) NOT NULL,
 `fulfillment_tracking_url` varchar(500) NOT NULL,
 `fulfillment_tracking_last_status_text` varchar(700) NOT NULL,
 `fulfillment_tracking_last_date` DATETIME NOT NULL,

 `fulfillment_tracking_country_from` varchar(3) NOT NULL,
 `fulfillment_tracking_country_to` varchar(3) NOT NULL,
 `fulfillment_tracking_carrier_from` varchar(10) NOT NULL,
 `fulfillment_tracking_carrier_to` varchar(10) NOT NULL,

 `fulfillment_status_delivered_tcreate` DATETIME NOT NULL,
 `fulfillment_status_confirmed_tcreate` DATETIME NOT NULL,
 `fulfillment_status_in_transit_tcreate` DATETIME NOT NULL,
 `fulfillment_status_out_for_delivery_tcreate` DATETIME NOT NULL,
 `fulfillment_status_failure_tcreate` DATETIME NOT NULL,
 `fulfillment_status_not_found_tcreate` DATETIME NOT NULL,
 `fulfillment_status_customer_pickup_tcreate` DATETIME NOT NULL,
 `fulfillment_status_alert_tcreate` DATETIME NOT NULL,
 `fulfillment_status_expired_tcreate` DATETIME NOT NULL,




 `fulfillment_topen` DATETIME NOT NULL,

 `fulfillment_tcheck` DATETIME NOT NULL,

 `fulfillment_tmodified` DATETIME NOT NULL,
 `fulfillment_tcreate` DATETIME NOT NULL,

 PRIMARY KEY (`fulfillment_id`),
 FOREIGN KEY (`order_id`) REFERENCES `orders`(order_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*=====
  items
  ===== */

CREATE TABLE `items`(
 `item_id` int(10) NOT NULL AUTO_INCREMENT,
 `order_id` int(10) NOT NULL,
 `order_shopify_id` varchar(255) NOT NULL,
 `item_shopify_id` varchar(255)  NOT NULL,
 `item_quantity` int(5) NOT NULL,
 `item_sku` varchar(100) NOT NULL,
 `item_shopify_product_id` varchar(255) NOT NULL,
 `item_shopify_variant_id` varchar(255) NOT NULL,
 `item_name` varchar(255) NOT NULL,
 `item_price` float(5,2) NOT NULL,
 `item_is_fulfilled` int(1) NOT NULL,
 `item_is_refunded` int(1) NOT NULL,
 `item_refund_tcreate` DATETIME NOT NULL,
 `item_tmodified` DATETIME NOT NULL,
 `item_tcreate` DATETIME NOT NULL,
  PRIMARY KEY (`item_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*=======
  proxies
  ======= */

CREATE TABLE `proxies`(
 `proxy_id` int(10) NOT NULL AUTO_INCREMENT,
 `proxy_ip` VARCHAR(25) NOT NULL,
 `proxy_port` int(10) NOT NULL,
 `proxy_is_enabled` int(1) NOT NULL,
 `proxy_tmodified` DATETIME NOT NULL,
 `proxy_tcreate` DATETIME NOT NULL,
 PRIMARY KEY (`proxy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*======
  crawls
  ====== */

CREATE TABLE `crawls`(
 `crawl_id` int(10) NOT NULL AUTO_INCREMENT,
 `fulfillment_id` int(10) NOT NULL,
 `order_id` int(10) NOT NULL,
 `proxy_id` int(10) NOT NULL,
 `crawl_result` int(5) NOT NULL,

 `crawl_delivery_status` int(3) NOT NULL,
 `crawl_previous_delivery_status` int(3) NOT NULL,

 `crawl_tracking_company` int(10) NOT NULL,
 `crawl_tstart` TIMESTAMP NOT NULL,
 `crawl_tfinish` TIMESTAMP NOT NULL,
 `crawl_tcreate` TIMESTAMP NOT NULL,

 PRIMARY KEY (`crawl_id`),
 FOREIGN KEY (`order_id`) REFERENCES `orders`(order_id),
 FOREIGN KEY (`fulfillment_id`) REFERENCES `fulfillments`(fulfillment_id),
 FOREIGN KEY (`proxy_id`) REFERENCES `proxies`(proxy_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*===
  sys
  === */
CREATE TABLE `sys`(
 `key` varchar(255) NOT NULL,
 `value` varchar(500) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*========
  tracking
  ======== */

CREATE TABLE `vendor_tracking`(
 `tracking_number` varchar(50) NOT NULL,
 `fulfillment_id` int(10) NOT NULL,
 `order_id` int(10) NOT NULL,
 `vendor_id` int(10) NOT NULL,
 `order_receipt_id` varchar(255) NOT NULL,
 `tracking_status` int(3) NOT NULL, /* 0=no matching, 2=matched tracking # to order_id and fulfillment_id */
 `tracking_tmodified` TIMESTAMP NOT NULL,
 `tracking_tcreate` TIMESTAMP NOT NULL,

 PRIMARY KEY (`tracking_number`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(order_id),
 FOREIGN KEY (`fulfillment_id`) REFERENCES `fulfillments`(fulfillment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*=========
  api_stats
  ========= */

CREATE TABLE `api_stats`(
 `api_id` int(3) NOT NULL,
 `call_count` int(10) NOT NULL,
 `call_tcreate` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
 `call_tmodified` TIMESTAMP DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*==================
  vendor_sheet_stats
  ================== */

CREATE TABLE `vendor_file_stats`(
 `sheet_id` int(10) NOT NULL AUTO_INCREMENT,
 `vendor_id` int(10) NOT NULL,
 `file_name` varchar(255) NOT NULL UNIQUE,
 `file_status` int(3) NOT NULL, /* 0=not scanned 1=success 2=warning 3=fatal error*/
 `file_success_count` int(3) NOT NULL,
 `file_total_rows` int(20) NOT NULL,
 `file_error_rows` int(20) NOT NULL,
 `file_error_json` varchar(500) NOT NULL,
 `file_tcreate` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
 `file_tmodified` TIMESTAMP DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`sheet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*==================
  vendor_file_config
  ================== */

CREATE TABLE `vendor_file_config`(
 `config_id` int(10) NOT NULL AUTO_INCREMENT,
 `vendor_id` int(10) NOT NULL,
 `vendor_sheet_col_type` int(3) NOT NULL,
 `vendor_sheet_col_data` int(3) NOT NULL,
 `vendor_sheet_col_condition` int(3) NOT NULL,
 `vendor_sheet_col_regex` int(3) NOT NULL,
 `config_tcreate` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
 `config_tmodified` TIMESTAMP DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*=======
  vendors
  ======= */
CREATE TABLE `vendors`(
 `vendor_id` int(10) NOT NULL AUTO_INCREMENT,
 `vendor_status` int(3) NOT NULL,
 `vendor_name` varchar(255) NOT NULL,
 `vendor_display_name` varchar(255) NOT NULL,
 `vendor_tcreate` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
 `vendor_tmodified` TIMESTAMP DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*=======
  indexes
  ======= */
CREATE INDEX idx_fulfillments_fulfillment_tracking_number  ON canary_db2.fulfillments (fulfillment_tracking_number) COMMENT '' ALGORITHM DEFAULT LOCK DEFAULT;
CREATE INDEX idx_orders_order_receipt_id  ON canary_db2.orders (order_receipt_id) COMMENT '' ALGORITHM DEFAULT LOCK DEFAULT;
CREATE INDEX idx_orders_order_tcreate  ON canary_db2.orders (order_tcreate) COMMENT '' ALGORITHM DEFAULT LOCK DEFAULT;