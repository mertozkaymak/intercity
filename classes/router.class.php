<?php

    require_once ( __DIR__ . "/../config.php" );
    
    class Router {

        private $ideasoft, $intercity_service, $store;

        public function __construct() {
            $this->ideasoft = new Ideasoft;
            $this->store = Database::connect()->table("ideasoft")->selectAll("WHERE access_token IS NOT NULL", "*")[0];
            $this->intercity_service = new IntercityService;
        }

        # SERVICE

            # LOCATIONS
            public function LOCATIONS() {
                return $this->intercity_service->pipe("/get-locations")->get();
            }
            
            # STATUS
            public function APPOINTMENT_STATUS($arr) {
                return $this->intercity_service->pipe("/check-appointment-month-list")->post($arr);
            }
            
            # INSERT
            public function INSERT_APPOINTMENT($arr) {
                return $this->intercity_service->pipe("/insert-appointment")->post($arr);
            }


        
        # IDEASOFT

            # GET
            public function IDEASOFT_GET($arr) {
                $filter = isset($arr["filter"]) ? $arr["filter"] : "";
                return $this->ideasoft->setSiteURL($this->store->site_url)->checkAcc()->get($arr["uri"], $filter);
            }
            
            # GET BY
            public function IDEASOFT_GETBY($arr) {
                $filter = isset($arr["filter"]) ? $arr["filter"] : "";
                return $this->ideasoft->setSiteURL($this->store->site_url)->checkAcc()->getBy($arr["uri"], $filter);
            }

            # POST
            public function IDEASOFT_POST($arr) {
                return $this->ideasoft->setSiteURL($this->store->site_url)->checkAcc()->post($arr["uri"], $arr["data"]);
            }

            # DELETE
            public function IDEASOFT_DELETE($arr) {
                return $this->ideasoft->setSiteURL($this->store->site_url)->checkAcc()->delete($arr["uri"], $arr["product_id"]);
            }

        # DATABASE

            # GET ALL AS OBJECT
            public function DB_GETALL($table, $sql, $cols) {
                return Database::connect()->table($table)->selectAll($sql, $cols);
            }
            
            # GET WITH AS OBJECT
            public function DB_GETWITH($table, $arr, $sql, $cols) {
                return Database::connect()->table($table)->selectWith($arr, $sql, $cols);
            }

            # GET WITH AS INDEX ARRAY
            public function DB_GETWITH2($table, $arr, $sql, $cols) {
                return Database::connect()->table($table)->PDOStatement(PDO::FETCH_NUM)->selectWith($arr, $sql, $cols);
            }

            # INSERT
            public function DB_INSERT($table, $arr) {
                return Database::connect()->table($table)->insert($arr);
            }

            # UPDATE
            public function DB_UPDATE($table, $arr, $sql) {
                return Database::connect()->table($table)->update($arr, $sql);
            }

        # CUSTOM
        
    }

?>