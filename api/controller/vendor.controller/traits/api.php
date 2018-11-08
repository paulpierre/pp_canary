<?php

namespace Traits {
    
    use Methods\Curl as Curl;
    use Methods\Log as Log;
        
    trait Api {
                
        protected static function getOrderApi($_data){
            
            $status = 'any';
            $limit = 250;
            
            $url = self::getApiUrl("orders/" . $_data['order_shopify_id'] . ".json");
            $data = json_encode(Array());
            
            $curl = new Curl;
            $response = $curl -> get($url);
                        
            Log::set('SHOPIFY - GET ORDER','order_shopify_id: ' . $_data['order_shopify_id'],true); 
            Log::set(' .');
            
            return $response;
        }
        
        protected static function reopenOrderApi($_data){
            Log::set('SHOPIFY - reopen order _data',$_data,true);
            $url = self::getApiUrl('orders/' . $_data['order_shopify_id'] . '/open.json');
            $data = json_encode(Array());
            
            if(!TEST_MODE){
                $curl = new Curl;
                $response = $curl -> post($url, $data);
            } else {
                $curl = new Curl;
                $response = self::getTestOrderApi($_data);
            }
            Log::set('SHOPIFY - reopen order response',$response,true);
            Log::set('SHOPIFY - REOPENED ORDER','order_shopify_id: ' . $_data['order_shopify_id'],true);  
            Log::set(' .');
            
            return json_decode($response,true);
        }
        
        protected static function closeOrderApi($_data){
            
            $url = self::getApiUrl('orders/' . $_data['order_shopify_id'] . '/close.json');
            $data = json_encode(Array());
            
            if(!TEST_MODE){                
                $curl = new Curl;
                $response = $curl -> post($url, $data);
            } else {
                $response = self::getTestOrderApi($_data);
            }
            
            Log::set('SHOPIFY - CLOSED ORDER','order_shopify_id: ' . $_data['order_shopify_id'],true);  
            Log::set(' .');
            
            return json_decode($response,true);
        }
        
        protected static function cancelOrderApi($_data){
            
            $url = self::getApiUrl('orders/' . $_data['order_shopify_id'] . '/cancel.json');
            $data = json_encode(Array());
            
            if(!TEST_MODE){
                $curl = new Curl;
                $response = $curl -> post($url, $data);
            } else {
                $response = self::getTestOrderApi($_data);
            }
            
            Log::set('SHOPIFY - CANCELLED ORDER','order_shopify_id: ' . $_data['order_shopify_id'],true); 
            Log::set(' .');
            
            return json_decode($response,true);
        }
        
        protected static function getFulfillmentApi($_data){
            
            $status = 'any';
            $limit = 250;
            
            $url = self::getApiUrl("orders/" . $_data['order_shopify_id'] . "/fulfillments/" . $_data['fulfillment_shopify_id'] . ".json");
            $data = json_encode(Array());
            
            $curl = new Curl;
            $response = $curl -> get($url);
                        
            Log::set('SHOPIFY - GET FULFILLMENT','order_shopify_id: ' . $_data['order_shopify_id'] . ' | fulfillment_shopify_id: ' . $_data['fulfillment_shopify_id'],true);
            Log::set(' .');
            
            return $response;
        }
        
        protected static function updateFulfillmentApi($_data){
            
            $url = self::getApiUrl('orders/' . $_data['order_shopify_id'] . '/fulfillments/' . $_data['fulfillment_shopify_id'] . '.json');
            $httpHeaders = array('Content-Type' => 'application/json');
            $data = json_encode(Array(
                'fulfillment' => Array(
                    'tracking_numbers' => $_data['tracking_numbers'],
                    'id' => $_data['fulfillment_shopify_id']
                )
            ));
            
            if(!TEST_MODE){
                $curl = new Curl;
                $response = $curl -> put($url, $data, $httpHeaders);
            } else {
                $response = self::getTestOrderApi($_data);
            }
            
            Log::set('SHOPIFY - UPDATED FULFILLMENT','order_shopify_id: ' . $_data['order_shopify_id'] . ' | fulfillment_shopify_id: ' . $_data['fulfillment_shopify_id'] . ' | tracking_numbers: ' . join(", ",$_data['tracking_numbers']),true);
            Log::set('data',$data,true);
            Log::set(' .');
            
            
            return $response;
        }
        
