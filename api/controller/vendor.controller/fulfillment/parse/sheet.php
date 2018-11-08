<?php

namespace Fulfillment\Parse {
    
    use Excel\Sheet as ExcelSheet;    
    use Fulfillment\Parse\Sheets as Sheets;
    use Fulfillment\Parse\Row as Row;
    use Methods\Log as Log;
    
    use Database as Database;
    
    class Sheet extends ExcelSheet {
        
        private $sheet_status = SHEET_STATUS_SUCCESS;
        private $sheet_error = ERROR_SHEET_PARSING_NO_ERROR;
        private $order_receipt_id_column_index;
        private $tracking_number_column_index;
        private $sku_column_index;
        
        public function __construct($sheet) {
            
            parent::__construct($sheet);
            
            $this -> sheet_array = Array(
                                            'summary' => Array(
                                                                'rows' => Array(
                                                                                    'total'     => 0, 
                                                                                    'unknown'   => 0, 
                                                                                    'success'   => 0, 
                                                                                    'warning'   => 0, 
                                                                                    'failure'   => 0
                                                                                )
                                                                ),
                                            'sheet' => Array(
                                                                'name'      => null, 
                                                                'status'    => null,
                                                                'error'     => null,
                                                                'header'    => null,
                                                                'rows'      => Array()
                                                            ),
                                            'data' => Array(),
                                        );
            
            return;
        }    

        public function __clone() {

        }

        public function get(){
            
            if($this -> sheet_array['summary']['rows']['total'] == 0){
                $this -> setOrderReceiptIdColumnIndex();
                $this -> setTrackingNumberColumnIndex();
                $this -> setSkuColumnIndex();
                $this -> updateHeader();
                $this -> setRows();            
            }

            $this -> sheet_array['sheet']['name']   = $this -> name;
            $this -> sheet_array['sheet']['status'] = $this -> sheet_status;
            $this -> sheet_array['sheet']['error']  = $this -> sheet_error;
            $this -> sheet_array['sheet']['header'] = $this -> header;
            
            return $this -> sheet_array;
        }
        
        private function setOrderReceiptIdColumnIndex(){
            
            $this -> order_receipt_id_column_index = $this -> getOrderReceiptIdColumnIndex();
            
            if($this -> order_receipt_id_column_index === false){
                    
                $this -> sheet_status = SHEET_STATUS_FAILURE;
                $this -> sheet_error = ERROR_SHEET_PARSING_ORDER_RECEIPT_ID_NOT_FOUND;
                
            }
            
            return;
        }
        
        private function setTrackingNumberColumnIndex(){
            
            $this -> tracking_number_column_index = $this -> getTrackingNumberColumnIndex();
                        
            return;
        }
        
        private function setSkuColumnIndex(){
            
            $this -> sku_column_index = $this -> getSkuColumnIndex();
            
            if($this -> sku_column_index === false){
                    
                $this -> sheet_status = SHEET_STATUS_FAILURE;
                $this -> sheet_error = ERROR_SHEET_PARSING_SKU_NOT_FOUND;
                
            }
            
            return;
        }
        
        protected function updateHeader(){
            
            $header[] = 'Row ID';
            $header[] = 'Row Status';
            $header[] = 'Error';
            $header[] = 'Action';
            
            array_splice($this -> header, 0, 0, $header);
            
            return;
        }
        
        protected function setRows() {
            
            foreach($this -> rows as $index => $row) {
                
                $row = new Row($row, $this -> order_receipt_id_column_index, $this -> tracking_number_column_index, $this -> sku_column_index);
                $row_array = $row -> get();
                
                $this -> sheet_array['summary']['rows']['total']++;
                
                if($row_array['data']['status'] == ROW_STATUS_SUCCESS) {
                    $this -> sheet_array['summary']['rows']['success']++;
                } elseif($row_array['data']['status'] == ROW_STATUS_WARNING) {
                    $this -> sheet_array['summary']['rows']['warning']++;
                } elseif($row_array['data']['status'] == ROW_STATUS_FAILURE) {
                    $this -> sheet_array['summary']['rows']['failure']++;
                } else {
                    $this -> sheet_array['summary']['rows']['unknown']++;
                }
                                
                $row_id = Sheets::setRowId();
                                
                $update = [];
                $update[] = $row_id;
                $update[] = $row_array['data']['status'];
                $update[] = $row_array['data']['error'];
                $update[] = $row_array['data']['action'];
                
                array_splice($row_array['row'], 0, 0, $update);
                
                $this -> sheet_array['sheet']['rows'][$index] = $row_array['row'];
                            
                $row_array['data']['sheet'] = $this -> name;
                $row_array['data']['row'] = $index + 1;
                $row_array['data']['row_id'] = $row_id;
                
                $this -> sheet_array['data'][] = $row_array['data'];
            }
            
            return;
        }
        
        private function getOrderReceiptIdColumnIndex(){           
            
            $_count = 0;            
            foreach ($this -> header as $o) {
                if (
                    $o == '编码' ||
                    $o == '店铺单号'||
                    $o == '订单编号'||
                    $o == '订单号' ||
                    stripos($o,'店铺单号') > -1  ||
                    stripos(strtolower(preg_replace('/\s+/', ' ',$o)), 'order #') > -1 ||
                    stripos(strtolower(preg_replace('/\s+/', ' ',$o)), 'order num') > -1 ||
                    stripos(strtolower(preg_replace('/\s+/', ' ',$o)), 'order number') > -1
                ){
                    return $_count;
                }
                $_count++;
            }
            
            return false;

        }
        
        private function getTrackingNumberColumnIndex(){           
            
            $_count = 0;            
            foreach ($this -> header as $o) {
                if (
                    stripos($o,'跟踪号') > -1  ||
                    stripos(strtolower(preg_replace('/\s+/', ' ',$o)), 'tracking code') > -1 ||
                    stripos(strtolower(preg_replace('/\s+/', ' ',$o)), 'tracking number') > -1 ||
                    strtolower($o) == 'shipping'
                ){
                    return $_count;
                }
                $_count++;
            }
            
            return false;

        }
        
        private function getSkuColumnIndex(){           
            
            $_count = 0;            
            foreach ($this -> header as $o) {
                if (
                    stripos(strtolower($o), 'sku') > -1
                ){
                    return $_count;
                }
                $_count++;
            }
            
            return false;

        }

        public function __destruct() {

        }
    }
}
