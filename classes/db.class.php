<?php

    require_once ( __DIR__ . "/../config.php" );

    class Database implements DbMethods {
        
        private static $dbh;
        private $table, $statement = PDO::FETCH_OBJ;

        public static function connect(): OBJECT {
            
            try {
                
                self::$dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
                self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return new static;

            } catch (PDOException $e) {

                echo "Connection failed: " . $e->getMessage();
                exit();
                
            }

        }

        public static function disconnect(): VOID {

            self::$dbh = NULL;

        }

        public function table($table): Object {
            $this->table = $table;
            return $this;
        }

        public function PDOStatement($statement): Object {
            $this->statement = $statement;
            return $this;
        }
        
        public function selectAll(String $query, String $fields): ARRAY {
            
            try {

                $stmt = self::$dbh->prepare("SELECT " . $fields . " FROM `" . $this->table . "`" . " $query");
                $stmt->execute();
                $result = $stmt->fetchAll($this->statement);

                if($stmt->rowCount() < 1){
                    return array();
                }

                return $result;

            } catch (PDOException $e) {
                
                return array();

            }

        }
        
        public function selectWith(Array $params, String $query, String $fields): ARRAY {

            if($query === "" || count($params) < 1){
                return $this->selectAll($query, $fields);
            }
            
            try {

                $stmt = self::$dbh->prepare("SELECT " . $fields . " FROM `" . $this->table . "` $query");
                $stmt->execute($params);
                $result = $stmt->fetchAll($this->statement);

                if($stmt->rowCount() < 1){
                    return array();
                }

                return $result;

            } catch (PDOException $e) {
                
                return array();

            }
            
        }
        
        public function insert(Array $params): INT {

            if(count($params) < 1){
                return FALSE;
            }

            $sql = "INSERT INTO `" . $this->table . "`";
            $counter = 0; $fields = ""; $questOps = ""; $execute = array();

            foreach ($params as $key => $value) {

                $fields .= ($counter === 0) ? $key : ", $key";
                $questOps .= ($counter === 0) ? "?" : ", ?";
                
                array_push($execute, $value);
                
                $counter = 1;

            }

            $sql .= "(" . $fields . ") VALUES (" . $questOps . ")";

            try {

                $stmt = self::$dbh->prepare($sql);
                $stmt->execute($execute);

                if($stmt->rowCount() < 1){
                    return 0;
                }

                return $stmt->rowCount();

            } catch (PDOException $e) {
                
                return 0;

            }

        }

        public function update(Array $params, String $query): INT {

            if(count($params) < 1){
                return FALSE;
            }

            $sql = "UPDATE `" . $this->table . "` SET ";
            $counter = 0; $execute = array();

            foreach ($params as $key => $value) {

                if(!is_numeric($key)){
                    $sql .= ($counter === 0) ? "$key = ?" : ", $key = ?";
                }

                array_push($execute, $value);
                
                $counter = 1;

            }

            $sql .= " $query";

            try {

                $stmt = self::$dbh->prepare($sql);
                $stmt->execute($execute);

                if($stmt->rowCount() < 1){
                    return 0;
                }

                return $stmt->rowCount();

            } catch (PDOException $e) {
                
                return 0;

            }

        }

        public function deleteWith(Array $params, String $query): ARRAY {

            if($query === "" || count($params) < 1){
                return array();
            }
            
            try {

                $stmt = self::$dbh->prepare("DELETE FROM `" . $this->table . "` $query");
                $stmt->execute($params);
                $result = $stmt->fetchAll($this->statement);

                if($stmt->rowCount() < 1){
                    return array();
                }

                return $result;

            } catch (PDOException $e) {
                
                return array();

            }
            
        }

        public function truncate(): ARRAY {
            
            try {

                $stmt = self::$dbh->prepare("TRUNCATE TABLE `" . $this->table . "`");
                $stmt->execute();
                $result = $stmt->fetchAll($this->statement);

                if($stmt->rowCount() < 1){
                    return array();
                }

                return $result;

            } catch (PDOException $e) {
                
                return array();

            }
            
        }

        public function createTable(Array $tables): INT {

            if(count($tables) < 1){
                return 0;
            }

            $check_table = self::connect()->table($this->table)->selectAll("LIMIT 1", "1");
            
            if(isset($check_table[0])){
                return 0;
            }

            $q = "";
            $counter = 0;

            foreach ($tables as $table) {

                $t = "";
                
                if(isset($table["name"]) && $table["name"] !== ""){
                    $t .= $table["name"];
                    if(isset($table["type"]) && $table["type"] !== ""){
                        $t .= " " . $table["type"];
                        if(isset($table["length"]) && $table["length"] > 0){
                            $t .= "(" . $table["length"] . ")";
                        }
                        if(isset($table["options"]) && $table["options"] !== ""){
                            $t .= " " . $table["options"];
                        }
                    }else{
                        continue;
                    }
                }else{
                    continue;
                }

                if($counter === 0){
                    $q .= $t;
                }else{
                    $q .= ", " . $t;
                }

                $counter = 1;
            }

            
            try {

                $stmt = self::$dbh->prepare("CREATE TABLE `" . $this->table . "`(" . $q . ")");
                $stmt->execute();
                
                if($stmt){
                    return 1;
                }else{
                    return 0;
                }

            } catch (PDOException $e) {
                
                return 2;

            }
            
        }

        public function clearAllDuplicateRows(Array $comparisonVariables): INT {

            if(count($comparisonVariables) < 1){
                return 0;
            }

            $sql = "DELETE t1 FROM `" . $this->table . "` t1 INNER JOIN `" . $this->table . "` t2 WHERE t1.id < t2.id";
            foreach ($comparisonVariables as $key => $variables) {
                if($key === "comparison1"){
                    foreach ($variables as $variable) {
                        $sql .= " AND t1." . $variable . " = t2." . $variable;
                    }
                }else if($key === "comparison2"){
                    foreach ($variables as $key2 => $variable) {
                        $sql .= " AND t1." . $key2 . " = '" . $variable . "'";
                    }
                }
            }

            try {

                $stmt = self::$dbh->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll($this->statement);

                if($stmt->rowCount() < 1){
                    return 0;
                }

                return 1;

            } catch (PDOException $e) {
                
                echo $e->getMessage();
                return 2;

            }

        }

        public function customQuery(Array $params, String $query, String $condition, Bool $result): ARRAY {

            if(strpos(strtoupper($query), "TABLE") !== FALSE){
                $check_table = self::connect()->table($this->table)->selectAll("LIMIT 1", "1");
                if(isset($check_table[0])){
                    return array();
                }
            }

            try {

                $stmt = self::$dbh->prepare("$query `" . $this->table . "` $condition");

                if(count($params) > 0){
                    $stmt->execute($params);
                }
                else{
                    $stmt->execute();
                }
                
                if($result === TRUE){
                    $result = $stmt->fetchAll($this->statement);
                    if($stmt->rowCount() < 1){
                        return array();
                    }
                    return $result;
                }

                return array();

            } catch (PDOException $e) {
                
                echo $e->getMessage();
                return array();

            }

        }

    }

?>