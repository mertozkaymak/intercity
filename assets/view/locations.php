<?php
    header("Access-Control-Allow-Origin: *");
    require_once(__DIR__ . "/../../config.php");
?>
<div class="container p-0">
    <div class="content-body">
        <div class="form-group">
            <select class="form-control" data-selector="appointment-province">
                <option>İl Seçiniz</option>
                <?php
                    for ($index = 0; $index < count($_POST["provinces"]); $index++) {
                        echo '<option value="' . $_POST["provinces"][$index]["id"] . '" >' . $_POST["provinces"][$index]["name"] . '</option>';
                    }
                ?>
            </select>
        </div>
        <div class="form-group">
            <select class="form-control disabled" data-selector="appointment-sublocation" disabled>
                <option>İlçe Seçiniz</option>
            </select>
        </div>
    </div>
    <h3 class="my-5 text-center border-bottom pb-5">SERVİSLERİMİZ</h3>
    <div class="container content-footer p-0">
        <?php
            for ($index = 0; $index < count($_POST["services"]); $index++) {
                $googlemap = (isset($_POST["services"][$index]["latitude"]) && $_POST["services"][$index]["latitude"] !== "0" && $_POST["services"][$index]["longitude"] !== "0") ? "https://www.google.com/maps/search/?api=1&query=" . $_POST["services"][$index]["latitude"] . "," . $_POST["services"][$index]["longitude"] : FALSE;
                echo '<div class="row align-items-center border m-0 mb-3 p-3" style="background: rgba(0, 0, 0, 0.1);border-radius: 7px;">
                    <div class="col-lg-3 pl-0">
                        <div style="border-radius: 7px;width: 100%;height:200px;background: url(';
                        if(isset($_POST["services"][$index]["images"])){
                            if((strpos($_POST["services"][$index]["images"], ",") === FALSE)){
                                echo $_POST["services"][$index]["images"];
                            }else{
                                echo explode(",", $_POST["services"][$index]["images"])[0];
                            }
                        }else{
                            echo DEFAULT_IMAGE;
                        } 
                        echo ');background-repeat: no-repeat;background-size: cover;background-position: center;"></div>
                    </div>
                    <div class="col-md-6 col-lg-5 justify-content-center mt-3 mt-lg-0" style="display: grid;font-size: 2rem;">
                        <div class="row"><h5>' . $_POST["services"][$index]["name"] . '</h5></div>
                        <div class="row">' . $_POST["services"][$index]["address"] . '</div>
                    </div>
                    <div class="col-md-6 col-lg-4 d-flex justify-content-center p-0 p-lg-auto">
                        <a href="';
                        if($googlemap){
                            echo $googlemap;
                        }else{
                            echo "javascript:void(0);";
                        }
                        echo '" target="_blank" class="btn btn-primary btn-sm mt-3 w-50';
                        if(!$googlemap){
                            echo " disabled";
                        }
                        echo '" data-selector="get-service-map">
                            <i class="fa fa-map mr-3" aria-hidden="true"></i>
                            Haritada Gör
                        </a>
                    </div>
                </div>';
            }
        ?>
    </div>
</div>