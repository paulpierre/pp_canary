<?php

namespace Traits {
    
    use Methods\Log as Log;
    
    use Database as Database;
    
    trait Db {
        
        protected static function createTrackingDb($_data){
            Log::set('_data',$_data,true);
            
            $db_array = Array(
                'tracking_number' => $_data['tracking_number'],
                'fulfillment_id' => $_data['fulfillment_id'],
                'order_id' => $_data['order_id'],
                'vendor_id' => $_data['vendor_id'],
                'order_receipt_id' => $_data['order_receipt_id'],
                'tracking_status' => 0,
                'tracking_tmodified' => date('Y-m-d H:i:s'),
                'tracking_tcreate' => date('Y-m-d H:i:s'),
                
            );
            
            if(!TEST_MODE){
                $db_instance = new Database();
                $result = $db_instance->db_create('vendor_tracking',$db_array);
                unset($db_instance);
            }
            
            Log::set('DATABASE - CREATED NEW TRACKING','order_id: ' . $_data['order_id'] . ' | fulfillment_id: ' . $_data['fulfillment_id'] . ' | tracking_number: ' . $_data['tracking_number'],true);
            Log::set(' .');
            
            return;
        }
        
        protected static function updateFulfillmentDb($_data){
                        
            $q = "UPDATE "
                    . "fulfillments "
                . "SET "
                    . "fulfillment_tracking_number = '" . $_data['tracking_number'] . "', "
                    . "fulfillment_tmodified= '" . $_data['tracking_tmodified'] . "' "
                . "WHERE "
                    . "fulfillment_id = '" . $_data['fulfillment_id'] . "';";
            
            if(!TEST_MODE){
                $db_instance = new Database();
                $data = $db_instance->db_query($q,DATABASE_NAME);
                unset($db_instance);
            }
            
            Log::set('DATABASE - UPDATED FULFILLMENT','order_id: ' . $_data['order_id'] . ' | fulfillment_id: ' . $_data['fulfillment_id'] . ' | tracking_number: ' . $_data['tracking_number'],true); 
            Log::set('sql',$q,true);
            Log::set(' .');
            
            return;
        }
        
        protected static function deleteTrackingDb($_data){
                        
            $q = "DELETE FROM "
                    . "vendor_tracking "
                . "WHERE "
                    . "tracking_number = '" . $_data['tracking_number'] . "';";
            
            if(!TEST_MODE){
                $db_instance = new Database();
                $data = $db_instance->db_query($q,DATABASE_NAME);
                unset($db_instance);
            }
            
            Log::set('DATABASE - CANCELLED TRACKING','tracking_number: ' . $_data['tracking_number'],true); 
            Log::set('sql',$q,true);
            Log::set(' .');
            
            return;
        }
        
        protected static function deleteFulfillmentDb($_data){
                        
            $q = "DELETE FROM "
                    . "fulfillments "
                . "WHERE "
                    . "fulfillment_tracking_number = '" . $_data['tracking_number'] . "';";
            
            if(!TEST_MODE){
                $db_instance = new Database();
                $data = $db_instance->db_query($q,DATABASE_NAME);
                unset($db_instance);
            }
            
            Log::set('DATABASE - DELETE FULFILLMENT','tracking_number: ' . $_data['tracking_number'],true);  
            Log::set('sql',$q,true);
            Log::set(' .');
            
            return;
        }
        
        protected static function createFulfillmentDb($_data){
            Log::set('_data',$_data,true);
            
            $db_array = Array(
                'order_id' => $_data['order_id'],
                'order_shopify_id' => $_data['order_shopify_id'],
                'fulfillment_shopify_id' => $_data['fulfillment_shopify_id'],
                'fulfillment_vendor_id' => $_data['vendor_id'],
                'fulfillment_tracking_number' => $_data['tracking_number'],
                'fulfillment_tracking_number_tcreate' => date('Y-m-d H:i:s'),
                'fulfillment_topen' => date('Y-m-d H:i:s'),
                'fulfillment_tmodified' => date('Y-m-d H:i:s'),
                'fulfillment_tcreate' => date('Y-m-d H:i:s'),
            );  
            
            if(!TEST_MODE){
                $db_instance = new Database();
                $result = $db_instance->db_create('fulfillments',$db_array);
                unset($db_instance);
            } else {
                $result = mt_rand(10000,99999);
            }
            
            Log::set('DATABASE - CREATED FULFILLMENT','order_id: ' . $_data['order_id'] . ' | fulfillment_id: ' . $result,true);
            Log::set('DATABASE - CREATED FULFILLMENT','response: ' . $result,true);
            Log::set(' .');
            
            return $result;
        }
    }    
}