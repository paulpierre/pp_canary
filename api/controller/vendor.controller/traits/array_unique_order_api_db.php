<?php

namespace Traits {
    
    use Methods\Log as Log;
        
    trait ArrayUniqueOrderApiDb {
        
        private $_line_items;
        
        private function setArrayUniqueOrderApiDb(){
            
            $_result = Array();
            
            foreach($this -> shopify_orders as $_shopify_order){
            
                $this -> _line_items = $_shopify_order['line_items'];
                
                if(isset($this -> data[$this -> action])){
                    
                    $_order_shopify_id;
                    $_tracking_number;
                    $_vendor_id;                    
                    $_order_receipt_id;                     
                    $_order_id;
                    $_line_items_array = Array();
                    
                    $_order_shopify_cancelled_at = $_shopify_order['cancelled_at'];
                    $_order_shopify_closed_at    = $_shopify_order['closed_at'];

                    $order_shopify_closed_at    = is_null(trim($_order_shopify_cancelled_at))?0:strtotime($_order_shopify_closed_at);
                    $order_shopify_cancelled_at = is_null(trim($_order_shopify_cancelled_at))?0:strtotime($_order_shopify_cancelled_at);
                    
                    $order_status = 'opened';

                    if($order_shopify_closed_at != 0 || $order_shopify_cancelled_at !=0){
                        $order_status = $order_shopify_closed_at > $order_shopify_cancelled_at?'closed':'cancelled';
                    }
                    
                    foreach($this -> data[$this -> action] as $_k => $_v){
                                                
                        if($_shopify_order['id'] == $_v['order_shopify_id']){
                            
                            $_order_shopify_id    = $_v['order_shopify_id'];
                            $_tracking_number     = $_v['tracking_number'];
                            $_vendor_id           = $_v['vendor_id'];                            
                            $_order_receipt_id    = $_v['order_receipt_id'];                           
                            $_order_id            = $_v['order_id'];
                                                        
                            foreach($this -> _line_items as $k => $v){
                                
                                if(isset($v['sku'])){
                                
                                    if($_v['sku'] == $v['sku']){
                                        
                                        $_line_items_array[] = Array('item' => $v['id'], 'sku' => $v['sku'], 'sheet' => $_v['sheet'], 'row' => $_v['row']);
                                        $this -> _line_items[$k] = Array();
                                    }
                                }
                            }
                        }
                    } 
                    
                    $_result[] = Array (
                                            'order_shopify_id'              => $_order_shopify_id,
                                            'tracking_number'               => $_tracking_number,
                                            'vendor_id'                     => $_vendor_id,                            
                                            'order_receipt_id'              => $_order_receipt_id,                            
                                            'order_id'                      => $_order_id,                            
                                            'order_shopify_status'          => $order_status,
                                            'order_shopify_cancelled_at'    => $order_shopify_cancelled_at,
                                            'order_shopify_closed_at'       => $order_shopify_closed_at,
                                            'line_items'                    => $_line_items_array
                                        );
                }     
            }        
                    
            $this -> array_unique_order_api_db = $_result;
                        
            return;
        }
        
    }
}