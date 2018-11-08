<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Excel {
    
    use \SpreadsheetReader as SpreadsheetReader;
    use \Methods\Log as Log;
    
    class Workbook {
    
        protected $vendor_id;
        protected $file_name;
        protected $vendor_array;
        protected $path;
        protected $file_path;

        public $workbook = Array();

        private $allowed_mime = Array('xls','xlsx');

        public function __construct($vendor_id, $file_name, $vendor_array) {
            
            $this -> vendor_id = $vendor_id;
            $this -> file_name = $file_name;
            $this -> vendor_array = $vendor_array;
            $this -> path = DATA_PATH . 'tracking_numbers/' . $this -> vendor_array[$this -> vendor_id]['file'] .'/';
            $this -> file_path = $this -> path . $this -> file_name;

            if(in_array(self::getMime($this -> file_name), $this -> allowed_mime)) $this -> getWorkbook();
            
            return;
        }    

        public function __clone() {

        }   

        public function get(){
            
            Log::set('start');
            
            if(sizeof($this -> workbook) == 0){
                $this -> getWorkbook();
            }
            
            Log::set('end', $this -> workbook);
            
            return $this -> workbook;
        }

        private static function getMime($file_name){
            $f = explode('.',$file_name);
            return end($f);        
        }

        private function getWorkbook(){        

            include(LIB_PATH . 'php-excel-reader/excel_reader2.php');
            include(LIB_PATH . 'SpreadsheetReader.php');

            $excel = new SpreadsheetReader($this -> file_path);

            $sheets = $excel->Sheets();

            foreach($sheets as $sheet_index => $x){

                $excel->ChangeSheet($sheet_index);

                $this -> workbook[$sheet_index]['name'] = $sheets[$sheet_index];

                foreach($excel as $index => $row){
                    
                    $_row = self::getSanitizeUtfArray($row);
                    
                Log::set('ROW - ORD','ORD:' . $_row[0] . " | ". ord($_row[0]) . " | ". htmlspecialchars($_row[0]),true);
                    
                    if(!empty(trim($_row[0])) & trim($_row[0]) != '') 
                        $this -> workbook[$sheet_index]['rows'][] = $_row;
                }
            }

            return;
        }
        
        private static function getSanitizeUtfArray($_array){
            
            $array = Array();
            foreach($_array as $k => $v){
                $array[] = self::getSanitizeUtfString($v);
            }
            
            return $array;
        }
                
        private static function getSanitizeUtfString($string){
            
            return mb_convert_encoding($string, "UTF-8", "UTF-8");
        }
        
        public function __destruct() {

        }
    }
    
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
    
    class Sheet {
        
        public $sheet;

        protected $name;
        protected $header;
        protected $rows;
        protected $sheet_array = Array();

        public function __construct($sheet) {

            $this -> sheet = $sheet;
            $this -> name = $sheet['name'];
            $this -> header = $sheet['rows'][0];
            unset($sheet['rows'][0]);
            $this -> rows = $sheet['rows'];

            return;
        }    

        public function __clone() {

        }    

        protected function get(){

            return $this -> sheet_array;        
        }     

        protected function set($index,$row){

            $this -> sheet_array[$index] = $row;

            return;
        }

        public function __destruct() {

        }
    }
    
    class Row {
    
        protected $row;
        protected $row_array = Array();

        public function __construct($row) {

            $this -> row = $row;

            return;
        }    

        public function __clone() {

        }   

        protected function get(){

            return $this -> row_array;        
        }     

        protected function set($index,$row){

            $this -> row_array[$index] = $row;

            return;
        }

        public function __destruct() {

        }
    }
}