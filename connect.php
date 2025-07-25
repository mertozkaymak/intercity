<?php

    date_default_timezone_set('Europe/Istanbul');
    require_once(__DIR__ . "/config.php");

    if(isset($_GET["code"])){

        $in_proggess_store = Database::connect()->table("ideasoft")->selectWith(array($_GET["domain"]), "WHERE site_url = ?", "*");

        if(count($in_proggess_store) > 0){

            $auth = array(
                "grant_type"    => "authorization_code",
                "client_id"     => $in_proggess_store[0]->client_id,
                "client_secret" => $in_proggess_store[0]->client_secret,
                "code"          => $_GET["code"],
                "redirect_uri"  => $in_proggess_store[0]->redirect_url
            );

            $ideasoft = new Ideasoft;
            $connection_status = $ideasoft->setSiteURL($_GET["domain"])->request("/oauth/v2/token")->connect($auth);
            $next_store =  Database::connect()->table("ideasoft")->selectAll("WHERE access_token IS NULL AND refresh_token IS NULL", "*");

            echo $_GET["domain"] . " | " . $connection_status . "<br>";

            if(count($next_store) > 0){

                $script = '<script>
                    setTimeout(function(){
                        window.location.href = "' . $next_store[0]->site_url . '/panel/auth?client_id=' . $next_store[0]->client_id . '&response_type=code&state=*****&redirect_uri=' . $next_store[0]->redirect_url . '";
                    }, 5000);
                </script>';
        
                echo $script;
        
            }
            else{

                echo "Tokens in all stores have been renewed.";

            }

        }

    }
    else{

        $idea_stores =  Database::connect()->table("ideasoft")->selectAll("WHERE access_token IS NULL AND refresh_token IS NULL", "*");

        if ( count( $idea_stores ) > 0 ) {

            $script = '<script>
                window.location.href = "' . $idea_stores[0]->site_url . '/panel/auth?client_id=' . $idea_stores[0]->client_id . '&response_type=code&state=*****&redirect_uri=' . $idea_stores[0]->redirect_url . '";
            </script>';

            echo $script;
            
        }
        else{

            echo "Not found new ideasoft store for connect.";

        }

    }

?>
