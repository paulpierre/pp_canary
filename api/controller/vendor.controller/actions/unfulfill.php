<?php

namespace Actions {
    
    use \Traits\Api as Api;
    use \Traits\Db as Db;
    use \Methods\Log as Log;
    
    class Unfulfill {
        
        use \Traits\Api; 
        use \Traits\Db;
        use \Traits\ArrayUniqueUnfulfillApiDb;
        
        private $action = ROW_ACTION_UNFULFILLMENT;
        
        private $data;        
        private $shopify_orders;
        
        private $array_unique_unfulfill_api_db = Array();
        
        
        public function __construct($data, $shopify_orders) {
            
            $this -> data = $data;
            $this -> shopify_orders = $shopify_orders;
            
            return;
        }
        
        public function __clone() {
            
        }    
        
        public function set(){
            
            $this -> setArrays();            
            
            $this -> setCreateNewFulfillments();
                        
            return;
        }
        
        private function setArrays(){
            
            $this -> setArrayUniqueUnfulfillApiDb();
            
            return;
        }        
        
        private function setCreateNewFulfillments(){
            Log::set('data',$this -> data,true);
            Log::set('array_unique_unfulfill_api_db',$this -> array_unique_unfulfill_api_db,true);
            Log::set('shopify_orders',$this -> shopify_orders,true);
            
            if(sizeof($this -> array_unique_unfulfill_api_db)){
                foreach($this -> array_unique_unfulfill_api_db as $array_unique_unfulfill_api_db){
                    
                    $order_status = $array_unique_unfulfill_api_db['order_shopify_status'];
                    Log::set('API order_status ',$order_status,true);
                    $order_shopify_id = $array_unique_unfulfill_api_db['order_shopify_id'];
                    
                    switch($order_status)
                    {
                        case 'closed':
                            $this -> reopenOrderApi($array_unique_unfulfill_api_db);
                        break;
                    
                        case 'cancelled':
                            $this -> reopenOrderApi($array_unique_unfulfill_api_db);
                        break;
                    
                        default:
                            
                        break;
                    }
                    
                    foreach($this -> shopify_orders[$order_shopify_id]['fulfillments'] as $_fulfillment){
                        
                        $is_tracking = 0;
                        
                        $is_tracking = $_fulfillment['tracking_number'] == $array_unique_unfulfill_api_db['tracking_number']?1:0;
                        
                        if($is_tracking == 0){
                            
                            foreach($_fulfillment['tracking_numbers'] as $_tracking_number){
                                $is_tracking = $_tracking_number == $array_unique_unfulfill_api_db['tracking_number']?1:0;
                            }                            
                        }
                        
                        if($is_tracking == 1){
                            
                            $cancelled_fulfillment = $this -> cancelFulfillmentApi($array_unique_unfulfill_api_db);
                            
                            $this -> deleteTrackingDb($array_unique_unfulfill_api_db);
                            $this -> deleteFulfillmentDb($array_unique_unfulfill_api_db);
                            
                            $this -> regenerateFulfillment($array_unique_unfulfill_api_db,$cancelled_fulfillment);
                            
                        }                        
                    }
                    
                    switch($order_status)
                    {
                        case 'closed':
                            $this -> closeOrderApi($array_unique_unfulfill_api_db);
                        break;
                    
                        case 'cancelled':
                            $this -> cancelOrderApi($array_unique_unfulfill_api_db);
                        break;
                    
                        default:
                            
                        break;
                    }                    
                    
                    Log::set(' _');
                    
                }
            }
            
            return;
        }
        
        protected function regenerateFulfillment($array_unique_unfulfill_api_db,$cancelled_fulfillment){
            
            $regenerated_tracking_numbers = Array();
            $regenerated_items = Array();
            $is_regenerate = 0;
            
            if(isset($cancelled_fulfillment['fulfillment']['line_items'])){
                foreach($cancelled_fulfillment['fulfillment']['line_items'] as $line_item){

                    $is_line_item = 0;

                    if(isset($array_unique_unfulfill_api_db['line_items']['item'])){
                        foreach($array_unique_unfulfill_api_db['line_items']['item'] as $item){
                            if($line_item == $item) $is_line_item = 1;
                        }

                        if($is_line_item == 0){
                            $regenerated_items[$item] = $item;
                            $is_regenerate = 1;
                        }
                    }
                }
            }
            
            if($is_regenerate == 1){
            
                if($cancelled_fulfillment['tracking_number'] != $array_unique_unfulfill_api_db['tracking_number']){

                    $regenerated_tracking_numbers[$cancelled_fulfillment['tracking_number']] = $cancelled_fulfillment['tracking_number'];

                }

                foreach($cancelled_fulfillment['fulfillment']['tracking_numbers'] as $tracking_number){

                    if($tracking_number != $array_unique_unfulfill_api_db['tracking_number']){

                        $regenerated_tracking_numbers[$tracking_number] = $tracking_number;

                    }
                }
                
                $_data = Array(
                    'order_shopify_id' => $array_unique_unfulfill_api_db['order_shopify_id'],
                    'tracking_number' => $regenerated_tracking_numbers,
                    'line_items' => $regenerated_items,
                );
                
                
                Log::set('REGENERATE FULFILLMENT');
                $fulfillment_shopify_id = $this -> createFulfillmentApi($_data)['fulfillment']['id'];                    
                $array_unique_unfulfill_api_db['fulfillment_shopify_id'] = $fulfillment_shopify_id;
                
                foreach($regenerated_tracking_numbers as $tracking_number){
                    
                    $array_unique_unfulfill_api_db['tracking_number'] = $tracking_number;
                    
                    $fulfillment_id = $this -> createFulfillmentDb($data);
                    $array_unique_unfulfill_api_db['fulfillment_id'] = $fulfillment_id;
                    Log::set('REGENERATE TRACKING');
                    $this -> createTrackingDb($array_unique_unfulfill_api_db);
                }
            }
            
            return;
        }
        
        public function __destruct() {
            
        } 
    }
}