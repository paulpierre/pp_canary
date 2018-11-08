<?php

namespace Traits {
    
    use Methods\Log as Log;
        
    trait ArrayUniqueFulfillmentApi {
        
        private function setArrayUniqueFulfillmentApi(){
            
            $_result = Array();
            
            if(isset($this -> data[$this -> action])){
            
                $i = 0;
                foreach($this -> data[$this -> action] as $_k => $_v){
                    
                    $duplicate = 0;
                    if(sizeof($_result) > 0){
                        foreach ($_result as $k => $v) {

                            if( isset($v['order_shopify_id']) &&
                                isset($v['fulfillment_shopify_id']) &&
                                $_v['order_shopify_id']         == $v['order_shopify_id'] && 
                                $_v['fulfillment_shopify_id']   == $v['fulfillment_shopify_id']
                                    ){
                                $duplicate = 1;
                                                                
                                if(isset($_v['tracking_number']) && !$this -> isTrackingNumberInShopify($_v['order_shopify_id'], $_v['fulfillment_shopify_id'], $_v['tracking_number'])){
                                    $_result[$k]['tracking_numbers'][] = $_v['tracking_number'];
                                }
                                                                
                                if(isset($_v['item_shopify_id']) && !$this -> isLineItemInShopify($_v['order_shopify_id'], $_v['fulfillment_shopify_id'], $_v['item_shopify_id'])){                                    
                                    $_result[$k]['line_items'][] = $_v['item_shopify_id'];
                                }
                            }

                        }
                    }

                    if($duplicate == 0 && isset($_v['order_shopify_id']) && isset($_v['fulfillment_shopify_id'])){

                        $_result[$i] = Array (
                                                'order_shopify_id'              => $_v['order_shopify_id'],
                                                'fulfillment_shopify_id'        => $_v['fulfillment_shopify_id']
                                            );
                        
                        if($this -> isFulfillmentInShopify($_v['order_shopify_id'], $_v['fulfillment_shopify_id'])){
                                                        
                            $_order_shopify_cancelled_at = $this -> shopify_orders[$_v['order_shopify_id']]['cancelled_at'];
                            $_order_shopify_closed_at    = $this -> shopify_orders[$_v['order_shopify_id']]['closed_at'];

                            $order_shopify_closed_at    = is_null(trim($_order_shopify_cancelled_at))?0:strtotime($_order_shopify_closed_at);
                            $order_shopify_cancelled_at = is_null(trim($_order_shopify_cancelled_at))?0:strtotime($_order_shopify_cancelled_at);

                            $order_status = 'opened';

                            if($order_shopify_closed_at != 0 || $order_shopify_cancelled_at !=0){
                                $order_status = $order_shopify_closed_at > $order_shopify_cancelled_at?'closed':'cancelled';
                            }
                            
                            $_result[$i] = Array (
                                                    'order_shopify_id'              => $_v['order_shopify_id'],
                                                    'tracking_number'               => $_v['tracking_number'],
                                                    'vendor_id'                     => $_v['vendor_id'],                            
                                                    'order_receipt_id'              => $_v['order_receipt_id'],
                                                    'fulfillment_shopify_id'        => $_v['fulfillment_shopify_id'],
                                                    'order_shopify_status'          => $order_status,
                                                    'order_shopify_cancelled_at'    => $order_shopify_cancelled_at,
                                                    'order_shopify_closed_at'       => $order_shopify_closed_at,
                                                );
                        }
                        
                        $_result[$i]['tracking_numbers'] = Array();
                        if(isset($_v['tracking_number']) && !$this -> isTrackingNumberInShopify($_v['order_shopify_id'], $_v['fulfillment_shopify_id'], $_v['tracking_number'])){                            
                            $_result[$i]['tracking_numbers'][] = $_v['tracking_number'];                            
                        }
                                                
                        $_result[$i]['line_items'] = Array();
                        if(isset($_v['item_shopify_id']) && !$this -> isLineItemInShopify($_v['order_shopify_id'], $_v['fulfillment_shopify_id'], $_v['item_shopify_id'])){                                    
                            $_result[$i]['line_items'][] = $_v['item_shopify_id'];
                        }

                        $i++;
                    }
                }
                
                $this -> array_unique_fulfillment_api = $_result;
            }
            
            return;
        }
        
        private function isFulfillmentInShopify($order_shopify_id, $fulfillment_shopify_id){
            
            if(array_key_exists($order_shopify_id, $this -> shopify_orders)){
                foreach($this -> shopify_orders[$order_shopify_id]['fulfillments'] as $_fulfillment){                
                    if($_fulfillment['id'] == $fulfillment_shopify_id)return true;
                }
            }
            
            return false;
        }
        
        private function isTrackingNumberInShopify($order_shopify_id, $fulfillment_shopify_id, $tracking_number){
            
            if(array_key_exists($order_shopify_id, $this -> shopify_orders)){
                foreach($this -> shopify_orders[$order_shopify_id]['fulfillments'] as $_fulfillment){                
                    if($_fulfillment['id'] == $fulfillment_shopify_id){
                        foreach($_fulfillment['tracking_numbers'] as $_tracking_number){
                            if($_tracking_number == $tracking_number) return true;
                        }
                    }
                }
            }
            
            return false;
        }
        
        private function isLineItemInShopify($order_shopify_id, $fulfillment_shopify_id, $line_item){
            
            if(array_key_exists($order_shopify_id, $this -> shopify_orders)){
                foreach($this -> shopify_orders[$order_shopify_id]['fulfillments'] as $_fulfillment){                
                    if($_fulfillment['id'] == $fulfillment_shopify_id){
                        foreach($_fulfillment['line_items'] as $_line_items){
                            if($_line_items['id'] == $line_item) return true;
                        }
                    }
                }
            }
            
            return false;
        }
    }
}