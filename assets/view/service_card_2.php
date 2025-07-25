<?php
    header("Access-Control-Allow-Origin: *");
    require_once(__DIR__ . "/../../config.php");
    $googlemap = ($_POST["latitude"] !== "0" && $_POST["longitude"] !== "0") ? "https://www.google.com/maps/search/?api=1&query=" . $_POST["latitude"] . "," . $_POST["longitude"] : FALSE;
?>
<div class="row align-items-center border m-0 mb-3 p-3" style="background: rgba(0, 0, 0, 0.1);border-radius: 7px;">
    <div class="col-lg-3 pl-0">
    <!--<img src="<?= isset($_POST["images"]) ? (strpos($_POST["images"], ",") === FALSE) ? $_POST["images"] : explode(",", $_POST["images"])[0] : DEFAULT_IMAGE ?>" class="card-img" alt="<?= $_POST["name"] ?>">-->
        <div style="border-radius: 7px;width: 100%;height:200px;background: url('<?= isset($_POST["images"]) ? (strpos($_POST["images"], ",") === FALSE) ? $_POST["images"] : explode(",", $_POST["images"])[0] : DEFAULT_IMAGE ?>');background-repeat: no-repeat;background-size: cover;background-position: center;"></div>
    </div>
    <div class="col-md-6 col-lg-5 justify-content-center mt-3 mt-lg-0" style="display: grid;font-size: 2rem;">
        <div class="row">
            <h5><?= $_POST["name"] ?></h5>
        </div>
        <div class="row">
            <?= $_POST["address"] ?>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 d-flex justify-content-center p-0 p-lg-auto">
        <a href="<?= ($googlemap) ? $googlemap : "javascript:void(0);" ?>" target="_blank" class="btn btn-primary btn-sm mt-3 w-50 <?= ($googlemap) ? "" : "disabled" ?>" data-selector="get-service-map">
            <i class="fa fa-map mr-3" aria-hidden="true"></i>
            Haritada Gör</a>
    </div>
</div>