<?php
/**
 * SQL Queries
 * User: paulpierre
 * Date: 11/22/17
 * Time: 4:39 PM
 */

/**
 *  NOTE: You'll need to run a string replacement for the follow macros
 *  {WHERE} - Custom where clause
 *  {ID} - ID of the thing you'd like to lookup
 *  {DATE_RANGE} - Date range look up
 *  {COLUMNS} - Custom column lookup clause
 *
 */


/** ==========================
 *  VENDOR SUMMARY PERFORMANCE
 *  ==========================
 */



define('SQL_REPORT_VENDOR_DELIVERY_SUMMARY_BY_VENDOR_ID','
	SELECT
		F.fulfillment_delivery_status as status,
		count(F.fulfillment_delivery_status) as count

		FROM orders O
		INNER JOIN fulfillments F ON F.order_id=O.order_id
		LEFT JOIN vendor_tracking V ON
			O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
		WHERE
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
            F.fulfillment_tracking_number !="" AND /* Has tracking number */
            F.fulfillment_tcheck !="0000-00-00 00:00:00" AND
            V.vendor_id={ID}
            {DATE_RANGE}
		GROUP BY fulfillment_delivery_status
');


define('SQL_REPORT_VENDOR_DELIVERY_STATUS_BY_VENDOR_ID_AND_DELIVERY_STATUS','
	SELECT
		V.vendor_id,
		O.order_receipt_id as receipt,
		O.order_topen,
		I.item_name as name,
		I.item_price as price,
		I.item_shopify_variant_id as variant,
		I.item_sku as sku,
		I.item_quantity as qty,
		O.order_customer_fn as fn,
		O.order_customer_ln as ln,
		O.order_customer_address1 as address1,
		O.order_customer_address2 as address2,
		O.order_customer_city as city,
		O.order_customer_province as province,
		O.order_customer_zip as zip,
		O.order_customer_country as country,
		O.order_customer_phone as phone,
		O.order_customer_email as email,
		
		fulfillment_status_delivered_tcreate,
		fulfillment_status_confirmed_tcreate,
		fulfillment_status_in_transit_tcreate,
		fulfillment_status_out_for_delivery_tcreate,
		fulfillment_status_failure_tcreate,
		fulfillment_status_not_found_tcreate,
		fulfillment_status_customer_pickup_tcreate,
		fulfillment_status_alert_tcreate,
		fulfillment_status_expired_tcreate,
		
		F.fulfillment_tracking_number as tracking,
		F.fulfillment_delivery_status as delivery_status,
		F.fulfillment_tracking_last_status_text as last_status,
		F.fulfillment_tracking_last_date as last_date,
		F.fulfillment_tcheck

		FROM orders O
		INNER JOIN fulfillments F ON F.order_id=O.order_id
		LEFT JOIN items I ON
			O.order_id = I.order_id
		LEFT JOIN vendor_tracking V ON
			O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
		WHERE
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
            F.fulfillment_tracking_number !="" AND /* Has tracking number */
            F.fulfillment_tcheck !="0000-00-00 00:00:00" AND
            F.fulfillment_delivery_status={STATUS} AND
            V.vendor_id={ID}
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');
//TODO: WE DONT REALLY NEED THIS REPORT, YOU CAN DO THE SAME WITH THE ABOVE QUERY
define('SQL_REPORT_VENDOR_DELIVERED_STATUS_BY_VENDOR_ID_LIST_ORDERS','
	SELECT
		V.vendor_id,
		O.order_receipt_id as receipt,
		O.order_topen,
		I.item_name as name,
		I.item_price as price,
		I.item_shopify_variant_id as variant,
		I.item_sku as sku,
		I.item_quantity as qty,
		O.order_customer_fn as fn,
		O.order_customer_ln as ln,
		O.order_customer_address1 as address1,
		O.order_customer_address2 as address2,
		O.order_customer_city as city,
		O.order_customer_province as province,
		O.order_customer_zip as zip,
		O.order_customer_country as country,
		O.order_customer_phone as phone,
		O.order_customer_email as email,
		F.fulfillment_tracking_number as tracking,
		F.fulfillment_delivery_status as delivery_status,
		F.fulfillment_tracking_last_status_text as last_status,
		F.fulfillment_tracking_last_date as last_date,
		F.fulfillment_tracking_delivered_tcreate,
		F.fulfillment_tcheck

		FROM orders O
		INNER JOIN fulfillments F ON F.order_id=O.order_id
		LEFT JOIN items I ON
			O.order_id = I.order_id
		LEFT JOIN vendor_tracking V ON
			O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
		WHERE
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
            F.fulfillment_tracking_number !="" AND /* Has tracking number */
            F.fulfillment_tcheck !="0000-00-00 00:00:00" AND
            F.fulfillment_delivery_status=4 AND
            V.vendor_id={ID}
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');

define('SQL_REPORT_DELIVERY_STATUS_SUMMARY_BY_VENDOR','
	SELECT
		V.vendor_id,
		F.fulfillment_delivery_status as status,
		count(F.fulfillment_delivery_status) as count

		FROM orders O
		INNER JOIN fulfillments F ON F.order_id=O.order_id
		LEFT JOIN vendor_tracking V ON
			O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
		WHERE
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
            F.fulfillment_tracking_number !="" AND /* Has tracking number */
            F.fulfillment_tcheck !="0000-00-00 00:00:00" /* We have checked the tracking status of this already */
            {DATE_RANGE}
            
		GROUP BY V.vendor_id,F.fulfillment_delivery_status
		
    
');


define('SQL_REPORT_PACKAGE_STUCK_IN_CUSTOMS_BY_VENDOR_ID_LIST_ORDERS','
	SELECT
		V.vendor_id,
		O.order_receipt_id,
		O.order_topen,
		I.item_name,
		I.item_price,
		I.item_shopify_variant_id,
		I.item_sku,
		I.item_quantity,
		O.order_customer_fn,
		O.order_customer_ln,
		O.order_customer_address1,
		O.order_customer_address2,
		O.order_customer_city,
		O.order_customer_province,
		O.order_customer_zip,
		O.order_customer_country,
		O.order_customer_phone,
		O.order_customer_email,
		F.fulfillment_tracking_number,
		F.fulfillment_tracking_last_status_text,
		F.fulfillment_delivery_status,
		F.fulfillment_tracking_last_date,
		F.fulfillment_status_in_transit_tcreate, /* When we last saw this in transit via API */
		F.fulfillment_tcheck

		FROM orders O
		INNER JOIN fulfillments F ON F.order_id=O.order_id
		LEFT JOIN items I ON
			O.order_id = I.order_id
		LEFT JOIN vendor_tracking V ON
			O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
		WHERE
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
            F.fulfillment_delivery_status=2 AND	/* is NOT_FOUND status */
            F.fulfillment_tracking_number != "" AND /* Has tracking number */
            F.fulfillment_tcheck !="0000-00-00 00:00:00" AND /* We have checked the tracking status of this already */
            F.fulfillment_tcheck > DATE_ADD(F.fulfillment_tracking_last_date, INTERVAL 7 DAY) AND
            V.vendor_id={ID}
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');


define('SQL_REPORT_COURIER_LOST_PACKAGE_BY_VENDOR_ID_LIST_ORDERS','
	SELECT
		V.vendor_id,
		O.order_receipt_id,
		O.order_topen,
		I.item_name,
		I.item_price,
		I.item_shopify_variant_id,
		I.item_sku,
		I.item_quantity,
		O.order_customer_fn,
		O.order_customer_ln,
		O.order_customer_address1,
		O.order_customer_address2,
		O.order_customer_city,
		O.order_customer_province,
		O.order_customer_zip,
		O.order_customer_country,
		O.order_customer_phone,
		O.order_customer_email,
		F.fulfillment_tracking_number,
		F.fulfillment_delivery_status,
		F.fulfillment_tracking_last_status_text,
		F.fulfillment_tracking_last_date,
		F.fulfillment_status_not_found_tcreate,
		F.fulfillment_tcheck

    FROM orders O
    INNER JOIN fulfillments F ON F.order_id=O.order_id
    LEFT JOIN items I ON
        O.order_id = I.order_id
    LEFT JOIN vendor_tracking V ON
        O.order_id = V.order_id AND
        F.fulfillment_id = V.fulfillment_id AND
        O.order_receipt_id = V.order_receipt_id
    WHERE
        O.order_is_refunded =0 AND /* Eliminate refunded orders */
        F.fulfillment_tracking_number != "" AND /* Has tracking number */
        F.fulfillment_tcheck !="0000-00-00 00:00:00" AND /* We have checked the tracking status of this already */
        F.fulfillment_delivery_status=6 AND	/* is NOT_FOUND status */
        F.fulfillment_tcheck > DATE_ADD(F.fulfillment_tracking_last_date, INTERVAL 5 DAY) AND /* If the time elapsed between when we first set the state to NOT FOUND to since we checked it after the first time is more than 5 days, it means we have a problem. Alert the user. */
        V.vendor_id={ID}
        {DATE_RANGE}
        {SORT}
    {LIMIT}
');

define('SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDOR','
	SELECT
		V.vendor_id,
		AVG(DATEDIFF(F.fulfillment_status_delivered_tcreate,F.fulfillment_tracking_number_tcreate)) as days, /* Vendor Average delivery time */
		COUNT(O.order_id) as order_count

		FROM orders O
		INNER JOIN fulfillments F ON F.order_id=O.order_id
		LEFT JOIN items I ON
			O.order_id = I.order_id
		LEFT JOIN vendor_tracking V ON
			O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
		WHERE
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
            F.fulfillment_tracking_number != "" AND /* Has tracking number */
            F.fulfillment_tcheck !="0000-00-00 00:00:00" AND /* We have checked the tracking status of this already */
            F.fulfillment_status_delivered_tcreate !="0000-00-00 00:00:00" AND
            F.fulfillment_delivery_status=4
            {DATE_RANGE}
		GROUP BY V.vendor_id
');


/** ==============
 *  REFUNDS REPORT
 *  ==============
 */

define('SQL_REPORT_REFUNDS_BY_VENDOR_BY_GEO','
    SELECT
        V.vendor_id,
        I.item_name,
        I.item_sku,
        O.order_customer_country,
        SUM(I.item_price) cost,
        SUM(I.item_quantity) quantity
    
    
    FROM orders O
        INNER JOIN fulfillments F ON F.order_id=O.order_id
        LEFT JOIN items I ON
            O.order_id = I.order_id
        LEFT JOIN vendor_tracking V ON
            O.order_id = V.order_id AND
            F.fulfillment_id = V.fulfillment_id AND
            O.order_receipt_id = V.order_receipt_id
        WHERE
            O.order_is_refunded=1
            {DATE_RANGE}
        GROUP BY O.order_customer_country,V.vendor_id
        ORDER BY cost DESC
');

define('SQL_REPORT_REFUNDS_BY_VENDOR_BY_ITEM_SKU','
	SELECT
	V.vendor_id,
	I.item_name,
	I.item_sku,
	SUM(I.item_price) cost,
	SUM(I.item_quantity) quantity


FROM orders O
	INNER JOIN fulfillments F ON F.order_id=O.order_id
	LEFT JOIN items I ON
		O.order_id = I.order_id
	LEFT JOIN vendor_tracking V ON
		O.order_id = V.order_id AND
        F.fulfillment_id = V.fulfillment_id AND
        O.order_receipt_id = V.order_receipt_id
	WHERE
		O.order_is_refunded=1
        {DATE_RANGE}
	GROUP BY I.item_sku,V.vendor_id
	ORDER BY cost DESC
');

define('SQL_REPORT_VENDOR_REFUND_SUMMARY','
		SELECT
		V.vendor_id,
		SUM(O.order_total_cost) as cost,
		count(V.vendor_id) as count

		FROM orders O
		LEFT JOIN vendor_tracking V ON O.order_id = V.order_id
		WHERE
			O.order_is_refunded=1
            {DATE_RANGE}
		GROUP BY V.vendor_id
');


define('SQL_REPORT_REFUNDS_BY_VENDOR_ID','
		SELECT
		V.vendor_id,
		O.order_receipt_id,
		O.order_topen,
		O.order_total_cost,
		O.order_customer_fn,
		O.order_customer_ln,
		O.order_customer_address1,
		O.order_customer_address2,
		O.order_customer_city,
		O.order_customer_province,
		O.order_customer_zip,
		O.order_customer_country,
		O.order_customer_phone,
		O.order_customer_email

		FROM orders O
		LEFT JOIN vendor_tracking V ON O.order_id = V.order_id
		WHERE
			O.order_is_refunded=1 AND
            V.vendor_id = {ID}
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');


define('SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDORS_BY_COUNTRY','
SELECT
			V.vendor_id,
			AVG(DATEDIFF(F.fulfillment_status_delivered_tcreate,F.fulfillment_tracking_number_tcreate)) as days, /* Vendor Average delivery time */
			O.order_customer_country as country,
			COUNT(O.order_id) as order_count

			FROM orders O
			INNER JOIN fulfillments F ON F.order_id=O.order_id
			LEFT JOIN items I ON
				O.order_id = I.order_id
			LEFT JOIN vendor_tracking V ON
				O.order_id = V.order_id AND
                F.fulfillment_id = V.fulfillment_id AND
                O.order_receipt_id = V.order_receipt_id
			WHERE
				O.order_is_refunded =0 AND /* Eliminate refunded orders */
                F.fulfillment_tracking_number != "" AND /* Has tracking number */
                F.fulfillment_tcheck !="0000-00-00 00:00:00" AND /* We have checked the tracking status of this already */
                F.fulfillment_status_delivered_tcreate !="0000-00-00 00:00:00" AND
                F.fulfillment_delivery_status=4
                {DATE_RANGE}
			GROUP BY V.vendor_id, O.order_customer_country
');



/** ======================
 *  SYSTEM SUMMARY REPORTS
 *  ======================
 */


define('SQL_REPORT_CRAWL_TOTAL_PROGRESS','
	SELECT B.tracking_numbers_done/(A.tracking_numbers_left + B.tracking_numbers_done) AS progress, A.tracking_numbers_left as tracking_numbers_left, (A.tracking_numbers_left + B.tracking_numbers_done) as tracking_numbers_total FROM
(
/* This is how many tracking numbers left to crawl today */
    (SELECT
			count(O.order_id) as tracking_numbers_left
			FROM fulfillments F LEFT JOIN orders O
				 on F.order_id = O.order_id
			WHERE (
                (F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 2 DAY) AND
					O.order_topen > DATE_SUB(now(),INTERVAL 6 MONTH)
					) OR
					F.fulfillment_tcheck="0000-00-00 00:00:00" OR
                    F.fulfillment_tracking_last_date="0000-00-00 00:00:00"
				) AND
					F.fulfillment_is_tracking = 1 AND
                    F.fulfillment_tracking_number !="" ) AS A,

		/* This is how many tracking numbers we crawled today */
		(SELECT count(F.fulfillment_id) as tracking_numbers_done FROM fulfillments F WHERE F.fulfillment_tracking_number !="" AND DATE(F.fulfillment_tcheck) = CURDATE()) AS B
	)
');

define('SQL_REPORT_CRAWL_QUEUE','
	SELECT
		O.order_id,
		F.fulfillment_id,
		O.order_topen,
		F.fulfillment_tcheck,
		F.fulfillment_tracking_last_date,
		F.fulfillment_tracking_number

		FROM fulfillments F LEFT JOIN orders O
			 on F.order_id = O.order_id
		WHERE (
            (F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 2 DAY) AND /* Find any tracking numbers that havent been checked in two days or more */
				O.order_topen > DATE_SUB(now(),INTERVAL 6 MONTH)	/* Also, include orders from X months ago (this is for older orders we need to follow up on */
				) OR
				F.fulfillment_tcheck="0000-00-00 00:00:00" OR	/* Or any new order that hasnt been checked */
                F.fulfillment_tracking_last_date="0000-00-00 00:00:00" /* Or any new order that hasnt been checked properly by the tracking API */
			) AND
				F.fulfillment_is_tracking = 1 AND /* Lets make sure these are only orders that we are tracking! */
                F.fulfillment_tracking_number !="" /* Lets also make sure there is an actual tracking number */

		ORDER BY F.fulfillment_tcheck ASC /* Lets order them by last check ASC because we want to get to the items that are 0000-00-00 first! */
');



define('SQL_REPORT_CRAWL_TOTAL_UNCHECKED_TRACKING_NUMBERS','
	SELECT COUNT(*) FROM fulfillments F /* These could be new orders as well */
	WHERE
    (F.fulfillment_tcheck="0000-00-00 00:00:00" OR F.fulfillment_tracking_last_date="0000-00-00 00:00:00") AND
    F.fulfillment_is_tracking = 1 AND
    F.fulfillment_tracking_number !=""
');

define('SQL_WIZARD_ORDERS_FULFILLMENT_ITEMS_BY_VENDOR_ID_AND_DELIVERY_STATUS','
	SELECT  
		{TABLES_COLUMNS} 
                
		FROM orders 
		LEFT JOIN fulfillments ON orders.order_id=fulfillments.order_id  
		LEFT JOIN items ON orders.order_id=items.order_id  
                LEFT JOIN vendor_tracking ON orders.order_id=vendor_tracking.order_id 
		WHERE 
                orders.order_receipt_id <>"" 
            {STATUS}
            {ID}
            {REFUND}
            {ALERT}
            {DATE_RANGE}
            {SORT}
        {LIMIT};
');

define('SQL_WIZARD_17TRACK_FULFILLMENTS','
	SELECT 
                fulfillments.fulfillment_id,fulfillments.order_id,fulfillments.fulfillment_tracking_number,fulfillments.fulfillment_tcheck, orders.order_delivery_status 
            FROM fulfillments 
            LEFT JOIN orders ON fulfillments.order_id=orders.order_id 
            WHERE 
                {ID};
');


define('SQL_WIZARD_TEST_ORDERS','
	SELECT
		*

		FROM orders
		WHERE 
                orders.order_id <>"" 
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');

define('SQL_WIZARD_TEST_FULFILLMENTS','
	SELECT
		*

		FROM fulfillments
		WHERE 
                fulfillments.fulfillment_id <>"" 
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');


define('SQL_WIZARD_TEST_VENDOR_TRACKING','
	SELECT
		*

		FROM vendor_tracking
		WHERE 
                vendor_tracking.fulfillment_id <>"" 
            {DATE_RANGE}
            {SORT}
        {LIMIT}
');