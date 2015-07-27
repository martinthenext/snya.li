<div class="col-lg-6 col-md-6 col-sm-12">
    <article class="panel panel-default similar">
        <div class="panel-heading">
            <h3 class="panel-title">
                <a href="<?= Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id)) ?>">
                    <?= $data->type_data->title ?>, <?= 'г. ' . $data->city->title ?><?php if (!empty($data->metro->title)) echo ', м. ' . $data->metro->title; ?> <small><?= Helper::Time($data->created) ?></small>
                </a>
            </h3>
        </div>
        <div class="panel-body">
            <div class="media" data-media-id="<?= $data->id ?>" <?php if (!isset($notHide)) : ?>style="display: block; overflow: hidden; max-height: 200px;"<?php endif; ?>>
                <div class="col-lg-12 col-md-12">
                    <?php if (!empty($data->vk_owner_avatar)): ?>
                        <img style="max-width: 80px; max-height: 80px; float: left; margin: 7px;" class="img-circle" src="<?= $data->vk_owner_avatar ?>" alt="<?= CHtml::encode($data->vk_owner_first_name . ' ' . $data->vk_owner_last_name) ?>" title="<?= CHtml::encode($data->vk_owner_first_name . ' ' . $data->vk_owner_last_name) ?>" />
                    <?php endif; ?>
                    <?= $data->shortContent ?>
                        <br />
                        <a href="<?= Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id)) ?>">
                            Открыть объявление
                        </a>
                </div>

            </div>
        </div>
        <div class="panel-footer"> 
            <?php foreach ($data->tags as $tag): ?>
                <span class="btn btn-xs btn-<?= $tag['class'] ?>"><?= $tag['title'] ?></span>
            <?php endforeach; ?>
        </div>
    </article>
</div>