<?php header("Access-Control-Allow-Origin: *"); ?>
<div class="container mb-5">
    <h3 class="mt-3 mb-5">Randevu Seçim Ekranı</h3>
    <div class="content-body">
        <div class="form-group">
            <input type="text" class="form-control" placeholder="Plaka Giriniz" data-selector="car-license-plate" />
        </div>
        <div class="form-group">
            <select class="form-control" data-selector="province">
                <option>İl Seçiniz</option>
                <?php
                    for ($index = 0; $index < count($_POST); $index++) {
                        echo '<option value="' . $_POST[$index]["id"] . '" >' . $_POST[$index]["name"] . '</option>';
                    }
                ?>
            </select>
        </div>
        <div class="form-group">
            <select class="form-control disabled" data-selector="sublocation" disabled>
                <option>İlçe Seçiniz</option>
            </select>
        </div>
    </div>
    <div class="content-footer">
        <div class="information-content">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <span>
                    Servis listesini görüntülemek için il ve ilçe seçimi yapınız.
                </span>
            </div>
        </div>
    </div>
</div>