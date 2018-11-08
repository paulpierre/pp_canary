<?php

namespace Methods {

    class Log {
        
        private static $registry = Array();
        
        public static function init($file_controller_id = null){

            if(!defined('FILE_CONTROLLER_ID')){
                if($file_controller_id){
                    define('FILE_CONTROLLER_ID',$file_controller_id);
                } else {
                    define('FILE_CONTROLLER_ID',date('YmdHis') . '_' . session_id());
                }
            }
            
            self::clear(FILE_CONTROLLER_ID);
            
            self::save('START: ' . date('Y-m-d H:i:s') . " | " . number_format(microtime(true),8,'.',''));
            self::setBigDivider();
            self::setBigDivider();
            self::save(PHP_EOL);
            
            return;
        }

        public static function set($trigger,$parameters = null,$force = false){

            //if(TEST_MODE){
                //$class_array = explode("\\",debug_backtrace()[1]['class']);
                //self::save(number_format(microtime(TRUE),12,'.','') . " | " . end($class_array) . " | " . debug_backtrace()[1]['function'] . ": $trigger  | " . date('Y-m-d H:i:s'));
                
                $_triger = $trigger;
                
                if(substr($trigger, -2) == ' _' || substr($trigger, -2) == ' .'){                    
                    $_triger = substr($trigger, 0, -2);
                }
                
                
                if($parameters != null){
                    if(TEST_DETAILS == 1 || $force == true){
                        
                        if(is_array($parameters)){                        
                            self::save($_triger);
                            self::save($parameters);
                        } else {
                            self::save($_triger . ' -> ' . $parameters);
                        }
                    }
                } else {
                    if(substr($trigger, -2) != ' _' && substr($trigger, -2) != ' .') self::save($_triger);
                }
                
                if(substr($trigger, -2) == ' _'){                    
                    self::setBigDivider();
                }
                
                if(substr($trigger, -2) == ' .'){                    
                    self::setSmallDivider();
                }
            //};

            return;
        }
        
        public static function clear($file){
            
            if(file_exists(DATA_PATH .  "tracking_numbers/" . $file . ".txt")) unlink(DATA_PATH .  "tracking_numbers/" . $file . ".txt");
            
            return;
        }
        
        public static function curl($headers){
            
            self::fp($headers,date('Y_m_d') . '_curl');
            
            return;
        }
        
        private static function setBigDivider(){
            
            self::save('----------------------------------------------------------------------------------------------------------');

            return;
        }
        
        private static function setSmallDivider(){
            
            self::save('..........................................................................................................');

            return;
        }

        private static function save($value){
            
            if(!defined('FILE_CONTROLLER_ID')){
                self::init();
            }
            
            if(is_array($value))
            {
                $value = print_r($value,true);
            }

            self::fp($value,FILE_CONTROLLER_ID);
            
            return;
        }
        
        private static function fp($string,$file){
            
            $string .= PHP_EOL;
            $fp = fopen(DATA_PATH .  "tracking_numbers/" . $file . ".txt", 'a');
            fwrite($fp, $string);
            fclose($fp);
            
            return;
        }
        
        public static function destruct(){
            
            self::save(PHP_EOL);
            self::setBigDivider();
            self::setBigDivider();
            self::save(PHP_EOL . 'END: ' . date('Y-m-d H:i:s') . " | " . number_format(microtime(true),8,'.',''));
            
            return;
        }
    }

}