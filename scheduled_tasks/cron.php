<?php

    # HEADERS
    date_default_timezone_set('Europe/Istanbul');

    # CLASSES
    require_once(__DIR__ . "/../config.php");
    $router = new Router();

    $products = $router->IDEASOFT_GET(array("uri" => "/products"));

    foreach ($products as $product) {
        
        $data = array(
            "name" => $product["name"],
            "brand" => explode(" ", explode("Periyodik Bakım Paketi", $product["name"])[0], 2)[0],
            "model" => explode(" ", explode("Periyodik Bakım Paketi", $product["name"])[0], 2)[1]
        );

        $db_row = $router->DB_GETWITH("products", array($product["id"]), "WHERE ideasoft_id = ?", "*");

        if(count($db_row) > 0){

            $data["0"] = $product["id"];
            $router->DB_UPDATE("products", $data, "WHERE ideasoft_id = ?");

        }
        else{

            $data["ideasoft_id"] = $product["id"];
            $router->DB_INSERT("products", $data);

        }

    }

?>