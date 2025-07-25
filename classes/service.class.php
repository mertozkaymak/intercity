<?php

    require_once ( __DIR__ . "/../config.php" );

    class IntercityService implements IntercityServiceMethods {

        private $url;

        private function curl(String $method, Array $body): String {

            $curl = curl_init();

            $settings = array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Basic *****"),
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_SSL_VERIFYPEER => FALSE
            );

            if ($method === "GET") {

                $settings[CURLOPT_CUSTOMREQUEST] = "GET";

            }else if ($method === "POST") {

                $settings[CURLOPT_CUSTOMREQUEST] = "POST";
            }

            if(count($body) > 0){
                
                $settings[CURLOPT_POSTFIELDS] = json_encode($body);

            }

            curl_setopt_array($curl, $settings);
            $response = curl_exec($curl);
            
            if($response === FALSE){
                return curl_error($curl);
            }

            curl_close($curl);

            return $response;

        }

        public function pipe(String $url) {
            $this->url = SERVICE_URL . "$url/";
            return $this;
        }

        public function get(): ARRAY {

            $response = $this->curl("GET", array());
            
            if($response !== "" && strpos($response, "[{") !== FALSE){

                $content = "[{" . explode("[{", $response, 2)[1];
                return json_decode($content, TRUE);

            }

            return array();

        }

        public function post(Array $data): ARRAY {

            $response = $this->curl("POST", $data);
            
            if(strpos($response, "[{") !== FALSE){

                $content = "[{" . explode("[{", $response, 2)[1];
                return json_decode($content, TRUE);

            }else if(strpos($response, "{") !== FALSE){

                $content = "{" . explode("{", $response, 2)[1];
                return json_decode($content, TRUE);

            }

            return array();

        }

    }

    class FilterService implements FilterServiceMethods {

        private $url;

        private function curl(String $method, Array $data): String {

            $curl = curl_init();

            $settings = array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"),
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_SSL_VERIFYPEER => FALSE
            );

            if ($method === "GET") {

                $settings[CURLOPT_CUSTOMREQUEST] = "GET";

            }else if ($method === "POST") {

                $settings[CURLOPT_CUSTOMREQUEST] = "POST";
            }

            if(count($data) > 0){
                
                $settings[CURLOPT_POSTFIELDS] = json_encode($data);

            }

            curl_setopt_array($curl, $settings);
            $response = curl_exec($curl);
            
            if($response === FALSE){
                return curl_error($curl);
            }

            curl_close($curl);

            return $response;

        }

        public function setAPIURL(): OBJECT {
            $this->url = "https://*****/api.php";
            return $this;
        }

        public function get(Array $data): ARRAY {

            $response = $this->curl("GET", $data);

            if($response !== "" && strpos($response, "[{") !== FALSE){

                try {

                    $content = substr("[{" . explode("[{", $response, 2)[1], 0, -1);
                    return json_decode($content, TRUE);

                } catch (TypeError $e) {

                    $content = "[{" . explode("[{", $response, 2)[1];
                    return json_decode($content, TRUE);

                }

            }

            return array();

        }

    }

?>