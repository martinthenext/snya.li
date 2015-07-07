<div class="row">
    <div class="col-lg-12 col-md-12">
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

                    var $win = $('html');
                    var $marker = $('#showMore');
                    
                    $win.scroll(function() {
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

