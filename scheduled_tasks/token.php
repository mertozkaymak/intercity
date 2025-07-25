<?php

    date_default_timezone_set('Europe/Istanbul');
    require_once(__DIR__ . "/../config.php");

    $idea_stores = Database::connect()->table("ideasoft")->selectAll("", "site_url, client_id, client_secret");

    if(count($idea_stores) > 0){
        
        $ideasoft = new Ideasoft;

        foreach ($idea_stores as $idea_store) {

            $auth = array(
                "grant_type"    => "refresh_token",
                "client_id"     => $idea_store->client_id,
                "client_secret" => $idea_store->client_secret
            );
            
            $reconnect_status = $ideasoft->setSiteURL($idea_store->site_url)->checkRef()->request("/oauth/v2/token")->reConnect($auth);
            echo $idea_store->site_url . " | " . $reconnect_status . "<br>";

        }

    }

?>
