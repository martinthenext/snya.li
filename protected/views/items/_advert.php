<article class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <a href="<?= Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id)) ?>">
                <?= $data->type_data->title ?>, <?= 'г. ' . $data->city->title ?><?php if (!empty($data->metro->title)) echo ', м. ' . $data->metro->title; ?>
            </a>
        </h3>
    </div>
    <div class="panel-body">
        <div class="media" data-media-id="<?= $data->id ?>" <?php if (!isset($notHide)) : ?>style="display: block; overflow: hidden; max-height: 200px;"<?php endif; ?>>
            <?php if (!empty($data->vk_owner_avatar)): ?>
                <div class="col-lg-2 col-md-2">
                    <a href="//vk.com/id<?= $data->vk_owner_id ?>" target="_blank">
                        <img class="media-object" src="<?= $data->vk_owner_avatar ?>" alt="<?= $data->vk_owner_first_name . ' ' . $data->vk_owner_last_name ?> title="<?= $data->vk_owner_first_name . ' ' . $data->vk_owner_last_name ?>">
                    </a>
                    <a href="//vk.com/id<?= $data->vk_owner_id ?>" target="_blank"><?= $data->vk_owner_first_name ?></a>
                    <?php foreach ($data->contacts as $contact): ?>
                                                                    <!--<span class="btn btn-xs btn-info"><?= $contact->value ?></span>-->
                        <br /><?= $contact->button ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="col-lg-<?= empty($data->attachments) ? '10' : '8' ?> col-md-<?= empty($data->attachments) ? '10' : '8' ?>">
                <?= $data->content ?>
            </div>
            <?php if (!empty($data->attachments)): ?>
                <div class="col-lg-2 col-md-2">
                    <?php foreach ($data->attachments as $attachment): ?>
                        <a class="lightbox-<?= $data->id ?>" href="<?= $attachment->src_lightbox ?>" title="Фотографии">
                            <img src="<?= $attachment->src ?>" class="img-thumbnail" />
                        </a>
                    <?php endforeach; ?>
                    <script type="text/javascript">
                        $('.lightbox-<?= $data->id ?>').lightbox();
                    </script>
                </div>
            <?php endif; ?>

        </div>
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
                    <a href="//vk.com/wall<?= $data->vk_owner_id ?>_<?= $data->vk_post_id ?>" target="_blank">
                        <?= Helper::Time($data->created) ?>
                    </a>
                </div>
            </div>
        </div>

        <?php if (Yii::app()->user->hasState('admin') || Yii::app()->user->hasState('moderator')): ?>
            <div class="row">
                <button class="btn btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> Забанить пользователя</button>
                <button class="btn btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> Отключить объявление</button>
            </div>
        <?php endif; ?>
    </div>
</article>