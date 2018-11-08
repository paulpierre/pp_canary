/* [ SQL Reports ]

DELIVERY_STATUS_UNKNOWN=0
`DELIVERY_STATUS_CONFIRMED=1
DELIVERY_STATUS_IN_TRANSIT=2 // ChinaPost = 10
DELIVERY_STATUS_OUT_FOR_DELIVERY=3);
DELIVERY_STATUS_DELIVERED=4  // ChinaPost = 40
DELIVERY_STATUS_FAILURE=5     // ChinaPost = 35 (Undelivered)
DELIVERY_STATUS_NOT_FOUND=6 // ChinaPost = 00
DELIVERY_STATUS_PICKUP=7  // ChinaPost = 30
DELIVERY_STATUS_ALERT=8  // ChinaPost = 50
DELIVERY_STATUS_EXPIRED=9 // ChinaPost = 20 */

/*------------------------------------------------------
	SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDORS_BY_COUNTRY
	------------------------------------------------------*/
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
			GROUP BY V.vendor_id, O.order_customer_country


/*------------------------------------------
	SQL_REPORT_AVERAGE_DELIVERY_TIME_BY_VENDOR
	------------------------------------------*/
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
		GROUP BY V.vendor_id


/*-----------------------------------
	SQL_REPORT_REFUNDS_BY_VENDOR_BY_GEO
	----------------------------------*/
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

		GROUP BY O.order_customer_country,V.vendor_id
		ORDER BY cost DESC

/*----------------------------------------
	SQL_REPORT_REFUNDS_BY_VENDOR_BY_ITEM_SKU
	----------------------------------------*/

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

	GROUP BY I.item_sku,V.vendor_id
	ORDER BY cost DESC


/*----------------------------------
	SQL_REPORT_VENDOR_REFUND_SUMMARY
	---------------------------------*/

		SELECT
		V.vendor_id,
		SUM(O.order_total_cost) as cost,
		count(V.vendor_id) as count

		FROM orders O
		LEFT JOIN vendor_tracking V ON O.order_id = V.order_id
		WHERE
			O.order_is_refunded=1

		GROUP BY V.vendor_id



/*----------------------------
	SQL_REPORT_REFUNDS_BY_VENDOR
	----------------------------*/

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
			V.vendor_id = 3




/*----------------------------------
	SQL_REPORT_VENDOR_DELIVERY_SUMMARY
	---------------------------------*/

	SELECT
		F.fulfillment_delivery_status,
		count(F.fulfillment_delivery_status)

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
			V.vendor_id=3
		GROUP BY fulfillment_delivery_status

/*-------------------------------------------
	SQL_REPORT_VENDOR_DELIVERY_STATUS_BY_STATUS
	--------------------------------------------*/
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

			V.vendor_id=3

/*--------------------------------------------
	SQL_REPORT_DELIVERY_STATUS_SUMMARY_BY_VENDOR
	--------------------------------------------*/
	SELECT
		V.vendor_id,
		F.fulfillment_delivery_status,
		count(F.fulfillment_delivery_status)

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
		GROUP BY V.vendor_id,F.fulfillment_delivery_status


/*---------------------------------------
	SQL_REPORT_ALERT_OR_CUSTOMS_LIST_ORDERS
	---------------------------------------*/
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
		F.fulfillment_status_alert_tcreate,
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
			F.fulfillment_delivery_status=8 AND
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
			F.fulfillment_tracking_number !="" AND /* Has tracking number */
			F.fulfillment_tcheck !="0000-00-00 00:00:00" /* We have checked the tracking status of this already */



/*---------------------------------------
	SQL_REPORT_DELIVERY_FAILURE_LIST_ORDERS
	---------------------------------------*/
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
		F.fulfillment_status_failure_tcreate,
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
			F.fulfillment_delivery_status=5 AND
			O.order_is_refunded =0 AND /* Eliminate refunded orders */
			F.fulfillment_tracking_number !="" AND /* Has tracking number */
			F.fulfillment_tcheck !="0000-00-00 00:00:00" /* We have checked the tracking status of this already */


/*-----------------------------------------------
	SQL_REPORT_CUSTOMER_PICKUP_LIST_ORDERS
	----------------------------------------------*/

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
	F.fulfillment_status_customer_pickup_tcreate,
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
		F.fulfillment_delivery_status=7 AND
		O.order_is_refunded =0 AND /* Eliminate refunded orders */
		F.fulfillment_tracking_number !="" AND /* Has tracking number */
		F.fulfillment_tcheck !="0000-00-00 00:00:00" /* We have checked the tracking status of this already */



/*-----------------------------------------------
	SQL_REPORT_PACKAGE_STUCK_IN_CUSTOMS_LIST_ORDERS
	----------------------------------------------*/
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
			F.fulfillment_tcheck > DATE_ADD(F.fulfillment_tracking_last_date, INTERVAL 7 DAY);

/*-------------------------------------------
	SQL_REPORT_COURIER_LOST_PACKAGE_LIST_ORDERS
	-------------------------------------------*/

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
			F.fulfillment_tcheck > DATE_ADD(F.fulfillment_tracking_last_date, INTERVAL 5 DAY); /* If the time elapsed between when we first set the state to NOT FOUND to since we checked it after the first time is more than 5 days, it means we have a problem. Alert the user. */


/*-------------------------------------------------
	SQL_REPORT_CRAWL_TOTAL_UNCHECKED_TRACKING_NUMBERS
	-------------------------------------------------*/
	SELECT COUNT(*) FROM fulfillments F /* These could be new orders as well */
	WHERE
		(F.fulfillment_tcheck='0000-00-00 00:00:00' OR F.fulfillment_tracking_last_date='0000-00-00 00:00:00') AND
		F.fulfillment_is_tracking = 1 AND
		F.fulfillment_tracking_number !=''

/*-------------------------------
	SQL_REPORT_CRAWL_TOTAL_PROGRESS
	-------------------------------*/
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
					F.fulfillment_tcheck='0000-00-00 00:00:00' OR
					F.fulfillment_tracking_last_date='0000-00-00 00:00:00'
				) AND
					F.fulfillment_is_tracking = 1 AND
					F.fulfillment_tracking_number !='' ) AS A,

		/* This is how many tracking numbers we crawled today */
		(SELECT count(F.fulfillment_id) as tracking_numbers_done FROM fulfillments F WHERE F.fulfillment_tracking_number !='' AND DATE(F.fulfillment_tcheck) = CURDATE()) AS B
	)


/*----------------------
	SQL_REPORT_CRAWL_QUEUE
	----------------------*/
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
				(F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 2 DAY) AND /* Find any tracking numbers that haven't been checked in two days or more */
				O.order_topen > DATE_SUB(now(),INTERVAL 6 MONTH)	/* Also, include orders from X months ago (this is for older orders we need to follow up on */
				) OR
				F.fulfillment_tcheck='0000-00-00 00:00:00' OR	/* Or any new order that hasn't been checked */
				F.fulfillment_tracking_last_date='0000-00-00 00:00:00' /* Or any new order that hasn't been checked properly by the tracking API */
			) AND
				F.fulfillment_is_tracking = 1 AND /* Let's make sure these are only orders that we are tracking! */
				F.fulfillment_tracking_number !='' /* Let's also make sure there is an actual tracking number */

		ORDER BY F.fulfillment_tcheck ASC /* Lets order them by last check ASC because we want to get to the items that are 0000-00-00 first! */
