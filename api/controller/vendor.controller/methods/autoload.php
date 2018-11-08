<?php


class Autoload {
    
    public static function initialize(){
        
        spl_autoload_register(__CLASS__."::_autoload");
        
        return;
    }

    protected static function _autoload($class){
        
        $namespace_array = explode('\\',$class);
        
        $class_matches = Array();
        preg_match_all('((?:^|[A-Z])[^A-Z]*)', array_pop($namespace_array), $class_matches);
        $class_file = strtolower(join("_", array_filter($class_matches[0])));
        
        $path = strtolower(join(DIRECTORY_SEPARATOR, $namespace_array));
        
        $file_api = API_DIRECT_PATH . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'vendor.controller' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $class_file . '.php';
        $path_lib = API_DIRECT_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $class_file . '.php';
        
        
        if(file_exists($file_api)){
            
            include($file_api);
            
        } elseif(file_exists($path_lib)) {
            
            include($path_lib);
            
        } else {
            
            throw new Error("Unable to load class: $class");
        }
        
        
        return;
    }
}

