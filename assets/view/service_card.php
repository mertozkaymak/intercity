<?php
    header("Access-Control-Allow-Origin: *");
    require_once(__DIR__ . "/../../config.php");
    $googlemap = ($_POST["latitude"] !== "0" && $_POST["longitude"] !== "0") ? "https://www.google.com/maps/search/?api=1&query=" . $_POST["latitude"] . "," . $_POST["longitude"] : FALSE;
?>
<div class="card mb-3" style="max-width: 540px;">
    <div class="row d-flex align-items-center no-gutters">
        <div class="col-md-4">
            <img src="<?= isset($_POST["images"]) ? (strpos($_POST["images"], ",") === FALSE) ? $_POST["images"] : explode(",", $_POST["images"])[0] : DEFAULT_IMAGE ?>" class="card-img" alt="<?= $_POST["name"] ?>">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title"><?= $_POST["name"] ?></h5>
                <p class="card-text small"><?= $_POST["address"] ?></p>
                <p class="card-text d-flex justify-content-center align-items-center mt-3">
                    <a href="javascript:void(0);" class="btn btn-primary btn-sm w-50" data-selector="get-service-appointment-data" data-location-id="<?= $_POST["sid"] ?>" data-avaiable-dates="<?= urlencode(json_encode($_POST["avaiable_dates"])) ?>">Seç</a>
                    <a href="<?= ($googlemap) ? $googlemap : "javascript:void(0);" ?>" target="_blank" class="btn btn-info btn-sm w-25 ml-3 <?= ($googlemap) ? "" : "disabled" ?>" data-selector="get-service-map">
                        <i class="fa fa-map" aria-hidden="true"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>