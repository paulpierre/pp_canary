<?php

namespace Excel {
    
    use \Methods\Log as Log;
    
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