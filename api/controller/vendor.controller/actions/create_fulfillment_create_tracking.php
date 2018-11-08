<?php

namespace Actions {
    
    use \Traits\Api as Api;
    use \Traits\Db as Db;
    use \Methods\Log as Log;
    
    class CreateFulfillmentCreateTracking {
        
        use \Traits\Api; 
        use \Traits\Db;
        use \Traits\ArrayUniqueOrderApiDb;
        
        private $action = ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING;
        
        private $data;        
        private $shopify_orders;
        
        private $array_unique_order_api_db = Array();
        
        
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
            
            $this -> setArrayUniqueOrderApiDb();
            
            return;
        }        
        
        private function setCreateNewFulfillments(){
            
            Log::set('array_unique_order_api_db',$this -> array_unique_order_api_db,true);
            
            if(sizeof($this -> array_unique_order_api_db)){
                foreach($this -> array_unique_order_api_db as $data){
                    
                    $order_status = $data['order_shopify_status'];
                                        
                    switch($order_status)
                    {
                        case 'closed':
                            $this -> reopenOrderApi($data);
                        break;
                    
                        case 'cancelled':
                            $this -> reopenOrderApi($data);
                        break;
                    
                        default:
                            
                        break;
                    }
                    Log::set('data',$data,true);
                    $fulfillment_shopify_id = $this -> createFulfillmentApi($data);   
                    Log::set('API fulfillment_shopify_id ',$fulfillment_shopify_id,true);
                    $data['fulfillment_shopify_id'] = $fulfillment_shopify_id['fulfillment']['id'];
                    
                    $fulfillment_id = $this -> createFulfillmentDb($data);
                    $data['fulfillment_id'] = $fulfillment_id;
                    
                    Log::set('DB fulfillment_id_',$fulfillment_id,true);
                    
                    $this -> createTrackingDb($data);
                    
                    switch($order_status)
                    {
                        case 'closed':
                            $this -> closeOrderApi($data);
                        break;
                    
                        case 'cancelled':
                            $this -> cancelOrderApi($data);
                        break;
                    
                        default:
                            
                        break;
                    }                    
                    
                    Log::set(' _');
                    
                }
            }
            
            return;
        }
        
        public function __destruct() {
            
        } 
    }
}