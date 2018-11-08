<?php

namespace Excel {
    
    use \Methods\Log as Log;
    
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
}