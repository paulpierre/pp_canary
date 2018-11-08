<?php
/** =====================
 *  vendor.controller.php
 *  =====================
 *  ------
 *  ABOUT:
 *  ------
 *  Allows to upload vendors files and parsing data
 *
 */
global $controllerID,$controllerObject,$controllerFunction,$controllerData,$vendor_array,$shopify_response_status_code_array;




define('TRACKING_REGEX_CHINAPOST',"/[A-Z]{2,5}[0-9]{5,12}/");
define('ROW_SCAN_LIMIT',10);
define('IS_DEBUG',false);
define('DEBUG_ROW_LIMIT',10);

define('ERROR_SHEET_PARSING_NO_ERROR',0);
define('ERROR_SHEET_PARSING_ORDER_RECEIPT_ID_NOT_FOUND',1);
define('ERROR_SHEET_PARSING_SKU_NOT_FOUND',2);
define('ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND',3);
define('ERROR_SHEET_PARSING_ORDER_FULFILLMENT_NUMBER_NOT_FOUND',4);
define('ERROR_SHEET_FILE_ERROR',5);

define('SHEET_STATUS_UNKNOWN',0);
define('SHEET_STATUS_SUCCESS',1);
define('SHEET_STATUS_WARNING',2);
define('SHEET_STATUS_FAILURE',3);

define('ERROR_ROW_PARSING_NO_ERROR',0);
define('ERROR_ROW_PARSING_ORDER_RECEIPT_ID_NOT_FOUND',1);
define('ERROR_ROW_PARSING_SKU_NOT_FOUND',2);
define('ERROR_ROW_PARSING_TRACKING_NUMBER_NOT_FOUND',3);
define('ERROR_ROW_PARSING_FULFILLMENT_ID_NOT_FOUND',4);
define('ERROR_ROW_PARSING_ORDER_ID_NOT_FOUND',5);
define('ERROR_ROW_PARSING_ITEM_NOT_FOUND',6);
define('ERROR_ROW_PARSING_NO_RECEIPT_ID_NO_TRACKING_NUMBER',7);

define('ROW_STATUS_UNKNOWN',0);
define('ROW_STATUS_SUCCESS',1);
define('ROW_STATUS_WARNING',2);
define('ROW_STATUS_FAILURE',3);

define('ROW_ACTION_NONE',0);
define('ROW_ACTION_NONE_DATA_UP_TO_DATE',1);
define('ROW_ACTION_CREATE_TRACKING',2);
define('ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING',3);
define('ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING',4);
define('ROW_ACTION_UNFULFILLMENT',5);

//For vendor sheet uploads, condition flags
define('SHEET_MATCH_EXACT_MATCH',0);
define('SHEET_MATCH_CONTAINS',1);
define('SHEET_MATCH_REGEX',2);

//For vendor sheet uploads, which column to match conditions on
define('SHEET_COLUMN_ORDER_RECEIPT_ID',1);
define('SHEET_COLUMN_TRACKING_NUMBER',2);

define('VENDOR_ARRAY',$vendor_array);
define('SHOPIFY_RESPONSE_STATUS_CODE',$shopify_response_status_code_array);

define('TEST_MODE',0);
define('TEST_DETAILS',0);

