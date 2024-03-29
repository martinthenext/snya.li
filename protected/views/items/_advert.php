<article class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <a href="<?= Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id)) ?>">
                <?= $data->type_data->title ?><?= (!empty($data->action) ? ' ' . $data->action->subtitle : '') ?>, <?= 'г. ' . $data->city->title ?><?php if (!empty($data->metro->title)) echo ', м. ' . $data->metro->title; ?>
            </a>
            <script type="text/javascript">
                new Ya.share({
                    element: 'yandex-share-<?= $data->id ?>',
                    elementStyle: {
                        'type': 'small',
                        'border': true,
                        'quickServices': ['vkontakte', 'facebook', 'twitter', 'odnoklassniki', 'moimir', 'gplus']
                    },
                    description: "<?= $data->shortDescription ?>",
                    link: "<?= Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id)) ?>",
                    l10n: 'ru',
                    theme: 'counter'
                });
            </script>
            <span id="yandex-share-<?= $data->id ?>"></span>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($data->enabled): ?>
            <div class="media" data-media-id="<?= $data->id ?>" <?php if (!isset($notHide)) : ?>style="display: block; overflow: hidden; max-height: 200px;"<?php endif; ?>>
                <?php if (!empty($data->vk_owner_avatar)): ?>
                    <div class="col-lg-2 col-md-2">
                        <a rel="nofollow" href="//vk.com/id<?= $data->vk_owner_id ?>" target="_blank">
                            <img class="img-circle" src="<?= $data->vk_owner_avatar ?>" alt="<?= CHtml::encode($data->vk_owner_first_name . ' ' . $data->vk_owner_last_name) ?>" title="<?= CHtml::encode($data->vk_owner_first_name . ' ' . $data->vk_owner_last_name) ?>" />
                        </a>
                        <br />
                        <a rel="nofollow" href="//vk.com/id<?= $data->vk_owner_id ?>" target="_blank"><?= $data->vk_owner_first_name ?></a>
                        <?php foreach ($data->contacts as $contact): ?>
                                                                                                                <!--<span class="btn btn-xs btn-info"><?= $contact->value ?></span>-->
                            <br /><?= $contact->button ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="col-lg-10 col-md-10">
                    <?= $data->content ?>
                </div>
                <?php if (!empty($data->images)): ?>
                    <div class="col-lg-10 col-md-10 col-offset-2">
                        <?php foreach ($data->images as $key => $image): ?>
                            <a itemscope itemtype="http://schema.org/ImageObject" class="lightbox-<?= $data->id ?>" href="<?= $image->src ?>" title="<?= CHtml::encode($this->pageTitle . ' фото ' . ($key + 1)) ?>">
                                <img style="width: 100px; height: 100px;" itemprop="contentUrl" src="<?= $image->thumb ?>" class="img-thumbnail" alt="<?= CHtml::encode($this->pageTitle . ' фото ' . ($key + 1)) ?>" title="<?= CHtml::encode($this->pageTitle . ' фото ' . ($key + 1)) ?>"/>
                            </a>
                        <?php endforeach; ?>
                        <script type="text/javascript">
                            $('.lightbox-<?= $data->id ?>').lightbox();
                        </script>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="col-lg-12">
                <h3>Сожалеем, но это объявление было отключено.</h3>
            </div>
        <?php endif; ?>
        <?php if (!isset($notHide)) : ?>

            <div class="media-show-panel" data-media-id="<?= $data->id ?>">
                <span class="glyphicon glyphicon-menu-down"></span> Показать полностью... <span class="glyphicon glyphicon-menu-down"></span>
            </div>
            <script type="text/javascript">
                setTimeout(function () {
                    if ($(".media[data-media-id=<?= $data->id ?>]").height() < 200) {
                        $(".media-show-panel[data-media-id=<?= $data->id ?>]").remove();
                        $(".media[data-media-id=<?= $data->id ?>]").css('overflow', 'visible').css('max-height: 100%');
                    }
                }, 100);
            </script>
        <?php endif; ?>
    </div>
    <div class="panel-footer"> 
        <div class="row">
            <div class="col-md-9 col-lg-9">
                <?php foreach ($data->tags as $tag): ?>
                    <span class="btn btn-xs btn-<?= $tag['class'] ?>"><?= $tag['title'] ?></span>
                <?php endforeach; ?>
            </div>
            <div class="col-md-3 col-lg-3" style="text-align: right;">
                <div class="time-ago small">
                    <?php if (!empty($data->vk_owner_id) && !empty($data->vk_post_id)): ?>
                        <a rel="nofollow" href="//vk.com/wall<?= $data->vk_owner_id ?>_<?= $data->vk_post_id ?>" target="_blank">
                            <?= Helper::Time($data->created) ?>
                        </a>
                    <?php else: ?>
                        <?= Helper::Time($data->created) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (Yii::app()->user->checkAccess('moderator')): ?>
            <div class="row">
                <div class="col-lg-12">
                    <a onclick="return confirm('Точно забанить и отключить объявления?');" target="_blank" href="<?= Yii::app()->createAbsoluteUrl("admin/addblacklist", array('vk_owner_id' => $data->vk_owner_id)) ?>" class="btn btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> Забанить и отключить его объявления</a>
                    <a onclick="return confirm('Точно отключить?');" target="_blank" href="<?= Yii::app()->createAbsoluteUrl("admin/disableitem", array('item_id' => $data->id)) ?>" class="btn btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> Отключить объявление</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</article>