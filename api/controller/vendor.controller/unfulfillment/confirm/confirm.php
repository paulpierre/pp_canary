<?php

namespace Unfulfillment\Confirm {
    
    use \Actions\Unfulfill as Unfulfill;
    
    use Methods\Log as Log;
    
    class Confirm {
        
        use \Traits\Api; 
        use \Traits\Db;
        
        private $data;
        
        private $orders_ids = Array();
        private $shopify_orders;
        
        
        private $_drop_fulfillment = Array();
        
        private $drop_fulfillment = Array();
        
        private $actions = Array(
                                    ROW_ACTION_UNFULFILLMENT,
                                );
        
        public function __construct($vendor_id, $data) {
            
            Log::init('massunfulfill_result');
            Log::curl(">IN\t | " . date('Y-m-d H:i:s') . "\t | " . $_SERVER['REMOTE_ADDR'] . "\t | " . 'UNFULFILLMENT-CONFIRM');
            
            $this -> data = json_decode($data,true);
            
            define('VENDOR_ID',$vendor_id);
            define('CURRENT_TIMESTAMP',date("Y/m/d H:i:s"));
            
            return;
        }    

        public function __clone() {
            
        } 

        public function get() {            
                        
            $this -> setShopifyOrders();
            $this -> setUnfulfillments();
                        
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
        
        public function setUnfulfillments(){
            
            if(isset($this -> data[ROW_ACTION_UNFULFILLMENT])){
                $this -> setUnfulfill();
            }
            
            return;
        }                
        
        private function setUnfulfill(){
                        
            $array_unique_order_api_db = new Unfulfill($this -> data, $this -> shopify_orders);
            $array_unique_order_api_db -> set();
            
            return;
        }
        
        public function __destruct() {
            
            Log::destruct();
            
        }
    }
    
}