#!/bin/bash
#==================
# canary_crawler.sh
#==================

#+------------------------+
#| DATABASE CONFIGURATION |
#+------------------------+

MYSQL_USER="c########"
MYSQL_PASS="########"
MYSQL_HOST="127.0.0.1"
MYSQL_DB="########"
MYSQL_BIN="mysql"
API_PATH="/home/canary/public_html/canary/src/api/"


cd $API_PATH

#+----------------+
#| RUN-TIME FLAGS |
#+----------------+

#MODULUS_IS_ENABLED=1 #so we can segment scans on an hourly basis. make it 0 if you want to scan everything

#+-------------------+
#| RUN-TIME SETTINGS |
#+-------------------+

TRACKING_ID=2                   #China Post
TRACKING_NUMBER_LIMIT=1 #40


#if [ "${MODULUS_IS_ENABLED}" == 1 ]; then
#    SQL_MODULUS=" AND MOD(O.order_id,24) = HOUR(NOW())"
#fi


#+-----------------------------------------+
#| GET TRACKING NUMBER COUNT FOR THIS HOUR |
#+-----------------------------------------+

#sql_get_total="SELECT count(F.fulfillment_id) FROM orders O LEFT JOIN fulfillments F on F.order_id = O.order_id WHERE ((F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 1 DAY) AND O.order_topen > DATE_SUB(now(),INTERVAL ${SQL_ORDER_AGE_LIMIT} MONTH)) OR F.fulfillment_tcheck='0000-00-00 00:00:00') AND F.fulfillment_is_tracking = 1 ${SQL_MODULUS};";
#sql_get_total="SELECT count(F.fulfillment_id) FROM orders O LEFT JOIN fulfillments F on F.order_id = O.order_id WHERE F.fulfillment_delivery_status !=4 AND F.fulfillment_is_tracking = 1;";
sql_get_total="SELECT count(F.order_id) FROM orders O INNER JOIN fulfillments F on F.order_id = O.order_id WHERE (O.order_topen > '2017-10-01 00:00:00' AND O.order_topen < '2017-10-30 23:59:59')) AND F.fulfillment_tcheck='0000-00-00 00:00:00' AND F.fulfillment_is_tracking = 1";


echo $sql_get_total

tracking_count=$(echo "${sql_get_total}" | ${MYSQL_BIN} --user=${MYSQL_USER} --password=${MYSQL_PASS} ${MYSQL_DB} --host ${MYSQL_HOST} | sed -n 2p);
echo "Records this hour: $tracking_count"

if [ "${tracking_count}" -eq 0 ]; then
    echo "No records to crawl. Perhaps something went wrong with this query:\n\n ${sql_get_total}"
    exit 0;
fi

thread_count=$(($tracking_count / $TRACKING_NUMBER_LIMIT))
let thread_count=thread_count+1

echo "$thread_count threads to launch"


#+--------------------------------+
#| LETS GRAB THE TRACKING NUMBERS |
#+--------------------------------+

COUNTER=0
while [ $COUNTER -lt ${thread_count} ]; do

    OFFSET=$(($COUNTER * $TRACKING_NUMBER_LIMIT))
    #sql_query="SELECT F.order_id, F.fulfillment_id, F.fulfillment_tracking_number FROM orders O LEFT JOIN fulfillments F on F.order_id = O.order_id WHERE ((F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 1 DAY) AND O.order_topen > DATE_SUB(now(),INTERVAL ${SQL_ORDER_AGE_LIMIT} MONTH)) OR F.fulfillment_tcheck='0000-00-00 00:00:00') AND F.fulfillment_is_tracking = 1 ${SQL_MODULUS} ORDER BY F.fulfillment_tcheck ASC LIMIT ${TRACKING_NUMBER_LIMIT} OFFSET $OFFSET";
    #sql_query="SELECT F.order_id, F.fulfillment_id, F.fulfillment_tracking_number FROM orders O LEFT JOIN fulfillments F on F.order_id = O.order_id WHERE F.fulfillment_delivery_status !=4 AND F.fulfillment_is_tracking = 1 ${SQL_MODULUS} ORDER BY F.fulfillment_tcheck ASC LIMIT ${TRACKING_NUMBER_LIMIT} OFFSET $OFFSET";

    sql_query="SELECT F.order_id, F.fulfillment_id, F.fulfillment_tracking_number FROM orders O INNER JOIN fulfillments F on F.order_id = O.order_id WHERE ((O.order_topen > '2017-10-01 00:00:00' AND O.order_topen < '2017-10-30 23:59:59') AND F.fulfillment_tcheck='0000-00-00 00:00:00' AND F.fulfillment_is_tracking = 1 ORDER BY F.fulfillment_tcheck ASC LIMIT ${TRACKING_NUMBER_LIMIT} OFFSET $OFFSET"


    echo "Executing query: $sql_query\n\n"
    echo ""

    #+----------------------------------------------+
    #| GRAB QUERY RESULT FROM MYSQL BUT LIMIT TO 40 |
    #+----------------------------------------------+
    #tracking_numbers=$(echo "${sql_query}" | ${MYSQL_BIN} --user=${MYSQL_USER} --password=${MYSQL_PASS} ${MYSQL_DB} --host ${MYSQL_HOST});

    #JSON_STR="["

    #+---------------------------------------------+
    #| ITERATE THROUGH LIST AND CONVERT IT TO JSON |
    #+---------------------------------------------+
    #while read -r line; do

     #   ORDER_ID=$(echo "$line" | cut -f1);
     #   FULFILLMENT_ID=$(echo "$line" | cut -f2);
     #   TRACKING_NUMBER=$(echo "$line" | cut -f3);

        #Lets skip the first header column
     #   if [ "${ORDER_ID}" == "order_id" ]; then
     #       continue
     #   fi

      #  JSON_STR+="{\"t\":\"$TRACKING_NUMBER\",\"o\":\"$ORDER_ID\",\"f\":\"$FULFILLMENT_ID\"},"

#done <<<"$tracking_numbers"


    #lets trim the last comma
    #JSON=$(echo "$JSON_STR" | sed 's/,*$//') #${JSON_STR:0:-1}
    #JSON+="]"


    #+----------------------------------------------------------------------+
    #| LETS PASS THE JSON LIST TO PHP WHICH WILL PASS IT OT PHP FOR STORAGE |
    #+----------------------------------------------------------------------+

    #echo ""
    #echo "running: /usr/bin/php -q index.php ########/crawler/${TRACKING_ID}/$JSON"
    #/usr/bin/php -q index.php "########/crawler/${TRACKING_ID}/$JSON"
  #  sleep 1

    let COUNTER=COUNTER+1
done

exit 0
