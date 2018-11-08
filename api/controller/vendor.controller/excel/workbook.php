<?php

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
            
            if(sizeof($this -> workbook) == 0){
                $this -> getWorkbook();
            }
            
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
                        //Log::set('row',$_row,true);
                        //if(isset($_row[0]) && !empty(trim($_row[0])) && trim($_row[0]) != '')
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
}