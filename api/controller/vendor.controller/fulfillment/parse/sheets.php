<?php

namespace Fulfillment\Parse {
    
    use Excel\Workbook as ExcelWorkbook;
    use Excel\Sheets as ExcelSheets;    
    use Fulfillment\Parse\Sheet as Sheet;
      
    use Methods\Log as Log;
    
    use Database as Database;
    
    class Sheets extends ExcelSheets {
        
        public static $row_id = 0;
        
        private $_sheets_array;
        
        public function __construct($vendor_id, $file_name, $vendor_array) {
            
            Log::init('massfulfill_parse');
            Log::curl(">IN\t | " . date('Y-m-d H:i:s') . "\t | " . $_SERVER['REMOTE_ADDR'] . "\t | " . 'FULFILLMENT-PARSE');
            
            parent::__construct($vendor_id, $file_name, $vendor_array);
            
            define('VENDOR_ID',$vendor_id);
            define('CURRENT_TIMESTAMP',date("Y/m/d H:i:s"));
            
            $this -> sheets_array = Array(
                                            'status' => 0,
                                            'summary' => Array(
                                                                'vendor_id' => VENDOR_ID,
                                                                'name' => $file_name,
                                                                'timestamp' => CURRENT_TIMESTAMP,
                                                                'sheets' => Array(
                                                                                    'total'     => 0, 
                                                                                    'unknown'   => 0, 
                                                                                    'success'   => 0, 
                                                                                    'warning'  => 0, 
                                                                                    'failure'   => 0
                                                                                ),
                                                                'rows' => Array(
                                                                                    'total'     => 0, 
                                                                                    'unknown'   => 0, 
                                                                                    'success'   => 0, 
                                                                                    'warning'   => 0, 
                                                                                    'failure'   => 0
                                                                                )
                                                                ),
                                            'sheets' => Array(),
                                            'data' => Array()                
                                           );
            
            return;
        }    

        public function __clone() {

        } 

        public function get() {
            
            if($this -> sheets_array['summary']['sheets']['total'] == 0){            
                $this -> setSheets();            
            }
            
            Log::set('sheets_array', $this -> sheets_array, true);
            
            return $this -> sheets_array;
        }

        protected function setSheets() {

            foreach($this -> sheets as $index => $sheet) {
                
                $sheet = new Sheet($sheet);
                $sheet_array = $sheet -> get();
                
                $this -> sheets_array['summary']['sheets']['total']++;
                
                if($sheet_array['sheet']['status'] == SHEET_STATUS_SUCCESS) {
                    $this -> sheets_array['summary']['sheets']['success']++;
                } elseif($sheet_array['sheet']['status'] == SHEET_STATUS_WARNING) {
                    $this -> sheets_array['summary']['sheets']['warning']++;
                } elseif($sheet_array['sheet']['status'] == SHEET_STATUS_FAILURE) {
                    $this -> sheets_array['summary']['sheets']['failure']++;
                } else {
                    $this -> sheets_array['summary']['sheets']['unknown']++;
                }
                
                $this -> sheets_array['summary']['rows']['total']   += $sheet_array['summary']['rows']['total'];
                $this -> sheets_array['summary']['rows']['success'] += $sheet_array['summary']['rows']['success'];
                $this -> sheets_array['summary']['rows']['warning'] += $sheet_array['summary']['rows']['warning'];
                $this -> sheets_array['summary']['rows']['failure'] += $sheet_array['summary']['rows']['failure'];
                $this -> sheets_array['summary']['rows']['unknown'] += $sheet_array['summary']['rows']['unknown'];
                
                $this -> sheets_array['sheets'][$index] = $sheet_array['sheet'];
                
                foreach($sheet_array['data'] as $row){
                    
                    $this -> _sheets_array[$row['order_shopify_id']][$row['fulfillment_shopify_id']][$row['row_id']] = $row;
                }
            }
            
            $this -> setStatusNodeDataArray();
            
            $this -> sheets_array['status'] = 1;

            return;
        }
        
        public static function setRowId(){
            
            $row_id = self::$row_id;            
            self::$row_id++;
            
            return $row_id;
        }
        
        public static function getRowId(){
            
            return self::$row_id;
        }
        
        private function setStatusNodeDataArray(){
            
            $_array = $this -> _sheets_array;
            
            foreach($_array as $order => $order_object){                
                $status = 0;
                $_object = Array();
                foreach($order_object as $fulfillment => $fulfillment_object){
                    $action = 0;
                    foreach($fulfillment_object as $row_id => $object){
                        $status = $object['status'] > $status?$object['status']:$status;
                        $action = $object['action'] > $action?$object['action']:$action;
                        $_object[] = $object;
                    }
                }
                foreach($_object as $o){
                    $o['order_status'] = $status;
                    $array[$o['action']][$o['row_id']] = $o;
                }
            }
            $this -> sheets_array['data'] = $array;
            
            return;
        }
        
        public function __destruct() {
            
            Log::destruct();
            
        }
    }
}