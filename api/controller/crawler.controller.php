<?php
/** ======================
 *  crawler.controller.php
 *  ======================
 *  ------
 *  ABOUT:
 *  ------
 *  The Crawler controller is called by the system cron job which crawls fulfillment records flagged in the
 *  database for shipment tracking. Everyday it will crawl http://17track.net to parse and store / update the
 *  shipping status of all outstanding fulfillment orders.
 *
 *  ----------
 *  FUNCTIONS:
 *  ----------
 *
 *  • Update the status of fulfillment records in the database
 *  • Connect to proxies to crawl and parse http://17track.net as invoked by the cron daemon
 *
 */
global $controllerObject,$controllerFunction,$controllerID,$controllerData,$tracking_countries_array,$tracking_carriers_array;

$array_lookup = Array();

/** ===================
 *  VALIDATE PARAMETERS
 *  ====================
 */

if(!isset($controllerFunction))
{
    log_error('Tracking company ID Provider not provided');
    is_crawler_error('Tracking company ID provider empty');
    exit();
}
$tracking_company = $controllerFunction;

if(!isset($controllerID))
{
    log_error('JSON NOT PROVIDED');
    is_crawler_error('Empty json string provided');
    exit();
}

$json = json_decode(urldecode($controllerID),true);

if ($json === null
    && json_last_error() !== JSON_ERROR_NONE) {
    log_error('Malformed JSON provided:' . PHP_EOL . $json);
    is_crawler_error('Malformed json provided in crawler.controller.php: '. $json);
    exit();
}

/** ======================
 *  BUILD API QUERY STRING
 *  ======================
 */

$_url = "http://v5-api.17track.net:8044/handlertrack.ashx?nums=";

foreach($json as $o)
{
    if(!isset($o['t'])) {
        log_error('Tracking number not set!');
        continue;
        }
    $_url .= $o['t'] .',';
    $array_lookup[strtolower($o['t'])]=Array(
        'order_id'=>$o['o'],
        'fulfillment_id'=>$o['f']
    );
    log_error('adding: ' . $o['t']);
}
if(empty($array_lookup)) {
    log_error('Entire array_look up empty! Theres a problem');
    exit();
}
//Lets chop off that last comma
$_url = rtrim($_url,',');

log_error('17Track API Query: ' . $_url);

$ch = curl_init();
$headers = [
    'Connection: close',
    'Accept: */*',
    'Accept-Language: en-US,en;q=0.6',
    'Cache-Control: no-cache',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    '17token: ' . TRACKER_API_TOKEN
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_URL,$_url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$res = curl_exec ($ch);
curl_close ($ch);

log_error('CURL Response: ' . PHP_EOL .$res);

/** ==================
 *  PARSE API RESPONSE
 *  ==================
 */

$tracker_json = json_decode($res,true);

if ($tracker_json === null
    && json_last_error() !== JSON_ERROR_NONE) {
    log_error('Malformed JSON provided by API call:'.PHP_EOL . print_r($tracker_json,true));
    is_crawler_error('Bad response from 17track.net API: ' . $tracker_json);
    exit();
}

log_error('API Response:' . print_r($tracker_json,true));

$result_array = Array();



switch ($tracker_json['ret'])
{
    //Exceed access limit
    case -5:
        log_error('## API ERROR: Exceed access limit' . PHP_EOL . print_r($tracker_json,true));
        exit();
    break;

    //Access error
    case 0:

    //Access error
    case -4:
    log_error('## API ERROR: Access error' . PHP_EOL . print_r($tracker_json,true));
    exit();
    break;
}

if(empty($tracker_json['dat']))
{
    log_error('17Track API error with response: '.PHP_EOL . print_r($tracker_json,true));
    //is_crawler_error('17Track API error.');
    exit();
}

/** =====================================================
 *  PASS EACH TRACKING CODE RESULT TO OUR TRACKING PARSER
 *  =====================================================
 */
$tracking_number_retry = Array();
foreach($tracker_json['dat'] as $o)
{

    if($o['delay'] == -1 ) {
        log_error('Tracking number returned -1: ' . $o['no']);
        $tracking_number_retry[] = $o['no'];
        continue;
    }

    switch(intval($tracking_company))
    {
        default:
        case TRACKING_COMPANY_CHINA_POST:
            $_result = parse_china_post($o);
        break;
    }

    if(!$_result)
    {
        //This means there way an error in parsing a particular field. Lets just move on
        continue;
    } else
        $result_array[] = $_result;

    if(empty($result_array))
    {
        //This means we have nothing to store, so we should abort entirely
        log_error('No parsing results to pass tracking.controller.php. Aborting. Tracking ID:' . $tracking_company);
    }
}


$post_string = http_build_query( Array(
    'crawl_tstart' => time(),
    'json'=>urlencode(json_encode($result_array))
));

$url = 'http://' . API_HOST . '/tracking/' . $tracking_company;

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS, $post_string);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);



/** ===================================
 *  PARSING FUNCTIONS FOR TRACKING APIS
 *  ===================================
 */



function parse_china_post($data)
{
    global  $array_lookup;
    if(empty($data)) {
        log_error('parse_china_post .. argument array empty! aborting.');
        return false;
    }

/*
    //This means we are being rate limited
    if(!isset($data['delay']) || intval($data['delay']) != 0) {
        $this->error_id = CRAWLER_ERROR_SERVER_RATE_LIMITED;
        return false;
    }

*/

    $tracking_status =  $data['track']['e'];

    for($i=0;$i < 5;$i++)
    {
        if(!isset($data['track']['z' . ($i)]))break;
    }
    $i--;

    if(empty($data['track']['z' . ($i)])) $i--;
    $_d = (isset($data['track']['z' . $i][0]))?$data['track']['z' . $i][0]:$data['track']['z' . $i];


    /**
     *  b = country code from
     *  c = country code to
     *  e = status code
     *  f =
     *  w1 = carrier from
     *  w2 = carrier to
     *  ln1 = language from
     *  ln2 = language to
     *
     */

    $tracking_number = $data['no'];
    $tracking_country_from = $data['track']['b'];
    $tracking_country_to = $data['track']['c'];
    $tracking_carrier_from = $data['track']['w1'];
    $tracking_carrier_to = $data['track']['w2'];

    $tracking_last_date = $_d['a'];

    $tracking_last_status_text = $_d['z'];

    return Array(

        'tracking_number'=>$tracking_number,
        'tracking_country_from'=>$tracking_country_from,
        'tracking_country_to'=>$tracking_country_to,
        'tracking_carrier_from'=>$tracking_carrier_from,
        'tracking_carrier_to'=>$tracking_carrier_to,
        'tracking_last_date' => $tracking_last_date,
        'tracking_last_status_text'=>$tracking_last_status_text,
        'tracking_last_status'=>$tracking_status,
        'order_id'=>$array_lookup[strtolower($tracking_number)]['order_id'],
        'fulfillment_id'=>$array_lookup[strtolower($tracking_number)]['fulfillment_id']
    );
}
