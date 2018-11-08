<?php
/** +-----------------------------------------------------------+
 *  | Canary - Shipments Tracker for Shopify by paul@pixel6.net |
 *  +-----------------------------------------------------------+
 *  Started 10/7/2017
 */

session_start();
session_id()?session_regenerate_id():session_create_id();

header("Access-Control-Allow-Origin: *");

//Lets determine whether we're running in production or not
define('MODE',
    (
        strtolower(php_uname("n"))=="########" ||
        strtolower(php_uname("n"))=="########" /*||
        strtolower(php_uname("n"))=="myshopify.stage"*/
        )?'local':'prod');

/** ==============
 *  LOAD RESOURCES
 *  ==============
 */

include_once('constants.php');
include_once('config.php');


/** ===========
 *  URL ROUTING
 *  ===========
 */

if(isset($argv[1])) $q = explode('/',$argv[1]);
else $q = explode('/',$_SERVER['REQUEST_URI']);


$q_pos = strpos($q[count($q)-1],'?');
if($q_pos)
{
    $q[count($q)-1] = substr($q[count($q)-1],0,$q_pos);
}
if(strpos($q[count($q)-1],'?')===0) unset($q[count($q)-1]);

$controllerObject   = strtolower((isset($q[1]))?$q[1]:false);
$controllerFunction = strtolower((isset($q[2]))?$q[2]:false);
$controllerID       = strtolower((isset($q[3]))?$q[3]:false);
$controllerData     = strtolower((isset($q[4]))?$q[4]:false);

$server_name = isset($_SERVER['HTTP_HOST'])?strtolower($_SERVER['HTTP_HOST']):$q[0];


/** ==================
 *  CONTROLLER ROUTING
 *  ==================
 */
//Load the object's appropriate controller
$_controller = CONTROLLER_PATH . $controllerObject . '.controller.php';

if(file_exists($_controller))  include($_controller);
else
    api_response(array(
        'code'=> RESPONSE_ERROR,
        'data'=> array('message'=>ERROR_INVALID_OBJECT)
    ));




/** ============
 *  API RESPONSE
 *  ============
 */
function api_response($res)
{   
    json_encode($res);
    if(json_last_error() !== JSON_ERROR_NONE){
        if(json_last_error() === 5 && isset($res['data']['report'])){
            foreach($res['data']['report'] as $_k=>$_v){
                foreach($_v as $k=>$v){
                    json_encode($v);
                    if(json_last_error() !== JSON_ERROR_NONE){
                        $res['data']['report'][$_k][$k] = mb_convert_encoding($v, "UTF-8", "UTF-8");
                    } else {
                        $res['data']['report'][$_k][$k] = $v;
                    }
                }
            }
        } else {
            $res = array(
                            'code'=> RESPONSE_ERROR,
                            'data'=> array('message'=>ERROR_INVALID_OBJECT)
                        );
        }
    }
    header('Content-Type: application/json');
    if(ENABLE_DEBUG)
    {
        exit('<pre>' . print_r(
                $res,true));
    }
    exit(json_encode(
        $res
    ));
}
?>
