<?php

    require_once ( __DIR__ . "/../config.php" );

    class Collective implements CollectiveMethods {

        public static function doFlush(): VOID {
            
            if (!headers_sent()) {
                
                ini_set('zlib.output_compression', 0);
                header('Content-Encoding: none');
            }
            
            echo str_pad('', 4 * 1024);
            
            do {
                $flushed = @ob_end_flush();
            } while ($flushed);
            @ob_flush();
            flush();
            
        }

        public static function createPostVariables(Array $fields): String {
            
            $postvars = '';
            
            foreach($fields as $key=>$value) {
                $postvars .= $key . "=" . $value . "&";
            }
            
            $postvars = rtrim($postvars, '&');
            
            return $postvars;
    
        }

        public static function array_sort($array) {

            $converted_strings = array();
            $temp_array = array();
            $group = array();
            $result = array();
    
            $turkish_chars = array("ğ", "Ğ", "ç", "Ç", "ş", "Ş", "ü", "Ü", "ö", "Ö", "ı", "İ");
            $turkish_chars_equal = array("g", "G", "c", "C", "s", "S", "u", "U", "o", "O", "i", "I");

            for ($index = 0; $index < count($array); $index++) {
                if (in_array(mb_substr($array[$index], 0, 1, 'UTF-8'), $turkish_chars)) {
                    $target_index = 0;
                    for ($index2 = 0; $index2 < count($turkish_chars); $index2++) {
                        if (mb_substr($array[$index], 0, 1, 'UTF-8') === $turkish_chars[$index2]) {
                            $target_index = $index2;
                            break;
                        }
                    }
                    $count = 0;
                    $converted_strings[str_replace(mb_substr($array[$index], 0, 1, 'UTF-8'), $turkish_chars_equal[$target_index], $array[$index], $count)] = $array[$index];
                    array_push($temp_array, str_replace(mb_substr($array[$index], 0, 1, 'UTF-8'), $turkish_chars_equal[$target_index], $array[$index], $count));
                }else{
                    array_push($temp_array, $array[$index]);
                }
            }
    
            sort($temp_array);

            foreach ($temp_array as $arr) {
                $temp = $arr;
                if(!isset($group[substr($arr, 0, 1)])){
                    $group[substr($arr, 0, 1)] = array();
                }
                foreach ($converted_strings as $key => $value) {
                    if($temp === $key){
                        $temp = $value;
                    }
                }
                array_push($group[substr($arr, 0, 1)], $temp);
            }

            foreach ($group as $key => $value) {
                sort($value);
                foreach ($value as $index) {
                    array_push($result, $index);
                }
            }
            
            return $result;
    
        }

        public static function guidv4($data){

            assert(strlen($data) == 16);
    
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        }    

    }

?>