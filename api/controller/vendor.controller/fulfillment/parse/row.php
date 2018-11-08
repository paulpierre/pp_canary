<?php

namespace Fulfillment\Parse {
    
    use Excel\Row as ExcelRow;  
    use Methods\Log as Log;
    
    use Database as Database;
    
    class Row extends ExcelRow {
        
        private $chinapost_regex = TRACKING_REGEX_CHINAPOST;
        
        private $order_receipt_id_column_index;
        private $sku_column_index;  
        private $tracking_number_column_index;
                
        private $row_status = ROW_STATUS_SUCCESS;
        private $row_action = ROW_ACTION_NONE;
        private $row_error = ERROR_ROW_PARSING_NO_ERROR;
        
        private $order_receipt_id;        
        private $tracking_number;
        private $sku;
        
        private $order_shopify_id;
        private $fulfillment_shopify_id;
        
        private $order_id;        
        private $fulfillment_id;
        
        private $item_shopify_id;
        private $item_shopify_product_id;
        private $item_shopify_variant_id;
        
        private $flag_is_empty_fulfilment_tracking_number = 0;
        
        public function __construct($row, $order_receipt_id_column_index, $tracking_number_column_index, $sku_column_index) {

            parent::__construct($row);
            
            $this -> order_receipt_id_column_index = $order_receipt_id_column_index;
            $this -> tracking_number_column_index = $tracking_number_column_index;
            $this -> sku_column_index = $sku_column_index;
            
            return;
        }    

        public function __clone() {

        }

        public function get(){
            
            if(sizeof($this -> row_array) == 0){            
                $this -> parseRow();            
            }
            
            $this -> row_array = Array (
                'row' => $this -> row,
                'data' => Array (
                    'status'                    => $this -> row_status,
                    'error'                     => $this -> row_error,
                    'action'                    => $this -> row_action,
                    'order_receipt_id'          => $this -> order_receipt_id,
                    'tracking_number'           => $this -> tracking_number,
                    'order_shopify_id'          => $this -> order_shopify_id,
                    'fulfillment_shopify_id'    => $this -> fulfillment_shopify_id,
                    'order_id'                  => $this -> order_id,
                    'fulfillment_id'            => $this -> fulfillment_id,
                    'sku'                       => $this -> sku,
                    'item_shopify_id'           => $this -> item_shopify_id,
                    'item_shopify_product_id'   => $this -> item_shopify_product_id,
                    'item_shopify_variant_id'   => $this -> item_shopify_variant_id,
                    'vendor_id'                 => VENDOR_ID
                    
                )
            );
            
            return $this -> row_array;
        }
        
