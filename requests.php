<?php

    # HEADERS
    header("Access-Control-Allow-Origin: *");
    date_default_timezone_set('Europe/Istanbul');

    # CLASSES
    require_once(__DIR__ . "/config.php");
    $router = new Router();

    if(isset($_POST["action"])){

        $action = $_POST["action"];

        if($action === "0") {

            $provinces = $router->DB_GETALL("provinces", "WHERE 1 ORDER BY name ASC", "*");
            
            $postdata = http_build_query($provinces);
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );
            $context  = stream_context_create($opts);

            echo file_get_contents('*****/assets/view/appointment.php', false, $context);

        }
        else if($action === "1") {

            $sublocations = $router->DB_GETWITH("sublocations", array($_POST["province_id"]), "WHERE province_id = ? ORDER BY name ASC", "*");

            for ($index = 0; $index < count($sublocations); $index++) {
                $sublocations[$index] = '<option value="' . $sublocations[$index]->id . '" data-province-id="' . $sublocations[$index]->province_id . '">' . $sublocations[$index]->name . '</option>';
            }

            echo '<option>İlçe Seçiniz</option>' . implode("", $sublocations);

        }
        else if($action === "2") {

            $services = $router->DB_GETWITH("services", array($_POST["province_id"], $_POST["sublocation_id"]), "WHERE province_id = ? and town_id = ? ORDER BY name ASC", "*");

            foreach ($services as $service) {

                # STATIC CONFIGS
                $status = FALSE;
                $date_limit = strtotime(date("Y-m-d H:s:i") . ' +14 days');

                $available_dates = $router->APPOINTMENT_STATUS(array(
                    "month"         => intval(date("m")),
                    "year"          => date("Y"),
                    "location_id"   => $service->sid
                ));

                $available_dates2 = $router->APPOINTMENT_STATUS(array(
                    "month"         => intval(date("m", strtotime(date("Y-m-d") . ' +1 month'))),
                    "year"          => date("Y"),
                    "location_id"   => $service->sid
                ));

                $service->avaiable_dates = array();
                $available_dates = array_merge($available_dates, $available_dates2);

                foreach ($available_dates as $available_date) {

                    if(strtotime($available_date["date"]) > $date_limit){
                        continue;
                    }

                    foreach ($available_date["hours"] as $appointmentStatusHour) {

                        if($appointmentStatusHour["status"]){

                            $status = TRUE;
                            array_push($service->avaiable_dates, $appointmentStatusHour["saat"]);

                        }

                    }

                }

                if($status){

                    $postdata = http_build_query($service);
                    $opts = array('http' =>
                        array(
                            'method'  => 'POST',
                            'header'  => 'Content-Type: application/x-www-form-urlencoded',
                            'content' => $postdata
                        )
                    );
                    $context  = stream_context_create($opts);
        
                    echo file_get_contents('*****/assets/view/service_card.php', false, $context);

                }

            }

        }
        else if($action === "3") {

            $appointment_status = json_decode(urldecode($_POST["appointment_status"]));

            for ($index = 0; $index < count($appointment_status); $index++) {

                $appointment_status[$index] = array(
                    "date" => explode("T", $appointment_status[$index])[0],
                    "hour" => substr(explode("+", explode("T", $appointment_status[$index])[1])[0], 0, -3)
                );

            }

            echo json_encode($appointment_status);

        }
        else if($action === "4") {

            $provinces = $router->DB_GETALL("provinces", "ORDER BY name ASC", "*");
            $services = $router->DB_GETALL("services", "ORDER BY name ASC", "*");

            $postdata = http_build_query(array("provinces" => $provinces, "services" => $services));
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );
            $context  = stream_context_create($opts);

            echo file_get_contents('*****/assets/view/locations.php', false, $context);

        }
        else if($action === "5") {

            $sublocations = $router->DB_GETWITH("sublocations", array($_POST["province_id"]), "WHERE province_id = ? ORDER BY name ASC", "*");

            for ($index = 0; $index < count($sublocations); $index++) {
                $sublocations[$index] = '<option value="' . $sublocations[$index]->id . '" data-province-id="' . $sublocations[$index]->province_id . '">' . $sublocations[$index]->name . '</option>';
            }

            echo '<option>İlçe Seçiniz</option>' . implode("", $sublocations);

        }
        else if($action === "6") {

            if(!isset($_POST["province_id"]) || !isset($_POST["sublocation_id"])){

                $services = $router->DB_GETALL("services", "ORDER BY name ASC", "*");

                foreach ($services as $service) {

                    $postdata = http_build_query($service);
                    $opts = array('http' =>
                        array(
                            'method'  => 'POST',
                            'header'  => 'Content-Type: application/x-www-form-urlencoded',
                            'content' => $postdata
                        )
                    );
                    $context  = stream_context_create($opts);
        
                    echo file_get_contents('*****/assets/view/service_card_2.php', false, $context);
    
                }

                exit;

            }

            $services = $router->DB_GETWITH("services", array($_POST["province_id"], $_POST["sublocation_id"]), "WHERE province_id = ? and town_id = ? ORDER BY name ASC", "*");

            foreach ($services as $service) {

                # STATIC CONFIGS
                $status = FALSE;
                $date_limit = strtotime(date("Y-m-d H:s:i") . ' +14 days');

                $available_dates = $router->APPOINTMENT_STATUS(array(
                    "month"         => intval(date("m")),
                    "year"          => date("Y"),
                    "location_id"   => $service->sid
                ));

                $available_dates2 = $router->APPOINTMENT_STATUS(array(
                    "month"         => intval(date("m", strtotime(date("Y-m-d") . ' +1 month'))),
                    "year"          => date("Y"),
                    "location_id"   => $service->sid
                ));

                $service->avaiable_dates = array();
                $available_dates = array_merge($available_dates, $available_dates2);

                foreach ($available_dates as $available_date) {

                    if(strtotime($available_date["date"]) > $date_limit){
                        continue;
                    }

                    foreach ($available_date["hours"] as $appointmentStatusHour) {

                        if($appointmentStatusHour["status"]){

                            $status = TRUE;
                            array_push($service->avaiable_dates, $appointmentStatusHour["saat"]);

                        }

                    }

                }

                if($status){

                    $postdata = http_build_query($service);
                    $opts = array('http' =>
                        array(
                            'method'  => 'POST',
                            'header'  => 'Content-Type: application/x-www-form-urlencoded',
                            'content' => $postdata
                        )
                    );
                    $context  = stream_context_create($opts);
        
                    echo file_get_contents('*****/assets/view/service_card_2.php', false, $context);

                }

            }

        }
        else if($action === "7") {

            $brands = $router->DB_GETALL("products", "WHERE 1 ORDER BY name ASC", "*");

            $temp = array();
            $temp2 = array();

            for ($index = 0; $index < count($brands); $index++) {
                if (!in_array($brands[$index]->brand, $temp)) {
                    array_push($temp2, '<option value="' . $brands[$index]->brand . '">' . $brands[$index]->brand . '</option>');
                    array_push($temp, $brands[$index]->brand);
                }
            }

            echo '<option>Marka</option>' . implode("", $temp2);

        }
        else if($action === "8") {

            $models = $router->DB_GETWITH("products", array($_POST["brand"]), "WHERE brand = ? ORDER BY name ASC", "*");

            $temp = array();
            $temp2 = array();

            for ($index = 0; $index < count($models); $index++) {
                if (!in_array($models[$index]->model, $temp)) {
                    array_push($temp2, '<option value="' . $models[$index]->model . '">' . $models[$index]->model . '</option>');
                    array_push($temp, $models[$index]->model);
                }
            }

            echo '<option>Model</option>' . implode("", $temp2);

        }

    }
    
?>