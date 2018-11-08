<?php
set_time_limit(0);
ini_set('mysql.connect_timeout',1600);
ini_set('max_execution_time', 1600);
ini_set('default_socket_timeout',1600);
ini_set("mysql.trace_mode", "0");

date_default_timezone_set('America/Los_Angeles');


/** ==========================
 *  DEFINE THE NAME OF THE APP
 *  ========================== */

define('APP_NAME','canary');
define('APP_DOMAIN','########');
define('APP_VERSION','1.1');


/** ================
 *  CRAWLER SETTINGS
 *  ================ */
define('API_TIME_INTERVAL',60*60*12); //every 12hrs
define('ORDER_COUNT_LIMIT', 5);

define('TRACKING_COMPANY_CONFIG',serialize(Array(
    TRACKING_COMPANY_CHINA_POST => Array(
        'url'=>'http://www.17track.net/restapi/handlertrack.ashx'
    )
)));

define('DEBUG_WRITE_CURL_RESPONSE_TO_FILE',false);


/** =================
 *  APPLICATION FLAGS
 *  ================= */

//Enable caching. In prod, set to true
define('ENABLE_CACHE',false);


/** ==============================
 *  INCLUDE MODELS AND CONTROLLERS
 *  ============================== */

//Lets define the path of the API based on Apache config's DOCUMENT_ROOT. Make sure DOC ROOT ends with "/"

switch(MODE) {
    case 'local':

        switch(strtolower(php_uname("n"))) {
            case 'myshopify.stage':
                //
                define('API_DIRECT_PATH','########/canary/src/api');
            break;
            
            default:
            case '########':
            case '########':
                define('API_DIRECT_PATH', '########/canary/src/api');
            break;
        }
    break;

    default:
    case 'prod':
        switch(strtolower(php_uname("n"))) {
            case 'myshopify.stage':
                define('API_DIRECT_PATH','########/canary/src/api');
            break;
        
            default:
            case 'voltronmb.local':
            case 'voltronmb.lan':
                define('API_DIRECT_PATH','########/canary/src/api');

            break;

        }
        break;

}

define('API_PATH',API_DIRECT_PATH .'/');


//Lets point out the name of models and controllers folder
$classesDir = array (
    API_PATH.'model/',

);

function __autoload($class_name) {

    global $classesDir;
    foreach ($classesDir as $directory) {
        log_error('__autoload: '.$directory . strtolower($class_name) . '.model.php');
        if (file_exists($directory . strtolower($class_name) . '.model.php')) {
            require_once ($directory . strtolower($class_name) . '.model.php');
            return;
        }
    }
}

/** =============================
 *  DEFINE SOME GLOBAL PATH NAMES
 *  ============================= */

//operational paths
define('LIB_PATH',API_PATH . 'shared/lib/');
define('DATA_PATH',API_PATH. 'data/');
define('CONTROLLER_PATH', API_PATH .'controller/');
define('MODEL_PATH', API_PATH .'model/');



//Define log directory
define('LOG_PATH',API_PATH . 'log/');

//Lets load some functional classes and utility functions
include(LIB_PATH . 'utility.php');
include(LIB_PATH . 'database.class.php');
include(API_PATH . 'class/crawler.class.php');



/** ======================
 *  Database Configuration
 * ======================= */