        /*
         * +---------------+
         * | DECISION TREE |
         * +---------------+
         * |
         * |
         * getResultsOrderReceiptIdCell()    
         *     |                |
         *     |                |
         *     |                YES | getResultsFromOrdersAndFulfillments()
         *     |                                |        |
         *     |                                |        |
         *     |                                |        YES ---> | getResultsTrackingExists()
         *     |                                |                        |        |
         *     |                                |                        |        |
         *     |                                |                        |        YES --> |-> ROW_ACTION_NONE_DATA_UP_TO_DATE | SHEET_STATUS_SUCCESS | ERROR_ROW_PARSING_NO_ERROR
         *     |                                |                        |
         *     |                                |                        |
         *     |                                |if not tracking number  NO -> getResultsIsTrackingSet
         *     |                                |----------------                    |        |
         *     |                                                |                    |        |
         *     |                                                |                    |        YES |-> ROW_ACTION_CREATE_TRACKING | SHEET_STATUS_SUCCESS | ERROR_ROW_PARSING_NO_ERROR
         *     |                                                |                    |                --------------------------
         *     |                                                |                    |                
         *     NO |-> getResultsFromTrackingNumberCell() <-| NO                    NO |-> ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING | SHEET_STATUS_SUCCESS | ERROR_ROW_PARSING_NO_ERROR
         *             |            |                                                     ---------------------------------------------
         *             |            |                                                                
         *             |            |                                                                
         *             |            |            
         *             |            |            
         *             |            YES ---------->  | getResultsFromFulfillments()
         *             |                                        |        |
         *             |                                        |        |
         *             |                                        |        YES ---> | getResultsTrackingExists()
         *             |                                        |                    |        |
         *             |                                        |                    |        |
         *             |                                        |                    |        YES |-> ROW_ACTION_NONE_DATA_UP_TO_DATE | SHEET_STATUS_SUCCESS | ERROR_ROW_PARSING_NO_ERROR
         *             |                                        |                    |
         *             |                                        |                    |
         *             |                                        |                    NO -> getResultsIsTrackingSet
         *             |                                        |                                        |        |
         *             |                                        |                                        |        |
         *             |                                        NO |-> getResultsFromOrders()            |        YES |-> ROW_ACTION_CREATE_TRACKING | SHEET_STATUS_SUCCESS | ERROR_ROW_PARSING_NO_ERROR
         *             |                                                    |        |                   |                --------------------------
         *             |                                                    |        |                   |
         *             |                                                    |        |                    NO |-> ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING | SHEET_STATUS_SUCCESS | ERROR_ROW_PARSING_NO_ERROR
         *             |                                                    |        |                           --------------------------------------------- 
         *             |                                                    |        |
         *             |                                                    |        YES |-> getResultSkuCell()
         *             |                                                    |                |        |
         *             |                                                    |                |        |
         *             |                                                    |                |        YES |-> getResultItems()
         *             |                                                    |                |                |        |    
         *             |                                                    |                |                |        |
         *             |                                                    |                |                |        |
         *             |                                                    |                |                |        YES |-> ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING | SHEET_STATUS_WARNING | ERROR_ROW_PARSING_FULFILLMENT_ID_NOT_FOUND
         *             |                                                    |                |                |                ---------------------------------------------
         *             |                                                    |                |                |
         *             |                                                    |                |                NO -> ROW_ACTION_NONE | ROW_STATUS_FAILURE | ERROR_ROW_PARSING_ITEM_NOT_FOUND
         *             |                                                    |                |
         *             |                                                    |                |
         *             |                                                    |                NO -> ROW_ACTION_NONE | ROW_STATUS_FAILURE | ERROR_ROW_PARSING_SKU_NOT_FOUND
         *             |                                                    |
         *             |                                                    |
         *             |                                                    NO -> ROW_ACTION_NONE | ROW_STATUS_FAILURE | ERROR_ROW_PARSING_ORDER_ID_NOT_FOUND
         *             |        
         *             |        
         *             NO -> ROW_ACTION_NONE | ROW_STATUS_FAILURE | ERROR_ROW_PARSING_NO_RECEIPT_ID_NO_TRACKING_NUMBER
         *         
         */
        
        private function parseRow(){
            
            $this -> getResultsOrderReceiptIdCell();
            
            return;
        }
        
        private function getResultsOrderReceiptIdCell(){
            
            $this -> setOrderReceiptIdCell();
            
            if($this -> row_error != ERROR_ROW_PARSING_ORDER_RECEIPT_ID_NOT_FOUND) {
                
                $this -> getResultsFromOrdersAndFulfillments();
                
            } else {
                
                $this -> getResultsFromTrackingNumberCell();
            }
                        
            return;
        }
        
        private function getResultsFromTrackingNumberCell(){
            
            $this -> setTrackingNumberCell();
            
            if($this -> row_error != ERROR_ROW_PARSING_TRACKING_NUMBER_NOT_FOUND) {
                
                $this -> getResultsFromFulfillments();
                
            } else {
                
                $this -> row_status = ROW_STATUS_FAILURE;
                $this -> row_action = ROW_ACTION_NONE;
                $this -> row_error  = ERROR_ROW_PARSING_NO_RECEIPT_ID_NO_TRACKING_NUMBER;
            }
        }
        
        private function getResultsFromOrdersAndFulfillments(){
            
            $data = $this -> getDataFromOrdersAndFulfillments();
            
            if(!empty($data) && empty($data[0]['fulfillment_tracking_number'])){
                $this -> setTrackingNumberCell();
                $this -> flag_is_empty_fulfilment_tracking_number = 1;
            }
            
            if(!empty($data) && (!empty($data[0]['fulfillment_tracking_number']) || !empty($this -> tracking_number))){

                $this -> order_id                   = $data[0]['order_id'];
                $this -> fulfillment_id             = $data[0]['fulfillment_id'];
                $this -> order_shopify_id           = $data[0]['order_shopify_id'];
                $this -> fulfillment_shopify_id     = $data[0]['fulfillment_shopify_id'];
                
                if(empty($this -> tracking_number)){
                    $this -> tracking_number            = $data[0]['fulfillment_tracking_number'];
                }
                
                $this -> getResultsTrackingExists();

            } else {
                
                $this -> getResultsFromTrackingNumberCell();
                
            }
            
            return;
        } 
        
