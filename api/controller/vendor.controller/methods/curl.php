<?php

namespace Methods {
    
    use \Methods\Log as Log;
    
    class Curl
    {
        
        private $lastUrl = null;
        private $lastData = null;
        private $lastHttpHeaders = Array();        
        private $lastHttpCode = null;
        private $last_primary_ip = null;
        private $lastLoopCounter = 0;
        
        private $maxLoopCounter = 10;
        
        private $timeout = 30;
        
        private $customrequest;

        protected function init($url, $httpHeaders = array()) {
            
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            
            curl_setopt($ch, CURLOPT_TIMEOUT, $this -> timeout);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this -> customrequest);
            
            curl_setopt($ch, CURLOPT_HEADER, true);
            
            curl_setopt($ch, CURLOPT_NOBODY, false);
            
            if(sizeof($httpHeaders) > 0){
                $headers = array();
                foreach ($httpHeaders as $key => $value) {
                    $headers[] = "$key: $value";        }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            };
            
            return $ch;

        }
        
        public function get($url, $httpHeaders = array()) {
            
            $this -> customrequest = 'GET';
            $this -> lastUrl = $url;
            $this -> lastHttpHeaders = $httpHeaders;
            
            $ch = $this -> init($url, $httpHeaders);
            
            return $this -> processRequest($ch);
        }

        public function post($url, $data, $httpHeaders = array()) {
            
            $this -> customrequest = 'POST';
            $this -> lastUrl = $url;
            $this -> lastData = $data;
            $this -> lastHttpHeaders = $httpHeaders;
            
            $ch = $this -> init($url, $httpHeaders);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            
            return $this -> processRequest($ch);
        }

        public function put($url, $data, $httpHeaders = array()) {
            
            $this -> customrequest = 'PUT';
            $this -> lastUrl = $url;
            $this -> lastData = $data;
            $this -> lastHttpHeaders = $httpHeaders;
            
            $ch = $this -> init($url, $httpHeaders);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            
            return $this -> processRequest($ch);
        }

        public function delete($url, $httpHeaders = array()) {
            
            $this -> customrequest = 'DELETE';
            $this -> lastUrl = $url;
            $this -> lastHttpHeaders = $httpHeaders;
            
            $ch = $this -> init($url, $httpHeaders);
                        
            return $this -> processRequest($ch);
        }

        protected function processRequest($ch) {
            
            $output = curl_exec($ch);

            if (curl_errno($ch)) {
                Log::curl($this -> logCurlError(curl_errno($ch),curl_error($ch)));
            }
            
            $this -> lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this -> last_primary_ip = curl_getinfo($ch, CURLINFO_PRIMARY_IP);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);
            