switch(MODE)
{
    case 'local':

        switch(strtolower(php_uname("n")))
        {
            default:
            case '########':
            case '########':
                define('TMP_PATH',API_PATH . 'tmp/');
                define('WWW_HOST','www.'.APP_NAME);
                define('API_HOST','api.' .APP_NAME);
                define('SITE_URL','http://' . API_HOST);

                define('DATABASE_HOST','########');
                define('DATABASE_PORT',3306);
                define('DATABASE_NAME',APP_NAME . '_db2');
                define('DATABASE_USERNAME',APP_NAME. '_db');
                define('DATABASE_PASSWORD','########');
                //Enable debugging mode
                define('ENABLE_DEBUG',true);
                define('ENABLE_LOGS',true);
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);

            break;
            case 'myshopify.stage':
                define('TMP_PATH',API_PATH . 'tmp/');
                define('WWW_HOST','########');
                define('API_HOST','########');
                define('SITE_URL','http://' . API_HOST);
                define('DATABASE_HOST','localhost');
                define('DATABASE_PORT',3306);
                define('DATABASE_NAME',APP_NAME . '_db2');
                define('DATABASE_USERNAME',APP_NAME. '_db');
                define('DATABASE_PASSWORD','########');
                //Enable debugging mode
                define('ENABLE_DEBUG',false);
                define('ENABLE_LOGS',false);
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
            break;
        }
    break;

    default:
    case 'prod':
        
        switch(strtolower(php_uname("n"))) {
            case 'myshopify.stage':
                define('TMP_PATH',API_PATH . 'tmp/');
                define('WWW_HOST','www.'.APP_NAME);
                define('API_HOST','api.' .APP_NAME);
                define('SITE_URL','http://' . API_HOST);
                define('DATABASE_HOST','########');
                define('DATABASE_PORT',3306);
                define('DATABASE_NAME',APP_NAME . '_db2');
                define('DATABASE_USERNAME',APP_NAME. '_db');
                define('DATABASE_PASSWORD','########');
                //Enable debugging mode
                define('ENABLE_DEBUG',false);
                define('ENABLE_LOGS',false);
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
            break;
        
            default:
                define('TMP_PATH',API_PATH . 'tmp/');
                define('WWW_HOST',APP_DOMAIN);
                define('API_HOST','########');
                define('SITE_URL','https://' . API_HOST);
                define('DATABASE_HOST','localhost');
                define('DATABASE_PORT',3306);
                define('DATABASE_NAME',APP_NAME . '_db2');
                define('DATABASE_USERNAME',APP_NAME. '_db');
                define('DATABASE_PASSWORD','########');
                //Enable debugging mode
                define('ENABLE_DEBUG',false);
                define('ENABLE_LOGS',false);
                //
                if(ENABLE_LOGS)
                {
                    error_reporting(E_ALL);
                    ini_set('display_errors', 1);
                    ini_set('display_startup_errors', 1);

                } else {
                    error_reporting(E_ERROR | E_PARSE);
                }
            break;
        }
        break;
}

if(ENABLE_CACHE)
{
    require_once(LIB_PATH . 'phpFastCache/phpFastCache.php');
    \phpFastCache\CacheManager::setup(array("path" => TMP_PATH));
    \phpFastCache\CacheManager::CachingMethod("phpfastcache");

    /**
     *  FLUSH CACHE
     */
    //if(MODE == 'local') $cache = \phpFastCache\CacheManager::getInstance(); $cache->clean();//exit();
}


/** ==========================
 *  Constants from 17Track.net
 *  ========================== */
include_once(DATA_PATH . '17track.php');

require_once LIB_PATH . 'XLSXReader.php';




/** -----------------------------------
 *  LETS FETCH APP SETTINGS FROM THE DB
 *  ----------------------------------- */

$db_instance = new Database();
$res = $db_instance->db_retrieve('sys',Array('*'));

foreach($res as $k)
    $sys_settings[$k['key']] = $k['value'];

define('CRAWLER_FAILURE',intval($sys_settings['CRAWLER_FAILURE']));
define('CRAWLER_DELAY_MIN',intval($sys_settings['CRAWLER_DELAY_MIN']));
define('CRAWLER_DELAY_MAX',intval($sys_settings['CRAWLER_DELAY_MAX']));
define('CRAWLER_QUERY_LIMIT',0);



/** -------------
 *  NOTIFICATIONS
 *  ------------- */

//Conditions that will set a flag to alert the system
define('MAX_DAYS_NOT_FOUND',intval($sys_settings['TRACKING_MAX_DAYS_NOT_FOUND']));
define('MAX_DAYS_IN_TRANSIT',intval($sys_settings['TRACKING_MAX_DAYS_IN_TRANSIT']));

//Boolean flags for notifications
define('CONDITION_SHOULD_NOTIFY_CUSTOMER_PICKUP',(intval($sys_settings['TRACKING_CONDITION_SHOULD_NOTIFY_CUSTOMER_PICKUP'])==1)?true:false);
define('CONDITION_SHOULD_NOTIFY_ALERT_STATUS',(intval($sys_settings['TRACKING_CONDITION_SHOULD_NOTIFY_ALERT_STATUS']) ==1)?true:false);

?>
