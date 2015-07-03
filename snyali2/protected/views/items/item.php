<div class="row">
    <!--<div class="col-lg-3 col-md-2">

        <?= $this->renderPartial('_panel') ?>

    </div>-->

    <div class="col-lg-12 col-md-12">

        <?php $this->renderPartial('_advert', array('data'=>$advert, 'notHide'=>1));?>

    </div>
</div>

