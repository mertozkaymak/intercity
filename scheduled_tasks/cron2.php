<?php

    # HEADERS
    date_default_timezone_set('Europe/Istanbul');

    # CLASSES
    require_once(__DIR__ . "/../config.php");
    $router = new Router();

    $last_order_id = $router->DB_GETALL("last_order_id", "WHERE id = 1", "ideasoft_id")[0]->ideasoft_id;
    $orders = $router->IDEASOFT_GET(array("uri" => "/orders", "filter" => "sinceId=$last_order_id&limit=100"));

    if($orders === FALSE){
        exit;
    }

    foreach ($orders as $order) {

        $last_order_id = $order["id"];
        $db_row = $router->DB_GETWITH("appointments", array($order["id"]), "WHERE order_id = ?", "*");

        if(count($db_row) > 0){
            continue;
        }

        foreach ($order["orderItems"] as $order_item) {

            $customization = FALSE;
            foreach ($order_item["orderItemCustomizations"] as $customizations) {
                if($customizations["productCustomizationFieldName"] == "Randevu"){
                    $customization = $order_item["orderItemCustomizations"][0]["productCustomizationFieldValue"];
                }
            }

            if($customization === FALSE){
                continue;
            }

            $customization = explode(" / ", $customization);
            $appointment_data = array();
            for ($index = 0; $index < count($customization); $index++) {
                $key = trim(explode(":", $customization[$index], 2)[0]);
                $value = trim(explode(":", $customization[$index], 2)[1]);
                $appointment_data[$key] = $value;
            }

            $data = array(
                "location_id"       => $appointment_data["Lokasyon"],
                "is_vale"           => "H",
                "surucu_adi"        => $order["customerFirstname"],
                "surucu_soyadi"     => $order["customerSurname"],
                "surucu_email"      => $order["customerEmail"],
                "surucu_telefon"    => str_replace(array("+90", "(", ")", " "), "", $order["customerPhone"]),
                "km"                => array_pop(explode(" ", $order_item["productName"])),
                "ruhsat_sahibi"     => $order["customerFirstname"] . " " . $order["customerSurname"],
                "model_yili"        => $appointment_data["Model Yılı"],
                "marka_no"          => explode("-", explode("_", $order_item["productSku"])[0])[0],
                "model_no"          => explode("-", explode("_", $order_item["productSku"])[0])[1],
                "randevu_tarihi"    => date("d.m.Y", strtotime($appointment_data["Gün"])) . " " . $appointment_data["Saat"],
                "plaka"             => $appointment_data["Plaka"],
            );

            $result = $router->INSERT_APPOINTMENT($data);

            # Array
            # (
            #     [result] => 2
            #     [errbuff] => araç bakım paketi, periyodik bakım km bulunurken hata: ORA-01403: no data found
            #     [insert_id] => 
            # )

            $data2 = array(
                "location_id"   =>  $data["location_id"],
                "vale"          =>  $data["is_vale"],
                "date"          =>  $data["randevu_tarihi"],
                "firstname"     =>  $data["surucu_adi"],
                "lastname"      =>  $data["surucu_soyadi"],
                "email"         =>  $data["surucu_email"],
                "phone"         =>  $data["surucu_telefon"],
                "km"            =>  $data["km"],
                "license_owner" =>  $data["ruhsat_sahibi"],
                "plate"         =>  $data["plaka"],
                "model_year"    =>  $data["model_yili"],
                "model_no"      =>  $data["model_no"],
                "brand_no"      =>  $data["marka_no"]
            );

            if(is_null($result["insert_id"])) {

                if(count($db_row) > 0){

                    $data2["error_log"] = $result["errbuff"];
                    $data2["0"] = $order["id"];

                    $router->DB_UPDATE("appointments", $data2, "WHERE order_id = ?");

                }
                else{

                    $data2["error_log"] = $result["errbuff"];
                    $data2["order_id"] = $order["id"];

                    $router->DB_INSERT("appointments", $data2);

                }

            }
            else{

                if(count($db_row) < 1){

                    $data2["insert_id"] = $result["insert_id"];
                    $data2["order_id"] = $order["id"];

                    $router->DB_INSERT("appointments", $data2);

                }

            }

        }

    }

?>