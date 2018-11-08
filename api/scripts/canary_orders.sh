#!/bin/bash
#==================
# canary_orders.sh
#==================

#+---------------+
#| CONFIGURATION |
#+---------------+

_HOSTNAME=$(hostname)
if [ "${_HOSTNAME}" == "########" ]  || [ "${_HOSTNAME}" == "########" ]; then
    API_PATH="########/canary/src/api"
elif [ "${_HOSTNAME}" == "lemon" ]; then
    API_PATH="/var/www/html/canary/canary/"
else
    API_PATH="/home/canary/public_html/canary/src/api/"
fi


cd $API_PATH

echo "/usr/bin/php -q index.php api.########/cron/orders"
/usr/bin/php -q index.php "########/cron/orders"

exit 0
