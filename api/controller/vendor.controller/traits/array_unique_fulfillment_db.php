<?php

namespace Traits {
    
    use Methods\Log as Log;
        
    trait ArrayUniqueFulfillmentDb {
        
        private function setArrayUniqueFulfillmentDb(){
            
            if(isset($this -> data[$this -> action])){
                $_result = Array();
                
                $i = 0;
                foreach($this -> data[$this -> action] as $_v){
                    
                    $duplicate = 0;                    
                    foreach ($_result as $v) {
                        
                        if( $_v['order_shopify_id']         == $v['order_shopify_id'] && 
                            $_v['order_id']                 == $v['order_id']
                                ){
                            $duplicate = 1;
                        }                        
                    }
                    
                    if($duplicate == 0){
                        
                        $_result[$i] = Array (
                                                'order_receipt_id'          => $_v['order_receipt_id'],
                                                'order_shopify_id'          => $_v['order_shopify_id'],
                                                'fulfillment_shopify_id'    => $_v['fulfillment_shopify_id'],
                                                'tracking_number'           => $_v['tracking_number'],
                                                'order_id'                  => $_v['order_id'],
                                                'fulfillment_id'            => $_v['fulfillment_id'],
                                                'vendor_id'                 => $_v['vendor_id'],
                                                'tracking_tmodified'        => date("Y-m-d H:i:s"),
                                                'tracking_tcreate'          => date("Y-m-d H:i:s")                                                
                                            );
                        
                        $i++;
                    }
                }
                                
                $this -> array_unique_fulfillment_db = $_result;
            }
            
            return;
            
        }
    }
}