        private function getResultsFromFulfillments(){
            
            $data = $this -> getDataFromFulfillments();
            
            if(!empty($data)){
                
                $this -> order_id                   = $data[0]['order_id'];
                $this -> fulfillment_id             = $data[0]['fulfillment_id'];
                $this -> order_shopify_id           = $data[0]['order_shopify_id'];
                $this -> fulfillment_shopify_id     = $data[0]['fulfillment_shopify_id'];
                
                $this -> getResultsTrackingExists();
                
            } else {
                
                $this -> getResultsFromOrders();
            }
        }
        
        private function getResultsTrackingExists(){
            
            $data = $this -> getTrackingExists();
            
            if(!empty($data)){
                
                $this -> row_status = ROW_STATUS_SUCCESS;
                $this -> row_action = ROW_ACTION_NONE_DATA_UP_TO_DATE;
                $this -> row_error  = ERROR_ROW_PARSING_NO_ERROR;

            } else {
                
                $this -> getResultsIsTrackingSet();
            }
            
            return;
        }
        
        private function getResultsIsTrackingSet(){
            
            if(!empty($this -> tracking_number) && $this -> flag_is_empty_fulfilment_tracking_number == 0){
                
                if(!empty($this -> order_shopify_id)){
                
                    $this -> row_status = ROW_STATUS_SUCCESS;
                    $this -> row_action = ROW_ACTION_CREATE_TRACKING;
                    $this -> row_error  = ERROR_ROW_PARSING_NO_ERROR;
                
                } else {
                    
                    $data = $this -> getDataFromOrders();
            
                    if(!empty($data)){

                        $this -> order_id                   = $data[0]['order_id'];
                        $this -> order_shopify_id           = $data[0]['order_shopify_id'];

                        $this -> row_status = ROW_STATUS_SUCCESS;
                        $this -> row_action = ROW_ACTION_CREATE_TRACKING;
                        $this -> row_error  = ERROR_ROW_PARSING_NO_ERROR;

                    } else {

                        $this -> row_status = ROW_STATUS_FAILURE;
                        $this -> row_action = ROW_ACTION_NONE;
                        $this -> row_error  = ERROR_ROW_PARSING_ORDER_ID_NOT_FOUND;
                    }
                }
                
            } else {

                $this -> row_status = ROW_STATUS_SUCCESS;
                $this -> row_action = ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING;
                $this -> row_error  = ERROR_ROW_PARSING_NO_ERROR;
            }
            
            return;
        }
        
        private function getResultsFromOrders(){
            
            $data = $this -> getDataFromOrders();
            
            if(!empty($data)){

                $this -> order_id                   = $data[0]['order_id'];
                $this -> order_shopify_id           = $data[0]['order_shopify_id'];
                
                $this -> getResultSkuCell();

            } else {

                $this -> row_status = ROW_STATUS_FAILURE;
                $this -> row_action = ROW_ACTION_NONE;
                $this -> row_error  = ERROR_ROW_PARSING_ORDER_ID_NOT_FOUND;
            }
            
            return;
        }
        
        private function getResultSkuCell(){
            
            $data = $this -> getSkuCell();            
            $sku = self::getSkuRegEx($data);
            
            if($sku){
                 
                $this -> sku = $sku;
                $this -> getResultItems();

            } else {

                $this -> row_status = ROW_STATUS_FAILURE;
                $this -> row_action = ROW_ACTION_NONE;
                $this -> row_error  = ERROR_ROW_PARSING_SKU_NOT_FOUND;
                
            }
            
            return;
        }
        
        private function getResultItems(){
            
            $data = $this -> getItems();
                            
            if(!empty($data)){

                $this -> item_shopify_id            = $data[0]['item_shopify_id'];
                $this -> item_shopify_product_id    = $data[0]['item_shopify_product_id'];
                $this -> item_shopify_variant_id    = $data[0]['item_shopify_variant_id']; 

                $this -> row_status = ROW_STATUS_WARNING;
                $this -> row_action = ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING;
                $this -> row_error = ERROR_ROW_PARSING_FULFILLMENT_ID_NOT_FOUND;

            } else {

                $this -> row_status = ROW_STATUS_FAILURE;
                $this -> row_action = ROW_ACTION_NONE;
                $this -> row_error = ERROR_ROW_PARSING_ITEM_NOT_FOUND;

            }
            
            return;
        }        
        
