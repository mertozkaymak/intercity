<?php

    require_once ( __DIR__ . "/../config.php" );

    class Ideasoft implements IdeasoftMethods {

        public $total_pages = 0;
        private $url, $access_token, $refresh_token, $site_url;

        private function curl(String $method, Array $body): String {

            $curl = curl_init();

            $settings = array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_SSL_VERIFYPEER => FALSE
            );

            if(isset($this->access_token)){

                $settings[CURLOPT_HEADER] = TRUE;
                $settings[CURLOPT_HTTPHEADER] = array(
                    "Authorization: Bearer " . $this->access_token,
                    "Content-Type: application/json",
                );

            }else{

                $settings[CURLOPT_HTTPHEADER] = array("Content-Type: application/json");

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

        private function curlBuffer(): ARRAY {

            $response = $this->curl("GET", array());

            if($response !== "" && strpos($response, "[{") !== FALSE){
                    
                $header = explode("[{", $response, 2)[0];
                $content = "[{" . explode("[{", $response, 2)[1];

                if(strpos($header, "504") !== FALSE){
                    return 504;
                }

                if(strpos($header, "429") !== FALSE){
                    return 429;
                }
                    
                if($this->total_pages === 0){
                    $total_pages = intval(trim(explode(":", explode("\n", $header)[8])[1]));
                    if($total_pages > 0){
                        $this->total_pages = ceil($total_pages / 100);
                    }
                }

                return json_decode($content, TRUE);

            }

            return array();

        }

        public function checkAcc(): OBJECT {

            $this->access_token = NULL;

            $check_token = Database::connect()->table("ideasoft")->selectWith(
                array($this->site_url),
                "WHERE site_url = ?", "access_token"
            );

            if(isset($check_token[0]->access_token)){
                $this->access_token = $check_token[0]->access_token;
            }

            return $this;
            
        }

        public function checkRef(): OBJECT {

            $this->refresh_token = NULL;

            $check_token = Database::connect()->table("ideasoft")->selectWith(
                array($this->site_url),
                "WHERE site_url = ?", "refresh_token"
            );

            if(isset($check_token[0]->refresh_token)){
                $this->refresh_token = $check_token[0]->refresh_token;
            }

            return $this;
            
        }

        public function request(String $url): OBJECT {
            $this->url = $this->site_url . $url;
            return $this;
        }

        public function setSiteURL(String $url): OBJECT {
            $this->site_url = $url;
            return $this;
        }

        public function connect(Array $auth): STRING {
            
            $response = $this->curl("POST", $auth);

            if($response !== ""){

                $response = json_decode($response, TRUE);

                $check_token = Database::connect()->table("ideasoft")->selectWith(
                    array($this->site_url), "WHERE access_token IS NULL AND refresh_token IS NULL AND site_url = ?", "*"
                );

                if(count($check_token) > 0 && is_null($check_token[0]->access_token) && is_null($check_token[0]->refresh_token)){

                    $rowCount = Database::connect()->table("ideasoft")->update(
                        array(
                            "access_token" => $response["access_token"],
                            "refresh_token" => $response["refresh_token"],
                            "last_access_date" => date("Y-m-d H:i:s"),
                            "last_refresh_date" => date("Y-m-d H:i:s"),
                            "0" => $this->site_url
                        ),
                        "WHERE site_url = ?"
                    );

                    if(!$rowCount){ return "An unexpected error occurred in the database."; }
                    return "The connection has been successfully created.";

                }
                
                return "The connection has already been established.";

            }

            return "An error occurred while sending the request.";

        }

        public function reConnect(Array $auth): STRING {

            if($this->refresh_token === NULL){
                return "The connection is a broken.";
            }

            $auth["refresh_token"] = $this->refresh_token;
            $response = $this->curl("POST", $auth);
            
            if($response !== ""){

                $response = json_decode($response, TRUE);
                
                $rowCount = Database::connect()->table("ideasoft")->update(
                    array(
                        "access_token" => $response["access_token"],
                        "refresh_token" => $response["refresh_token"],
                        "last_access_date" => date("Y-m-d H:i:s"),
                        "last_refresh_date" => date("Y-m-d H:i:s"),
                        "0" => $this->site_url
                    ),
                    "WHERE site_url = ?"
                );

                if(!$rowCount){ return "An unexpected error occurred in the database."; }
                return "The connection has been successfully created.";

            }

            return "An error occurred while sending the request.";
            
        }

        public function get(String $target_api, String $filter_api): ARRAY {

            $result = array();

            $stop = 0;
            $page = 1;

            $this->checkAcc();

            if($this->access_token === NULL){
                return array();
            }

            if($filter_api === ""){

                $filter_api = "limit=100&sort=-id";

            }else{

                $filter_api = $filter_api . "&limit=100&sort=-id";

            }

            while ($stop === 0) {

                $response = $this->request('/admin-api' . $target_api . '?' . $filter_api . "&page=" . $page)->curlBuffer();

                if(count($response) < 1){

                    sleep(5);

                }else if($response === 504){
                    
                    sleep(1);

                }else if($response === 429){

                    sleep(5);

                }else{

                    foreach ($response as $res) {
                        array_push($result, $res);
                    }

                    $page++;
                    sleep(1);

                }

                if($this->total_pages < $page){
                    $this->total_pages = 0;
                    $stop = 1;
                }

            }

            return $result;

        }

        public function getBy(String $target_api, String $filter_api): ARRAY {

            $this->request('/admin-api' . $target_api . $filter_api);
            
            $response = $this->curl("GET", array());
            $body = substr($response, strpos($response, "gzip") + 8, 2);

            if($body !== "[]" && $body !== "[{"){
                    
                $header = explode("{", $response, 2)[0];
                $content = "[{" . explode("{", $response, 2)[1] . "]";

                if(strpos($header, "504") !== FALSE){
                    sleep(5);
                    return 504;
                }

                if(strpos($header, "429") !== FALSE){
                    sleep(5);
                    return 429;
                }

                return json_decode($content, TRUE);

            }
            else if($body !== "[]"){
                    
                $header = explode("{", $response, 2)[0];
                $content = "[{" . explode("[{", $response, 2)[1];
                    
                if(strpos($header, "504") !== FALSE){
                    sleep(5);
                    return 504;
                }

                if(strpos($header, "429") !== FALSE){
                    sleep(5);
                    return 429;
                }

                return json_decode($content, TRUE);

            }

            return array();
            
        }
        
        public function post(String $target_api, Array $data): ARRAY {

            $this->request('/admin-api' . $target_api);
            
            $response = $this->curl("POST", $data);

            if($response !== "" && strpos($response, "{") !== FALSE){
                    
                $header = explode("{", $response, 2)[0];
                $content = "{" . explode("{", $response, 2)[1];

                if(strpos($header, "504") !== FALSE){
                    sleep(5);
                    return 504;
                }

                if(strpos($header, "429") !== FALSE){
                    sleep(5);
                    return 429;
                }

                return json_decode($content, TRUE);

            }

            return array();
            
        }

        public function delete(String $target_api, String $id): ARRAY {

            $this->request("/admin-api$target_api/$id");
            
            $response = $this->curl("DELETE", array());
            $body = substr($response, strpos($response, "gzip") + 8, 2);

            if(strpos($response, "204") !== FALSE){
                return array("status" => 1);
            }

            if($body !== "[]" && $body !== "[{"){
                $header = explode("{", $response, 2)[0];
                $content = "[{" . explode("{", $response, 2)[1] . "]";
                return json_decode($content, TRUE);
            }
            else if($body !== "[]"){
                $header = explode("{", $response, 2)[0];
                $content = "[{" . explode("[{", $response, 2)[1];
                return json_decode($content, TRUE);
            }

            return array();
            
        }

        public function put(String $target_api, String $target_id, Array $data): ARRAY {

            $this->request('/admin-api' . $target_api . '/' . $target_id);
            
            $response = $this->curl("PUT", $data);

            if($response !== "" && strpos($response, "{") !== FALSE){
                    
                $header = explode("{", $response, 2)[0];
                $content = "{" . explode("{", $response, 2)[1];

                if(strpos($header, "504") !== FALSE){
                    sleep(5);
                    return 504;
                }

                if(strpos($header, "429") !== FALSE){
                    sleep(5);
                    return 429;
                }

                return json_decode($content, TRUE);

            }

            return array();
            
        }

        public function oStatTR(String $status, Bool $upper): STRING {

            $search = array("ç","i","ı","ğ","ö","ş","ü");
            $replace = array("Ç","İ","I","Ğ","Ö","Ş","Ü");

            switch ($status) {
                case 'deleted':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Silindi")) : "Silindi";
                case 'waiting_for_approval':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Onay bekliyor")) : "Onay bekliyor";
                case 'approved':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Onaylandı")) : "Onaylandı";
                case 'fulfilled':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Kargoya verildi")) : "Kargoya verildi";
                case 'cancelled':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "İptal edildi")) : "İptal edildi";
                case 'delivered':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Teslim edildi")) : "Teslim edildi";
                case 'on_accumulation':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Tedarik sürecinde")) : "Tedarik sürecinde";
                case 'waiting_for_payment':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Ödeme bekleniyor")) : "Ödeme bekleniyor";
                case 'being_prepared':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Hazırlanıyor")) : "Hazırlanıyor";
                case 'refunded':
                    return ($upper) ? strtoupper(str_replace($search, $replace, "İade edildi")) : "İade edildi";
                default:
                    return ($upper) ? strtoupper(str_replace($search, $replace, "Durum Bulunamadı")) : "Durum Bulunamadı";
            }

        }

    }

?>