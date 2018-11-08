<?php

namespace Actions {
    
    use \Traits\Api as Api;
    use \Traits\Db as Db;
    use \Methods\Log as Log;
    
    class CreateTracking {
        
        use \Traits\Api; 
        use \Traits\Db;
        use \Traits\ArrayUniqueTrackingDb;
        
        private $action = ROW_ACTION_CREATE_TRACKING;
        
        private $data;
        
        private $array_unique_tracking_db = Array();
        
        
        public function __construct($data) {
            
            $this -> data = $data;
            
            return;
        }
        
        public function __clone() {
            
        }    
        
        /**
         * create tracking db
         * 
         * @return type
         */
        public function set(){
            
            $this -> setArrays();            
            
            $this -> setCreateTrackingDb();
            
            return;
        }
        
        private function setArrays(){
            
            $this -> setArrayUniqueTrackingDb();
            
            return;
        }
        
        private function setCreateTrackingDb(){
            
            if(sizeof($this -> array_unique_tracking_db)){
                foreach($this -> array_unique_tracking_db as $data){
                    $this -> createTrackingDb($data);
                }
            }
            
            return;
        }

        public function __destruct() {
            
        } 
    }
}