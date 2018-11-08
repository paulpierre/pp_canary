<?php
/** =================
 *  crawler.class.php
 *  =================
 *  Crawls fulfillment objects, updates status and saves crawl activity
 */
class Crawler
{
    public $fulfillment_id  = null;
    public $order_id        = null;
    public $error_id        = null;

    /** --------------------------------------------
     *  CRAWLER PARSING FUNCTION BY TRACKING COMPANY
     *  -------------------------------------------- */

    public function parse_china_post($data)
    {
        if(empty($data)) {
            $this->error_id = CRAWLER_ERROR_UNKNOWN;
            return false;
        }


        //This means we are being rate limited
        if(!isset($data['delay']) || intval($data['delay']) != 0) {
            $this->error_id = CRAWLER_ERROR_SERVER_RATE_LIMITED;
            return false;
        }



        $tracking_status_id =  strval($data['track']['e']);
        switch($tracking_status_id)
        {
            case '00': $tracking_status = DELIVERY_STATUS_NOT_FOUND; break;
            /**
             * _tips: "Item is not found at this moment, If necessary, please verify with the package sender and check back later.",
            _00_tipsMore_I1: "The carrier hasn't accepted your package yet.",
            _00_tipsMore_I2: "The carrier hasn't scanned and entered tracking information for your package yet.",
            _00_tipsMore_I3: "Your tracking number is incorrect or invalid.",
            _00_tipsMore_I4: "The item was posted a long time ago, info not available anymore.",
            _00_tipsMore_IOther: "Generally, after the sender ships your package, it will be processed by the carrier, then they scan and enter the tracking information.
             * There might be a delay between these scanning events and tracking availability in their system. Usually it takes a few days for processing,
             * therefore the tracking information may not appear online immediately, please try to track again later."
             */


            case '10': $tracking_status = DELIVERY_STATUS_IN_TRANSIT; break;
            /**
             *         _tips: "Item has shipped from originating country and is en route to its destination.",
            _10_tipsMore_I1: "Your package has been handed over to the carrier.",
            _10_tipsMore_I2: "Your package has been dispatched or departed from its country of origin.",
            _10_tipsMore_I3: "Your package has arrived its destination country and pending customs inspection.",
            _10_tipsMore_I4: "Your package has arrived its destination country and during domestic transportation.",
            _10_tipsMore_I5: "Your package is under another transportation period. For instance, it's in another country as a transit point and will be forwarded from there to its final destination country.",
            _10_tipsMore_IOther: "Please pay attention to detailed tracking information, if your item has arrived its destination country, we'd advise you to track it again within 1-2 days and observe the latest updates to ensure a smooth receipt of the package."
             */


            case '30': $tracking_status = DELIVERY_STATUS_PICKUP; break;
            /**
             *    _tips: "Item is out for delivery or arrived at local facility, you may schedule for delivery or pickup. Please be aware of the collection deadline.",
            _30_tipsMore_I1: "Your package has arrived at a local delivery point.",
            _30_tipsMore_I2: "Your package is out for delivery.",
            _30_tipsMore_IOther: "If you ensure your package has been delivered successfully, please ignore this notice. However, if you haven't received your package, we'd advise you to contact the carrier to arrange a re-delivery or collect your item. Hint: Generally, the carrier has collection deadline, we'd advise you pickup your package at once, or it might be returned to the sender."
             *
             */


            case '35': $tracking_status = DELIVERY_STATUS_FAILURE; break;
            /**
             *        _tips: "Item was attempted for delivery but failed, this may due to several reasons. Please contact the carrier for clarification.",
            _35_tipsMore_I1: "Possible reasons for unsuccessful item delivery attempt: addressee not available at the time of delivery; delivery delayed and rescheduled, addressee requested later delivery, address problem - unable to locate premises, rural or remote areas, etc.",
            _35_tipsMore_IOther: "If you ensure your package has been delivered successfully, please ignore this notice. However, if you haven't received your package, we'd advise you to contact the carrier to arrange a re-delivery or collect your item. Hint: Generally, the carrier has collection deadline, we'd advise you pickup your package at once, or it might be returned to the sender."
             */


            case '20': $tracking_status = DELIVERY_STATUS_EXPIRED; break;
            /**
             *    _tips: "Item was in transportation period for a long time still has no delivered results.",
            _20_tipsMore_I1: "At a certain stage during transportation, the carrier has not updated the tracking information due to the high volume of postage.",
            _20_tipsMore_I2: "The carrier was omitted to enter the tracking information.",
            _20_tipsMore_I3: "Your package may have been lost during the transportation period.",
            _20_tipsMore_IOther: "If you ensure your package has been delivered successfully, please ignore this notice. However, if you haven't received your package,
             * please pay attention, we'd advise you to contact the package sender or your shipping carrier for clarification."
             */
            case '50': $tracking_status = DELIVERY_STATUS_ALERT; break;
            /**
             *         _tips: "Item might undergo unusual shipping condition, this may due to several reasons, most likely item was returned to sender, customs issue, lost, damaged etc.",
            _50_tipsMore_I1: "Your package is being returned to sender due to any of these reasons: item refused by addressee; incorrect / illegible / incomplete address; expired retention period; addressee absence; etc.",
            _50_tipsMore_I2: "Your package might be retained by customs department due to any of these reasons: contains prohibited goods; importation of the goods is restricted; retained by customs due to tax payable, or any other unspecified reasons.",
            _50_tipsMore_I3: "Your package may have suffered damage or been lost during the transportation period.",
            _50_tipsMore_IOther: "If you ensure your package has been delivered successfully, please ignore this notice. However, if you haven't received your package and it is under the\"Alert\" status, then please pay attention: you need to read and analyze the detailed tracking information carefully. Due to complex description of worldwide shipping providers, we can't auto-detect and determine all the status 100% accurately, hope you understand. We'd advise you to contact the package sender or your shipping carrier for clarification."
             */


            case '40': $tracking_status = DELIVERY_STATUS_DELIVERED; break;
            /**
             *   _name: "Delivered",
            _tips: "Item was delivered successfully to the addressee.",
            _40_tipsMore_IOther: "Under most circumstances, the delivered status indicates that the carrier has delivered this package successfully. If the addressee didn't receive it, we'd advise you to contact the package sender or shipping carrier for clarification."
             */


            default: $tracking_status = DELIVERY_STATUS_UNKNOWN; break;
        }



        for($i=0;$i < 5;$i++)
        {
            if(!isset($data['track']['z' . ($i)]))break;
        }
        $i--;

        if(empty($data['track']['z' . ($i)])) $i--;
        //error_log(print_r($data,true));
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

        $tracking_country_from = strval($data['track']['b']);
        $tracking_country_to = strval($data['track']['c']);
        $tracking_carrier_from = strval($data['track']['w1']);
        $tracking_carrier_to = strval($data['track']['w2']);

        $tracking_last_date = $_d['a'];
        $tracking_last_location = $_d['c'];
        $tracking_last_location2 = $_d['d'];
        $tracking_last_status_text = $_d['z'];

        return Array(

            'country_from'=>$tracking_country_from,
            'country_to'=>$tracking_country_to,
            'carrier_from'=>$tracking_carrier_from,
            'carrier_to'=>$tracking_carrier_to,

            'last_date' => $tracking_last_date,
            'last_location'=>$tracking_last_location,
            'last_location2'=>$tracking_last_location2,
            'last_status_text'=>$tracking_last_status_text,
            'last_status'=>$tracking_status
        );
    }

