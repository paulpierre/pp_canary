<?php

namespace Excel {
    
    use \Methods\Log as Log;
    
    class Sheets {
    
        protected $sheets;
        protected $sheets_array = Array();

        public function __construct($vendor_id, $file_name, $vendor_array) {
            
            $workbook = new Workbook($vendor_id, $file_name, $vendor_array);
            $this -> sheets = $workbook -> get();
            
            return;        
        }    

        public function __clone() {

        }

        protected function get(){

            return $this -> sheets_array;
        }    

        protected function set($index,$sheet){

            $this -> sheet_array[$index] = $sheet;

            return;
        }

        public function __destruct() {

        }
    }    
}