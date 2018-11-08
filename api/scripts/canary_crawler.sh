#!/bin/bash
#==================
# canary_crawler.sh
#==================

#+------------------------+
#| DATABASE CONFIGURATION |
#+------------------------+

_HOSTNAME=$(hostname)
if [ "${_HOSTNAME}" == "########" ]  || [ "${_HOSTNAME}" == "########" ]; then
    MYSQL_USER="root"
    MYSQL_PASS="root"
    MYSQL_HOST="127.0.0.1"
    MYSQL_DB="########"
    MYSQL_BIN="/Applications/MAMP/bin/apache2/bin/mysql"
    API_PATH="########/canary/src/api"

elif [ "${_HOSTNAME}" == "lemon" ]; then
    MYSQL_USER="########"
    MYSQL_PASS="########"
    MYSQL_HOST="127.0.0.1"
    MYSQL_DB="########"
    MYSQL_BIN="mysql"
    API_PATH="/var/www/html/canary/canary/"
else
    MYSQL_USER="c########"
    MYSQL_PASS="########"
    MYSQL_HOST="127.0.0.1"
    MYSQL_DB="########"
    MYSQL_BIN="mysql"
    API_PATH="########/canary/src/api/"
fi


cd $API_PATH

#+----------------+
#| RUN-TIME FLAGS |
#+----------------+

MODULUS_IS_ENABLED=1 #so we can segment scans on an hourly basis. make it 0 if you want to scan everything

#+-------------------+
#| RUN-TIME SETTINGS |
#+-------------------+

TRACKING_ID=2                   #China Post
TRACKING_NUMBER_LIMIT=1 #40
SQL_ORDER_AGE_LIMIT="3"         #in months


if [ "${MODULUS_IS_ENABLED}" == 1 ]; then
    SQL_MODULUS=" AND MOD(O.order_id,24) = HOUR(NOW()) "
fi


#+---------------------+
#| QUIT IF CRAWL ERROR |
#+---------------------+
#sql_get_error="SELECT value FROM sys WHERE sys.key=\"CRAWLER_FAILURE\""

#should_exit=$(echo "${sql_get_error}" | ${MYSQL_BIN} --user=${MYSQL_USER} --password=${MYSQL_PASS} ${MYSQL_DB} --host ${MYSQL_HOST} | sed -n 2p);

#if [  "${should_exit}" == 1 ]; then
#    echo "A thread has thrown an error in crawling. Quitting..Please fix it to continue crawling."
#    exit 0
#fi



#+-----------------------------------------+
#| GET TRACKING NUMBER COUNT FOR THIS HOUR |
#+-----------------------------------------+

sql_get_total="SELECT count(F.fulfillment_id) FROM orders O LEFT JOIN fulfillments F on F.order_id = O.order_id WHERE ((F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 1 DAY) AND O.order_topen > DATE_SUB(now(),INTERVAL ${SQL_ORDER_AGE_LIMIT} MONTH)) OR F.fulfillment_tcheck='0000-00-00 00:00:00' OR F.fulfillment_tracking_last_date='0000-00-00 00:00:00') AND F.fulfillment_is_tracking = 1 AND (F.fulfillment_tracking_number !='') ${SQL_MODULUS};";

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
    sql_query="SELECT F.order_id, F.fulfillment_id, F.fulfillment_tracking_number FROM orders O INNER JOIN fulfillments F on F.order_id = O.order_id WHERE ((F.fulfillment_tcheck='0000-00-00 00:00:00' OR F.fulfillment_tracking_last_date='0000-00-00 00:00:00') OR (F.fulfillment_tcheck < DATE_SUB(now(), INTERVAL 1 DAY)) AND O.order_topen > DATE_SUB(now(),INTERVAL ${SQL_ORDER_AGE_LIMIT} MONTH)) AND F.fulfillment_is_tracking = 1 AND (F.fulfillment_tracking_number !='') ${SQL_MODULUS} ORDER BY F.fulfillment_tcheck ASC LIMIT ${TRACKING_NUMBER_LIMIT} OFFSET $OFFSET";

    echo "Executing query: $sql_query\n\n"

    #+----------------------------------------------+
    #| GRAB QUERY RESULT FROM MYSQL BUT LIMIT TO 40 |
    #+----------------------------------------------+
    tracking_numbers=$(echo "${sql_query}" | ${MYSQL_BIN} --user=${MYSQL_USER} --password=${MYSQL_PASS} ${MYSQL_DB} --host ${MYSQL_HOST});

    #+-------------------------------------------------------+
    #| KILL SCRIPT IF THERE ARE NOT TRACKING NUMBERS RETURNED|
    #+-------------------------------------------------------+

    if [[ -z "${tracking_count// }" ]]; then
        echo "No records returned from sequel query! Exiting:\n\n ${tracking_numbers}"
        exit 0;
    fi


    JSON_STR="["

    #+---------------------------------------------+
    #| ITERATE THROUGH LIST AND CONVERT IT TO JSON |
    #+---------------------------------------------+
    while read -r line; do

        ORDER_ID=$(echo "$line" | cut -f1);
        FULFILLMENT_ID=$(echo "$line" | cut -f2);
        TRACKING_NUMBER=$(echo "$line" | cut -f3);

        #Lets skip the first header column
        if [ "${ORDER_ID}" == "order_id" ]; then
            continue
        fi

        JSON_STR+="{\"t\":\"$TRACKING_NUMBER\",\"o\":\"$ORDER_ID\",\"f\":\"$FULFILLMENT_ID\"},"

    done <<<"$tracking_numbers"


    #lets trim the last comma
    JSON=$(echo "$JSON_STR" | sed 's/,*$//') #${JSON_STR:0:-1}
    JSON+="]"


    #+----------------------------------------------------------------------+
    #| LETS PASS THE JSON LIST TO PHP WHICH WILL PASS IT OT PHP FOR STORAGE |
    #+----------------------------------------------------------------------+

    echo ""
    echo "running: /usr/bin/php -q index.php ########/crawler/${TRACKING_ID}/$JSON"
    /usr/bin/php -q index.php "########/crawler/${TRACKING_ID}/$JSON"
    sleep 1

    let COUNTER=COUNTER+1
done

exit 0