switch($controllerFunction)
{
    
    /** +---------+
    *  | TRACKING |
    *  +----------+
    *
    *  DESCRIPTION:
    *  Allow to upload files from vedors
     * for updating trackings numbers
    *
    *  END-POINT:
    *  http://########/vendor/tracking_parse/
    *  http://########/vendor/tracking_parse/
    *
    */
    case 'tracking_parse':
        
        $vendor_id = $_POST ['vendor_id'];
        $safe_mode = $_POST ['safe_mode'];
        
        if(!array_key_exists($vendor_id,$vendor_array)){
            $api_response['code'] = 0;
            $api_response['msg'] = 'Vendor ID not provided. Select vendor.';
            echo json_encode($api_response);
            exit;
        }
        
        /**
         * get date and time of last file Edited
         */
        $workbookEdited = $_POST['fileLastEdited'];      
        $tempFile = $_POST['tempFile'];        
        $targetPath = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'];        
        $targetName = $_POST['targetName'];        
        $targetFile =  $targetPath . '/'. $targetName;
        
        /**
         * move file from tmp to our directory
         * and remove
         */
        if(!copy($tempFile, $targetFile)){
            echo "ERROR copy <br>";
        }
        unlink($tempFile);
        chmod($targetFile, 0777);
        
        /**
         * Object Oriented Workbook Parser
         */
        
        require './controller/vendor.controller/methods/autoload.php';
        Autoload::initialize();
        
        $tracking = new Fulfillment\Parse\Sheets($vendor_id, $targetName, $vendor_array);
        $result = $tracking -> get();
        
        /**
         * move file from to final directory
         * and remove tmp
         */
        $tmpPath = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'];        
        $tmpName = $targetName;        
        $tmpFile =  $tmpPath . '/'. $tmpName;
        
        $targetPath = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'] . '/done';        
        $targetName = $tmpName;        
        $targetFile =  $targetPath . '/'. $targetName;
        
        if(!copy($tmpFile, $targetFile)){
            echo "ERROR copy <br>";
        }
        unlink($tmpFile);
        chmod($targetFile, 0777);
        
        /**
         * send response and choose destination
         * depending of safe_mode
         */
        if($safe_mode){
            
            api_response(array('code'=>$result['status'],'msg'=>'','result'=>$result));
            
        } else {            
            include './controller/vendor.controller/methods/curl.php';
            
            $url = API_HOST . "/vendor/tracking_confirm/" . $result['summary']['vendor_id'];
            $post = 'data=' . json_encode($result['data'],true) . '&vendor_id=' . $result['summary']['vendor_id'];
            
            $curl = new Methods\Curl;
            $curl -> setOptTimeout(1);
            $curl -> post($url, $post);
            
        }
        
        exit;
    break;
    
    
    /** +----------------+
    *  | TRACKING CONFIRM|
    *  +-----------------+
    *
    *  DESCRIPTION:
    *  update tracking numbers after user confirmation or
    *  in case of turn off safe mode
    *
    *
    */
    case 'tracking_confirm':
        
        $vendor_id = $_POST ['vendor_id'];
        $data = $_POST ['data'];
        
        require './controller/vendor.controller/methods/autoload.php';
        Autoload::initialize();
        
        $tracking = new Fulfillment\Confirm\Confirm($vendor_id, $data);
        $result = $tracking -> get();
        
        exit;
    break;
    
    /** +-----------+
    *  | UNTRACKING |
    *  +------------+
    *
    *  DESCRIPTION:
    *  Allow to upload files from vedors
     * for updating trackings numbers
    *
    *  END-POINT:
    *  http://########/vendor/untracking_parse/
    *  http://########/vendor/untracking_parse/
    *
    */
    case 'untracking_parse':
        
        $vendor_id = $_POST ['vendor_id'];
        $safe_mode = $_POST ['safe_mode'];
        
        if(!array_key_exists($vendor_id,$vendor_array)){
            $api_response['code'] = 0;
            $api_response['msg'] = 'Vendor ID not provided. Select vendor.';
            echo json_encode($api_response);
            exit;
        }
        
        /**
         * get date and time of last file Edited
         */
        $workbookEdited = $_POST['fileLastEdited'];      
        $tempFile = $_POST['tempFile'];        
        $targetPath = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'];        
        $targetName = $_POST['targetName'];        
        $targetFile =  $targetPath . '/'. $targetName;
        
        /**
         * move file from tmp to our directory
         * and remove
         */
        if(!copy($tempFile, $targetFile)){
            echo "ERROR copy <br>";
        }
        unlink($tempFile);
        chmod($targetFile, 0777);
        
        /**
         * Object Oriented Workbook Parser
         */
        require './controller/vendor.controller/methods/autoload.php';
        Autoload::initialize();
        
        $tracking = new Unfulfillment\Parse\Sheets($vendor_id, $targetName, $vendor_array);
        $result = $tracking -> get();
        
        /**
         * move file from to final directory
         * and remove tmp
         */
        $tmpPath = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'];        
        $tmpName = $targetName;        
        $tmpFile =  $tmpPath . '/'. $tmpName;
        
        $targetPath = DATA_PATH . 'tracking_numbers/' . $vendor_array[$vendor_id]['file'] . '/done';        
        $targetName = $tmpName;        
        $targetFile =  $targetPath . '/_unlink_'. $targetName;
        
        if(!copy($tmpFile, $targetFile)){
            echo "ERROR copy <br>";
        }
        unlink($tmpFile);
        chmod($targetFile, 0777);
        
        /**
         * send response and choose destination
         * depending of safe_mode
         */
        if($safe_mode){
            
            api_response(array('code'=>$result['status'],'msg'=>'','result'=>$result));
            
        } else {            
            include './controller/vendor.controller/methods/curl.php';
            
            $url = API_HOST . "/vendor/untracking_confirm/" . $result['summary']['vendor_id'];
            $post = 'data=' . json_encode($result['data'],true) . '&vendor_id=' . $result['summary']['vendor_id'];
            
            $curl = new Methods\Curl;
            $curl -> setOptTimeout(1);
            $curl -> post($url, $post);
            
        }
        
        exit;
    break;
    
    
    
    /** +------------------+
    *  | UNTRACKING CONFIRM|
    *  +-------------------+
    *
    *  DESCRIPTION:
    *  update tracking numbers after user confirmation or
    *  in case of turn off safe mode
    *

    */
    case 'untracking_confirm':
        
        $vendor_id = $_POST ['vendor_id'];
        $data = $_POST ['data'];
        
        require './controller/vendor.controller/methods/autoload.php';
        Autoload::initialize();
        
        $tracking = new Unfulfillment\Confirm\Confirm($vendor_id, $data);
        $result = $tracking -> get();
        
        exit;
    break;
    
    
    /** +-----+
    *  | TREE |
    *  +------+
    *
    *  DESCRIPTION:
    *  Allow to display files
    *
    *  END-POINT:
    *  http://########/vendor/tree/
    *
    */
    case 'tree':
        //header('Content-Type: text/plain; charset=utf-8');
                
        $c_dir;
        $c_level;
    
        $dir_array = dirToArray(DATA_PATH . 'tracking_numbers/',array(),0);
        
        $tree = arrayToTree($dir_array);
        api_response(json_encode(array('code'=>1,'msg'=>'','data'=>$tree)));
    break;
    
    default:
        exit();
    break;
}