    public function parse_usps($data)
    {
        //TODO: This needs to do something.. or not
        return false;
    }


    public function fetch_tracking_multi_status($tracking_array,$tracking_company,$proxy_data = null)
    {

        print 'fetch_tracking_multi_status=>tracking_array!!: ' . print_r($tracking_array,true);

        $crawl_start = time();

        //Lets grab the URL config from the appropriate tracking company
        $tracking_config = unserialize(TRACKING_COMPANY_CONFIG);
        $_url = $tracking_config[$tracking_company]['url'];

        $ch = curl_init();


        $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';

        //Setup POST data properly
        switch($tracking_company)
        {
            case TRACKING_COMPANY_CHINA_POST:

                $referrer = 'http://www.17track.net/en/track?nums=';
                $track_array = Array('guid'=>'','data'=>Array());
                foreach($tracking_array as $o)
                {
                    $track_array['data'][] = Array('num'=>trim($o['fulfillment_tracking_number']));
                    $referrer .= $o['fulfillment_tracking_number'] . ',';
                }
                $referrer = rtrim($referrer,',');


                $json_data = json_encode($track_array);
                print 'track_array: ' . print_r($track_array,true) . PHP_EOL;
                print 'sending json data to POST CURL: ' . $json_data . PHP_EOL;
                //$json_data = '{"guid":"e82af6e0719748c28d1608bb5b5cd6b8","data":[{"num":"' .  $tracking_number . '"}]}';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);


                $headers = [
                    'Host: www.17track.net',
                    'User-Agent: ' . $user_agent,
                    'Referer: ' . $referrer,
                    'Accept: */*',
                    'Accept-Language: en-US,en;q=0.6',
                    'Cache-Control: no-cache',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Cookie: ' . 'v5_ShowedWelcome=1',
                    'X-Powered-By: ASP.NET',
                    //'Content-Length: ' . count($json_data)
                ];

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


                break;
        }

        curl_setopt($ch, CURLOPT_URL,$_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-type: application/json"));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if($proxy_data != null)
        {
            $proxy_ip = $proxy_data['proxy_ip'];
            $proxy_port = $proxy_data['proxy_port'];

            log_error('Connecting to proxy: ' . $proxy_ip . ':' . $proxy_port);

            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if(DEBUG_WRITE_CURL_RESPONSE_TO_FILE && file_exists('data/17track.json')) $res = file_get_contents('data/17track.json'); else {$res = curl_exec ($ch);curl_close ($ch);}
        if(DEBUG_WRITE_CURL_RESPONSE_TO_FILE && !file_exists('data/17track.json')) file_put_contents('data/17track.json',$res);


        log_error('curl response: ' . $res . PHP_EOL);
        print('curl response: ' . $res . PHP_EOL);



        //catch json errors
        try {
            $json = json_decode($res, true);
        } catch(Exception $e)
        {
            log_error('get_tracking_status() error: ' . print_r($e). PHP_EOL);

            $this->error_id = CRAWLER_ERROR_JSON_MALFORMED;
            return false;
        }

        //catch json errors
        if($json === null && json_last_error() !== JSON_ERROR_NONE)
        {
            $this->error_id = CRAWLER_ERROR_NO_JSON_RETURNED;
            return false;
        }

        //print 'crawl data: ' . print_r($json,true) . PHP_EOL;

        $data = $json;

        $crawl_end = time();


        /** ---------------------------------
         *  CALL APPROPRIATE PARSING FUNCTION
         *  --------------------------------- */
        switch($tracking_company)
        {
            case TRACKING_COMPANY_CHINA_POST:

                $result = Array();

                print 'dat count: ' . count($data['dat']). PHP_EOL;
                print 'data["dat"]: ' . PHP_EOL . print_r($data['dat'],true);

                //If we are calling multiple tracking numbers per call, lets iterate each parse

                if(empty($data['dat'])) return false;
                if(count($data['dat']) > 1)
                {
                    print 'doing a multi call!!' . PHP_EOL;
                    foreach($data['dat'] as $o)
                    {
                        //print 'requesting to parse array: ' . print_r($o,true) . PHP_EOL;
                        $q =  $this->parse_china_post($o);
                        //print 'parse_china_post result: ' . print_r($q,true). PHP_EOL;
                        if(!$q)  continue; else {

                            $q['crawl_start']=$crawl_start;
                            $q['crawl_end']=$crawl_end;
                            $q['crawl_duration']=($crawl_end - $crawl_start);

                            $result[$o['no']] = $q;
                            //print 'result added: ' . print_r($result,true) . PHP_EOL;
                        }


                    }

                    //otherwise lets just parse the first result
                } else {
                    print 'doing a single call!!' . PHP_EOL;
                    $result = $this->parse_china_post($data['dat'][0]);
                    if(!$result) return false;
                    $result['crawl_start'] = $crawl_start;
                    $result['crawl_end'] = $crawl_end;
                    $result['crawl_duration'] = $crawl_end - $crawl_start;
                }

                //print 'FINAL RESULT: ' . PHP_EOL . print_r($result,true) . PHP_EOL;
                return $result;

                break;

            default:
                return false;
                break;

        }
    }



    /** -------------------------------------------
     *  CURL FUNCTION TO CONNECT TO TRACKING SYSTEM
     *  AND CONNECT TO A PROXY IF ANY ARE DEFINED
     *  ------------------------------------------- */

    public function fetch_tracking_status($tracking_company,$tracking_number,$proxy_data =null)
    {

        $crawl_start = time();

        //Lets grab the URL config from the appropriate tracking company
        $tracking_config = unserialize(TRACKING_COMPANY_CONFIG);
        $_url = $tracking_config[$tracking_company]['url'];

        $ch = curl_init();

        log_error('Fetching tracking number: ' . $tracking_number . ' url:' . $_url . PHP_EOL);

        //Setup POST data properly
        switch($tracking_company)
        {
            case TRACKING_COMPANY_CHINA_POST:
                $json_data = '{"guid":"e82af6e0719748c28d1608bb5b5cd6b8","data":[{"num":"' .  $tracking_number . '"}]}';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                break;
        }

        curl_setopt($ch, CURLOPT_URL,$_url);
              curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-type: application/json"));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if($proxy_data != null)
        {
            $proxy_ip = $proxy_data['proxy_ip'];
            $proxy_port = $proxy_data['proxy_port'];

            log_error('Connecting to proxy: ' . $proxy_ip . ':' . $proxy_port);

            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec ($ch);

        log_error('curl response: ' . $res . PHP_EOL);
        print('curl response: ' . $res . PHP_EOL);

        curl_close ($ch);


        //catch json errors
        try {
            $json = json_decode($res, true);
        } catch(Exception $e)
        {
            log_error('get_tracking_status() error: ' . print_r($e). PHP_EOL);

            $this->error_id = CRAWLER_ERROR_JSON_MALFORMED;
            return false;
        }

        //catch json errors
        if($json === null && json_last_error() !== JSON_ERROR_NONE)
        {
            $this->error_id = CRAWLER_ERROR_NO_JSON_RETURNED;
            return false;
        }



        $data = $json;

        $crawl_end = time();


        /** ---------------------------------
         *  CALL APPROPRIATE PARSING FUNCTION
         *  --------------------------------- */
        switch($tracking_company)
        {
            case TRACKING_COMPANY_CHINA_POST:

                $result = $this->parse_china_post($data);
                if(!$result) return false;

                $result['crawl_start'] = $crawl_start;
                $result['crawl_end'] = $crawl_end;
                $result['crawl_duration'] = $crawl_end - $crawl_start;

                return $result;

            break;

            default:
                return false;
            break;

        }


    }
}