            return $this -> parseOutput($output,$header_size); 
        }

        protected function parseOutput($output,$header_size) {
            
            $header = substr($output, 0, $header_size);
            $result = substr($output, $header_size -1 , strlen($output) - 1);
            $headers = array();

            $arrRequests = explode("\r\n\r\n", $header);

            for ($index = 0; $index < count($arrRequests) -1; $index++) {

                foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
                    if ($i === 0){
                        $headers['http_code'] = $line;
                    } else {
                        list ($key, $value) = explode(': ', $line);
                        $headers[$key] = $value;
                    }
                }
            }
            
            $this -> createErrorLoop($headers);
            $this -> trackCallLimit($headers);
            
            $headers['custom_request'] = $this -> customrequest;
            
            Log::curl($this -> logCurlSuccess($headers));
            
            return $result;
        }
        
        
        protected function createErrorLoop($headers){
                      
            if($this -> lastHttpCode == 429 ||
                    $this -> lastHttpCode == 500 || 
                    $this -> lastHttpCode == 501 || 
                    $this -> lastHttpCode == 503 || 
                    $this -> lastHttpCode == 504) {
                $loopStatus = 1;
            } else {
                $loopStatus = 0;
            }
            
            if ($loopStatus == 1 && $this -> lastLoopCounter < $this -> maxLoopCounter) {
                                
                $headers['HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT'] = '40/40';
                $this -> trackCallLimit($headers);
                
                switch($this -> customrequest) {
                    
                    case 'GET':
                        
                        $this -> get($this -> lastUrl, $this -> lastHttpHeaders);
                        Log::curl($this -> logErrorLoop($this -> lastLoopCounter,$this -> customrequest));
                        $this -> lastLoopCounter += 1;
                        break;
                    
                    case 'POST':
                        
                        $this -> post($this -> lastUrl, $this -> lastData, $this -> lastHttpHeaders);
                        Log::curl($this -> logErrorLoop($this -> lastLoopCounter,$this -> customrequest));
                        $this -> lastLoopCounter += 1;
                        break;
                    
                    case 'PUT':
                        
                        $this -> put($this -> lastUrl, $this -> lastData, $this -> lastHttpHeaders);
                        Log::curl($this -> logErrorLoop($this -> lastLoopCounter,$this -> customrequest));
                        $this -> lastLoopCounter += 1;
                        break;
                    
                    case 'DELETE':
                        
                        $this -> delete($this -> lastUrl, $this -> lastHttpHeaders);
                        Log::curl($this -> logErrorLoop($this -> lastLoopCounter,$this -> customrequest));
                        $this -> lastLoopCounter += 1;
                        break;                    
                }

            };
            
            return;
        }
        
        protected function trackCallLimit($headers){
                
            if(isset($headers['HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT'])){
                $call_limit = explode('/',$headers['HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT'])[0];
            } elseif (isset($headers['X-Shopify-Shop-Api-Call-Limit'])) {
                $call_limit = explode('/',$headers['X-Shopify-Shop-Api-Call-Limit'])[0];
            } else {
                $call_limit = 0;
            }
            
            if($call_limit > 1){
                $sleep = ceil(($call_limit / 2));
                Log::curl($this -> logCallLimit($call_limit, $sleep));
                sleep($sleep);
            }
            
            return;
        }
        
        protected function logCurlSuccess($headers){
            
            $string = '<';
            $string .= isset($headers['custom_request'])?$headers['custom_request']:'-';
            $string .= "\t | ";
            
            $header_array = isset($headers['http_code'])?explode(" ", $headers['http_code']):Array();
            
            $string .= isset($header_array[2])?$header_array[2]:'-';
            $string .= "\t | ";
            $string .= isset($header_array[1])?$header_array[1]:$this -> lastHttpCode;
            $string .= "\t | ";
            $string .= date('Y-m-d H:i:s');
            $string .= "\t | ";
            $string .= session_id();
            $string .= "\t | ";
            $string .= isset($headers['HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT'])?'call_limit: ' . $headers['HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT']:'call_limit: ' . '-';
            $string .= "\t | ";
            $string .= isset($headers['X-Shopify-Shop-Api-Call-Limit'])?'call_limit: ' . $headers['X-Shopify-Shop-Api-Call-Limit']:'call_limit: ' . '-';
            $string .= "\t | ";
            $string .= 'remote_addr: ' . $_SERVER['REMOTE_ADDR'];
            $string .= "\t | ";
            $string .= array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)?'x-forwarded: ' . array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])):'x-forwarded: ' . '-';
            $string .= "\t | ";
            $string .= $this -> last_primary_ip;
            
            return $string;
        }
        
        protected function logCurlError($curl_errno,$curl_error){
            
            $string = '-';
            $string .= 'ERR';
            $string .= "\t | ";
            $string .= date('Y-m-d H:i:s');
            $string .= "\t | ";
            $string .= session_id();
            $string .= "\t | ";
            $string .= $curl_errno;
            $string .= "\t | ";
            $string .= $curl_error;
            $string .= "\t | ";
            $string .= 'remote_addr: ' . $_SERVER['REMOTE_ADDR'];
            $string .= "\t | ";
            $string .= array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)?'x-forwarded: ' . array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])):'x-forwarded: ' . '-';
            $string .= "\t | ";
            $string .= $this -> last_primary_ip;
            
            return $string;
        }
        
        protected function logErrorLoop($loop,$customrequest){
            
            $string = '-';
            $string .= 'LOOP';
            $string .= "\t | ";
            $string .= date('Y-m-d H:i:s');
            $string .= "\t | ";
            $string .= session_id();
            $string .= "\t | ";
            $string .= $loop;
            $string .= "\t | ";
            $string .= $customrequest;
            $string .= "\t | ";
            $string .= $this -> lastHttpCode;
            $string .= "\t | ";
            $string .= 'remote_addr: ' . $_SERVER['REMOTE_ADDR'];
            $string .= "\t | ";
            $string .= array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)?'x-forwarded: ' . array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])):'x-forwarded: ' . '-';
            $string .= "\t | ";
            $string .= $this -> last_primary_ip;
            
            return $string;
        }
        
        protected function logCallLimit($call_limit, $time){
            
            $string = '-';
            $string .= 'WAIT';
            $string .= "\t | ";
            $string .= date('Y-m-d H:i:s');
            $string .= "\t | ";
            $string .= session_id();
            $string .= "\t | ";
            $string .= $call_limit;
            $string .= "\t | ";
            $string .= $time;
            $string .= "\t | ";
            $string .= 'remote_addr: ' . $_SERVER['REMOTE_ADDR'];
            $string .= "\t | ";
            $string .= array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)?'x-forwarded: ' . array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])):'x-forwarded: ' . '-';
            $string .= "\t | ";
            $string .= $this -> last_primary_ip;
            
            return $string;
        }
        
        public function setOptTimeout($value){
            
            $this -> timeout = $value;
            
            return;
        }
    }

}