function log_vendor_sheet_stats($data)
{
    $sheet_count = 0;
    $sheet_name = '';
    $success_count = 0;
    $sheet_status = 0;

    //print PHP_EOL . print_r($data) . PHP_EOL;
    foreach($data as $_vendor_id=>$vendor_data)
    {
        $vendor_id = $_vendor_id;
        //print 'vendor_id: ' . $vendor_id . PHP_EOL;

        //Vendor File Level
        foreach($vendor_data as $f=>$sheet_data)
        {
            $file_name = $f;
            //print 'file_name: ' . $file_name . PHP_EOL;
            //print 'f: ' . $f . ' o: ' . print_r($sh,true);

            $file_json_data = json_encode($vendor_data);

            //Vendor Sheet Level
            foreach($sheet_data as $i => $o)
            {
                if(isset($o['status']) && intval($o['status'])> $sheet_status) $sheet_status = intval($o['status']);
                if(isset($o['success']) && intval($o['success']) > 0) $success_count += intval($o['success']);
                if(isset($o['total']) && intval($o['total']) > 0) $sheet_count += intval($o['total']);
            }

            $db_instance = new Database();

            $vendor_array = Array(
                'vendor_id'=>$vendor_id,
                'file_name'=>$file_name,
                'file_status'=>$sheet_status,
                'file_success_count'=>$success_count,
                'file_total_rows'=>$sheet_count,
                'file_error_rows'=>$sheet_count - $success_count,
                'file_error_json'=>$file_json_data,
                'file_tcreate'=>current_timestamp(),
                'file_tmodified'=>current_timestamp(),
            );
            $result = $db_instance->db_create('vendor_file_stats',$vendor_array);
            if(!$result)
            {
                //print('DATABASE ERROR: ' . print_r($result,true) . PHP_EOL);
            }

        }

    }
}


/**
 * 
 * TRACKING - parse Directory
 * Tree to Array
 * 
 */
function dirToArray($dir,$path) {
    $contents = array();      
    foreach (scandir($dir) as $node) {
        if ($node == '.')  continue;
        if ($node == '..') continue;
        if (is_dir($dir . DIRECTORY_SEPARATOR . $node)) { 
            $path = realpath($dir.DIRECTORY_SEPARATOR.$node);
            
            $contents[] = array('type'=>'dir','name' =>$node, 'files'=>dirToArray($dir . DIRECTORY_SEPARATOR . $node,$path));
        } else {
            if(strtolower(php_uname("n"))=="myshopify.stage"){
                $suburl = str_replace('index.php/', '', SITE_URL) . str_replace(API_DIRECT_PATH . "/", '', $path);
            } else {
                $suburl = str_replace('api.', '', SITE_URL) . str_replace(API_DIRECT_PATH, '', $path);
            }
            $contents[] = array('type'=>'file','name' =>$node,'link'=> $suburl . DIRECTORY_SEPARATOR . $node);
        }
    }
    return $contents;
}

/**
 * 
 * TRACKING - parse array
 * with directories and files
 * to html
 * 
 */
function arrayToTree($a){
    $s = '';
    foreach($a as $e){
        if($e['type'] == 'dir'){
            $s .= "<li data-jstree='{}'>" . $e['name'] . "<ul>";
            $s .= arrayToTree($e['files']);
            $s .= "</ul></li>";
        } else {
            $s .= "<li data-jstree='" . '{ "type" : "file" }' . "'><a href=". '"' . $e['link']. '"' . ">" . $e['name'] . "</a></li>";
        }
    }
    return $s;
}