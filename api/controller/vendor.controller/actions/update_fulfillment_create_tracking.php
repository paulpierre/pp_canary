<?php

namespace Actions {
    
    use \Traits\Api as Api;
    use \Traits\Db as Db;
    use \Methods\Log as Log;
    
    class UpdateFulfillmentCreateTracking {
        
        use \Traits\Api; 
        use \Traits\Db;
        use \Traits\ArrayUniqueTrackingDb;
        use \Traits\ArrayUniqueFulfillmentDb;
        use \Traits\ArrayUniqueFulfillmentApi;
        
        private $action = ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING;
        
        private $data;        
        private $shopify_orders;
        
        private $array_unique_tracking_db = Array();
        private $array_unique_fulfillment_db = Array();
        private $array_unique_fulfillment_api = Array();
        
        
        public function __construct($data, $shopify_orders) {
            
            $this -> data = $data;
            $this -> shopify_orders = $shopify_orders;
            
            return;
        }
        
        public function __clone() {
            
        }    
        
        public function set(){
            
            $this -> setArrays();            
            
            $this -> setCreateTrackingDb();
            $this -> setUpdateFulfillmentDb();
            $this -> setUpdateFulfillmentApi();
            
            return;
        }
        
        private function setArrays(){
            
            $this -> setArrayUniqueTrackingDb();
            $this -> setArrayUniqueFulfillmentDb();
            $this -> setArrayUniqueFulfillmentApi();
            
            return;
        }
        
        private function setCreateTrackingDb(){
            
            if(sizeof($this -> array_unique_tracking_db)){
                foreach($this -> array_unique_tracking_db as $data){
                    $this -> createTrackingDb($data);
                }
            }
            
            Log::set(' _');
            
            return;
        }
        
        private function setUpdateFulfillmentDb(){
            
            if(sizeof($this -> array_unique_fulfillment_db)){
                foreach($this -> array_unique_fulfillment_db as $data){
                    $this -> updateFulfillmentDb($data);
                }
            }
            
            Log::set(' _');
            
            return;
        }
        
        private function setUpdateFulfillmentApi(){
            
            if(sizeof($this -> array_unique_fulfillment_api)){
                foreach($this -> array_unique_fulfillment_api as $data){
                    $this -> updateFulfillmentApi($data);
                }
            }
            
            Log::set(' _');
            
            return;
        }

        public function __destruct() {
            
        } 
    }
}