        private function setOrderReceiptIdCell(){
            
            $this -> order_receipt_id = $this -> getOrderReceiptIdCell();
            
            if($this -> order_receipt_id == '' || $this -> order_receipt_id == false || empty($this -> order_receipt_id) || is_null($this -> order_receipt_id)){
                
                $this -> row_error = ERROR_ROW_PARSING_ORDER_RECEIPT_ID_NOT_FOUND;
                
                return;
            } 
            
            if(stripos($this -> order_receipt_id,'omgt') < 0 || empty(stripos($this -> order_receipt_id,'omgt'))) {
                $this -> order_receipt_id = '#OMGT6' . $this -> order_receipt_id;
            }
            
            return;
        }
        
        private function setTrackingNumberCell(){
                    
            if($this -> tracking_number_column_index === false){
            
                $this -> tracking_number_column_index = $this -> getTrackingNumberChinaPostCell();

                if($this -> tracking_number_column_index === false){

                    $this -> row_error = ERROR_ROW_PARSING_TRACKING_NUMBER_NOT_FOUND;

                    return;
                }
            }
            $this -> tracking_number = $this -> row[$this -> tracking_number_column_index];
            
            return;
        }
        
        private function getOrderReceiptIdCell(){
            
            if(!empty($this -> row[$this -> order_receipt_id_column_index])){
                return $this -> row[$this -> order_receipt_id_column_index];
            }
            
            return false;
        }
        
        private function getTrackingNumberChinaPostCell(){
            
            $_count = 0;
            foreach ($this -> row as $c) {

                if (preg_match($this -> chinapost_regex, $c)) {
                    return $_count;
                }
                $_count++;
            }

            return false;
        }        
        
        private function getSkuCell(){
            
            if(!empty($this -> row[$this -> sku_column_index])){
                return $this -> row[$this -> sku_column_index];
            }
            
            return false;
        }
        
        private function getDataFromOrdersAndFulfillments(){
            
            $db_instance = new Database();
            $q = 'SELECT O.order_id as order_id, F.fulfillment_id as fulfillment_id, F.order_shopify_id as order_shopify_id, F.fulfillment_tracking_number as fulfillment_tracking_number, F.fulfillment_shopify_id as fulfillment_shopify_id FROM orders O INNER JOIN fulfillments F ON F.order_id = O.order_id WHERE O.order_receipt_id ="' . $this -> order_receipt_id .'";';
            $data = $db_instance->db_query($q,DATABASE_NAME);
            unset($db_instance);
            
            return $data;
        }
        
        private function getDataFromFulfillments(){
            
            $db_instance = new Database();
            $data = $db_instance->db_retrieve('fulfillments', Array('order_id', 'fulfillment_id', 'order_shopify_id', 'fulfillment_shopify_id'), Array('fulfillment_tracking_number' => $this -> tracking_number));
            unset($db_instance);
            
            return $data;
        }
        
        private function getDataFromOrders(){
            
            $db_instance = new Database();
            $q = "SELECT order_id, order_shopify_id FROM orders WHERE order_receipt_id='" . $this -> order_receipt_id . "';";
            $data = $db_instance->db_query($q,DATABASE_NAME);
            unset($db_instance);
            
            return $data;
        }
                
        private function getTrackingExists(){
            
            $db_instance = new Database();
            $data = $db_instance->db_retrieve('vendor_tracking', Array('order_receipt_id'), Array('tracking_number' => $this -> tracking_number, 'order_id' => $this -> order_id, 'fulfillment_id' => $this -> fulfillment_id));
            unset($db_instance);
            
            return $data;
        }
        
        private function getItems(){
                        
            $db_instance = new Database();
            $q = "SELECT item_shopify_id, item_shopify_product_id, item_shopify_variant_id FROM items WHERE order_id='" . $this -> order_id . "' AND item_sku='" . $this -> sku . "';";
            $data = $db_instance->db_query($q,DATABASE_NAME);
            unset($db_instance);
            
            return $data;
        }
        
        private static function getSkuRegEx($sku){
            
            $a = explode("*", $sku);
            
            return sizeof($a) > 0?$a[0]:false;
        }
        
        public function __destruct() {

        }
    }
}