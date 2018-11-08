<?php

namespace Fulfillment\Confirm {
    
    use \Actions\CreateTracking as CreateTracking;
    use \Actions\UpdateFulfillmentCreateTracking as UpdateFulfillmentCreateTracking;
    use \Actions\CreateFulfillmentCreateTracking as CreateFulfillmentCreateTracking;
    
    use Methods\Log as Log;
    
    class Confirm {
        
        use \Traits\Api; 
        use \Traits\Db;
        
        private $data;
        
        private $orders_ids = Array();
        private $shopify_orders;
        
        
        private $_create_tracking = Array();
        private $_update_fulfillment_create_tracking = Array();
        private $_create_fulfillment_create_tracking = Array();
        
        private $create_tracking = Array();
        private $update_fulfillment_create_tracking = Array();
        private $create_fulfillment_create_tracking = Array();
        
        private $actions = Array(
                                    ROW_ACTION_CREATE_TRACKING,
                                    ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING,
                                    ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING
                                );
        
        public function __construct($vendor_id, $data) {
            
            Log::init('massfulfill_result');
            Log::curl(">IN\t | " . date('Y-m-d H:i:s') . "\t | " . $_SERVER['REMOTE_ADDR'] . "\t | " . 'FULFILLMENT-CONFIRM');
            
            $this -> data = json_decode($data,true);
            
            define('VENDOR_ID',$vendor_id);
            define('CURRENT_TIMESTAMP',date("Y/m/d H:i:s"));
            
            return;
        }    

        public function __clone() {
            
        } 

        public function get() {            
                        
            $this -> setShopifyOrders();
            $this -> setTrackings();
                        
            return;
        }
        
        public function setTrackings(){
            
            if(isset($this -> data[ROW_ACTION_CREATE_TRACKING])){
                $this -> setCreateTracking();
            }
            
            if(isset($this -> data[ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING])){
                $this -> setUpdateFulfillmentCreateTracking();
            }
            
            if(isset($this -> data[ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING])){
                $this -> setCreateFulfillmentCreateTracking();
            }
            
            return;
        }
        
        public function setShopifyOrders() {            
            
            foreach($this -> actions as $action){
                if(isset($this -> data[$action])){
                    foreach($this -> data[$action] as $v){
                        $this -> orders_ids[$v['order_shopify_id']] = $v['order_shopify_id'];
                    }
                }
            }
            
            $this -> shopify_orders = $this -> getShopifyOrders();
            
            return;
        }
                
        
        private function setCreateTracking(){
                        
            $array_unique_order_api_db = new CreateTracking($this -> data, $this -> shopify_orders);
            $array_unique_order_api_db -> set();
            
            return;
        }
        
        private function setUpdateFulfillmentCreateTracking(){
            
            $array_unique_order_api_db = new UpdateFulfillmentCreateTracking($this -> data, $this -> shopify_orders);
            $array_unique_order_api_db -> set();
            
            return;
        }
        
        
        private function setCreateFulfillmentCreateTracking(){
            
            $array_unique_order_api_db = new CreateFulfillmentCreateTracking($this -> data, $this -> shopify_orders);
            $array_unique_order_api_db -> set();
            
            return;
        }
        
        public function __destruct() {
            
            Log::destruct();
            
        }
    }
    
}