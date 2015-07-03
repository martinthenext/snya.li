<div class="row">
    <!--<div class="col-lg-3 col-md-2">
        <?= $this->renderPartial('_panel') ?>
    </div>-->

    <div class="col-lg-12 col-md-12">
        
        <div class="panel panel-default">
            <div class="panel-heading">Поиск</div>
            <div class="panel-body">
                <form method="get" action="<?=Yii::app()->createAbsoluteUrl('items/search')?>">
                    <div class="form-group">
                        <input name="search" type="text" value="<?=$search?>" class="form-control" placeholder="Сдаю квартиру в Москве">
                    </div>
                    <div class="form-group">
                        <select name="city" class="form-control">
                            <option value="">Город</option>
                            <?php foreach ($this->cities as $city):?>
                                <option<?=(!empty($params['city_id']) && $params['city_id'] == $city->id) ? ' selected' : ''?> value="<?=$city->link?>"><?=$city->title?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="type" class="form-control">
                            <option value="">Все объявления</option>
                            <?php foreach ($this->types as $type):?>
                                <option<?=(!empty($params['type']) && $params['type'] == $type->id) ? ' selected' : ''?> value="<?=$type->link?>"><?=$type->title?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-default">Найти</button>
                </form>
            </div>
            
            <div class="panel-footer">
                <?php if (!$totalFound):?>
                Ничего не найдено, попробуйте уточнить критерии поиска.
                <?php else: ?>
                Найдено <?=Yii::t('app', '{n} объявление|{n} объявления|{n} объявлений|{n} объявления', $totalFound)?>.
                <?php endif;?>
            </div>
        </div>

        <div id="listView">
            <?php $this->renderPartial('_loop', array('dataProvider' => $dataProvider)); ?>
        </div>
        
        <?php if ($dataProvider->totalItemCount > $dataProvider->pagination->pageSize): ?>
            <div class="form-actions">
                <button id="showMore" class="btn btn-primary">
                    <span id="triangle" class="glyphicon glyphicon-triangle-bottom"></span> Показать ещё
                </button>
            </div>
            <script type="text/javascript">
                /*<![CDATA[*/
                (function ($)
                {

                    // запоминаем текущую страницу и их максимальное количество
                    var page = parseInt('<?php echo (int) Yii::app()->request->getParam('page', 1); ?>');
                    var pageCount = parseInt('<?php echo (int) $dataProvider->pagination->pageCount; ?>');

                    var loadingFlag = false;
                    var $win = $(window);

                    var $marker = $('#showMore');

                    $win.scroll(function () {
                        if ($win.scrollTop() + $win.height() >= $marker.offset().top) {
                            $('#showMore').trigger('click');
                        }
                    });



                    $('#showMore').click(function ()
                    {
                        // защита от повторных нажатий
                        if (!loadingFlag)
                        {
                            // выставляем блокировку
                            loadingFlag = true;
                            $('#showMore').addClass('disabled').attr('disabled', true);
                            $.ajax({
                                type: 'post',
                                url: window.location.href,
                                data: {
                                    // передаём номер нужной страницы методом POST
                                    'page': page + 1
                                },
                                success: function (data)
                                {
                                    // увеличиваем номер текущей страницы и снимаем блокировку
                                    page++;
                                    loadingFlag = false;

                                    // прячем анимацию загрузки
                                    $('#showMore').removeClass('disabled').removeAttr('disabled');
                                    // вставляем полученные записи после имеющихся в наш блок
                                    $('#listView').append(data);

                                    // если достигли максимальной страницы, то прячем кнопку
                                    if (page >= pageCount) {
                                        $win.unbind('scroll');
                                        $('#showMore').hide();
                                    }
                                }
                            });
                        }
                        return false;
                    })
                })(jQuery);
                /*]]>*/
            </script>

        <?php endif; ?>
    </div>

</div>

