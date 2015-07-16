<div class="row">
    <!--<div class="col-lg-3 col-md-2">

    <?= $this->renderPartial('_panel') ?>

    </div>-->

    <div class="col-lg-12 col-md-12 col-sm-12">
        <?php $this->renderPartial('_advert', array('data' => $advert, 'notHide' => 1)); ?>
    </div>
</div>

<?php if (!empty($advert->similars)): ?>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <h3>Похожие объявления:</h3>
        </div>
    </div>
    <?php $rowOpened = false; ?>
    <?php foreach ($advert->similars as $key => $similar): ?>
        <?php if ($key % 2 == 0): $rowOpened = true; ?>
            <div class="row">
            <?php endif; ?>
            <?php $this->renderPartial('_advert_mini', array('data' => $similar)); ?>
            <?php if ($key % 2 != 0): $rowOpened = false; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($rowOpened): ?></div><?php endif; ?>
<?php endif; ?>