        protected static function createFulfillmentApi($_data){
            
            /**
             * Create line_items array
             */
            $items = Array();
            foreach($_data['line_items'] as $k => $v){
                
                $items[] = Array('id' => intval($v['item']));
                
            }
            
            /**
             * If tracking data is multiple then create
             * array to tracking numbers
             * else create single tracking number
             */
            if(is_array($_data['tracking_number'])){
                
                if(sizeof($_data['tracking_number']) > 1){
                    
                    $tracking_number = Array();
                    $tracking_url = Array();
                    foreach($_data['tracking_number'] as $v){
                        
                        $tracking_number[] = $v;
                        $tracking_url[] = 'http://track.aftership.com/' . $v;
                    }
                    
                    $_label_name_tracking_number = 'tracking_numbers';
                    $_label_name_tracking_url = 'tracking_urls';
                    
                } else {
                    
                    $tracking_number = $_data['tracking_number'][0];
                    $tracking_url = 'http://track.aftership.com/' . $_data['tracking_number'][0];
                    $_label_name_tracking_number = 'tracking_number';
                    $_label_name_tracking_url = 'tracking_url';
                    
                }
            } else {
                
                $tracking_number = $_data['tracking_number'];
                $tracking_url = 'http://track.aftership.com/' . $_data['tracking_number'][0];
                $_label_name_tracking_number = 'tracking_number';
                $_label_name_tracking_url = 'tracking_url';
                
            }
            
            $url = self::getApiUrl('orders/' . $_data['order_shopify_id'] . '/fulfillments.json');
            $httpHeaders = array('Content-Type' => 'application/json');
            Log::set('API url',$url,true);
            $data = json_encode(Array(
                'fulfillment' => Array(
                    $_label_name_tracking_number  => $tracking_number,
                    $_label_name_tracking_url => $tracking_url,
                    'tracking_company' => 'Other',
                    'line_items'        => $items,
                    'notify_customer'   => false
                )
            ));
            Log::set('API data',$data,true);
            if(!TEST_MODE){
                $curl = new Curl;
                $response = $curl -> post($url, $data, $httpHeaders);
                Log::set('API response',$response,true);
            } else {
                $response = json_encode(Array('fulfillment' => Array('id' => mt_rand(100000000000,999999999999))));
            }
            
            Log::set('SHOPIFY - CREATED NEW FULFILLMENT','order_shopify_id: ' . $_data['order_shopify_id'] . ' | fulfillment_shopify_id: ' . $response . ' | tracking_number: ' . $_data['tracking_number'],true);
            foreach($_data['line_items'] as $k => $v){
                $items[] = $v['item'];
                Log::set('item',"id: " . $v['item'] . " | sheet: " . $v['sheet'] . " | row: " . $v['row'],true);
            }            
            Log::set('data',$data,true);
            Log::set(' .');
                        
            return json_decode($response,true);
        }
        
        protected static function cancelFulfillmentApi($_data){
            
            $url = self::getApiUrl("orders/" . $_data['order_shopify_id'] . "/fulfillments/" . $_data['fulfillment_shopify_id'] . "/cancel.json");
            $httpHeaders = array('Content-Type' => 'application/json');
            $data = json_encode(Array());
            
            if(!TEST_MODE){
                $curl = new Curl;
                $response = $curl -> post($url, $data, $httpHeaders);
            } else {
                $response = self::getTestFulfillmentApi($_data);
            }
            
            Log::set('SHOPIFY - CANCELLED FULFILLMENT','order_shopify_id: ' . $_data['order_shopify_id'] . ' | fulfillment_shopify_id: ' . $_data['fulfillment_shopify_id'],true);
            Log::set('response',$response,true);
            Log::set('url',$url,true);
            Log::set('_data',$_data,true);
            Log::set('data',$data,true);
            Log::set(' .');
            
            return json_decode($response,true);
        }
        
        public function getShopifyOrders(){
            
            $data = Array();
            $status = 'any';
            $limit = 250;
            
            $_ids = self::setIdsUrlString($this -> orders_ids,$limit);
            
            foreach($_ids as $ids){
                
                $url = self::getApiUrl("orders.json?ids=" . $ids . "&status=$status&limit=$limit");
                
                $curl = new Curl;
                $curl = json_decode($curl -> get($url),true);
                                
                if(sizeof($curl) > 0 && isset($curl['orders'])){
                    foreach($curl['orders'] as $c){
                        $data[$c['id']] = $c;
                    }
                }
                
            }
            
            return $data;
        }
        
        protected static function setIdsUrlString($_data,$limit){
            
            $data = Array();
            
            foreach($_data as $v){
                $data[] = $v;
            }
            
            $start = 0;
            $end = $limit;
            
            $ids = Array();
            
            do {
                
                $end = sizeof($data) > $end - 1?$end:sizeof($data);
                
                $_ids = '';
                $s = '';
                for($i = $start; $i < $end; $i++){
                    $_ids .=$s . $data[$i];
                    $s = ',';
                }
                
                $ids[] = $_ids;
                
                $start += $limit;
                $end += $limit;
                
            }while(sizeof($data) > $start);
            
            return $ids;
        }
        
        protected static function getTestOrderApi($_data){
            
            $status = 'any';
            $limit = 250;
            
            $url = self::getApiUrl("orders/" . $_data['order_shopify_id'] . ".json");
            $data = json_encode(Array());
            
            $curl = new Curl;
            $response = $curl -> get($url);
            
            return $response;
        }
        
        protected static function getTestFulfillmentApi($_data){
            
            $status = 'any';
            $limit = 250;
            
            $url = self::getApiUrl("orders/" . $_data['order_shopify_id'] . "/fulfillments/" . $_data['fulfillment_shopify_id'] . ".json");
            $data = json_encode(Array());
            
            $curl = new Curl;
            $response = $curl -> get($url);
            
            return $response;
        }
        
        protected static function getApiUrl($postfix){
            
            return 'https://' . API_KEY . ':' . API_SECRET_KEY . '@omg-true.myshopify.com/admin/' . $postfix;
        }
    }
}