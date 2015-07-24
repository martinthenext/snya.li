<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    $(function () {
        var editor = CKEDITOR.replace('Adverts_text', {
            customConfig: '', // отключаем загрузку конфига редактора
            // Грузим css магазина что бы описание нифига не отличалось
            extraPlugins: 'autogrow', // автоматическая высота редактора
            autoGrow_onStartup: true, // разворачиваем редактор сразу
            htmlEncodeOutput: false,
            entities: false,
            // Далее пошли тулбары
            skin: 'bootstrapck',
            toolbar: 'Custom',
            toolbar_Custom: [
                ['Maximize', 'Bold', 'RemoveFormat', 'BulletedList'],
                ['Undo', 'Redo'],
                // Для теста, убрать
                '/', ['Source']
            ]
        });
        $('#Images_images').fileupload({
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('.media-images-container').append(
                            '<div class="col-xs-6 col-md-3" ><div class="thumbnail"><img src="' + file.url + '" />' +
                            '<input type="hidden" name="Images[]" value="' + file.id + '" />' +
                            '<button class="btn btn-danger btn-xs" onclick="if (!confirm("Действительно удалить фотографию?")) $(this).parent().parent().remove(); return false;">Удалить</button> <br />' +
                            '</div></div>'
                            );
                });
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
            }
        }).bind('fileuploadsend', function (e, data) {
            // This feature is only useful for browsers which rely on the iframe transport:
            if (data.dataType.substr(0, 6) === 'iframe') {
                // Set PHP's session.upload_progress.name value:
                var progressObj = {
                    name: 'PHP_SESSION_UPLOAD_PROGRESS',
                    value: (new Date()).getTime()  // pseudo unique ID
                };
                data.formData.push(progressObj);
                // Start the progress polling:
                if (typeof (data.context.data) != 'undefined') {
                    data.context.data('interval', setInterval(function () {
                        $.get('progress.php', $.param([progressObj]), function (result) {
                            e = $.Event('progress', {bubbles: false, cancelable: true});
                            $.extend(e, result);
                            ($('#Images_images').data('blueimp-fileupload') ||
                                    $('#Images_images').data('fileupload'))._onProgress(e, data);
                        }, 'json');
                    }, 1000));
                }
            }
        }).bind('fileuploadalways', function (e, data) {
            if (typeof (data.context) != 'undefined') {
                clearInterval(data.context.data('interval'));
            }
        });
    });</script>
<div class="row">
    <div class="col-lg-12">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'add-item-form',
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'htmlOptions' => array(
                'class' => 'inline-form',
                'errorCss' => 'has-error',
                'enctype' => 'multipart/form-data',
            ),
        ));
        ?>

        <?= $form->errorSummary($item, null, null, ['class' => 'alert alert-danger']); ?>
        <?= $form->errorSummary($image, null, null, ['class' => 'alert alert-danger']); ?>
        <?= $form->hiddenField($item, 'formId', ['id' => 'form_id']) ?>
        <div class="form-group">
            <?= $form->labelEx($item, 'type', ['class' => 'control-label']) ?>
            <?=
            $form->dropDownList($item, 'type', CHtml::listData(AdvertTypes::model()->findAll(['order' => 't.title asc']), 'id', 'title'), [
                'class' => 'form-control',
                'empty' => 'Тип объявления',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?= $form->labelEx($item, 'city_id', ['class' => 'control-label']) ?>
            <?=
            $form->dropDownList($item, 'city_id', CHtml::listData(Cities::model()->findAll(['order' => 't.title asc']), 'id', 'title'), [
                'class' => 'form-control',
                'empty' => 'Город',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?= $form->labelEx($item, 'action_id', ['class' => 'control-label']) ?>
            <?=
            $form->dropDownList($item, 'action_id', CHtml::listData(AdvertActions::model()->findAll(['order' => 't.title asc']), 'id', 'title'), [
                'class' => 'form-control',
                'empty' => 'Предмет объявления',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?= $form->labelEx($item, 'text', ['class' => 'control-label']) ?>
            <?= $form->textArea($item, 'text', ['class' => 'form-control']); ?>
        </div>

        <div class="form-group">
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>Загрузить фотографии...</span>
                <input  multiple type="file" name="Images[images][]" id="Images_images" data-url="<?= Yii::app()->createAbsoluteUrl("items/upload", ['formId' => $item->formId]) ?>">
            </span>
            <p class="help-block">Вы можете загрузить до <?= Images::MAX_FILES ?> фотографий в формате png или jpg.</p>
            <div id="progress" class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
            </div>
        </div>

        <div class="row media-images-container">
            <?php foreach ($userForm['images'] as $file): ?>
                <div class="col-xs-6 col-md-3" >
                    <div class="thumbnail">
                        <img src="<?= $file['url'] ?>" />
                        <input type="hidden" name="Images[]" value="<?= $file['id'] ?>" />
                        <button class="btn btn-danger btn-xs" onclick="if (confirm('Действительно удалить фотографию?')) $(this).parent().parent().remove(); return false;">Удалить</button> <br />
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <span class="help-block">Поля, отмеченные звездочкой <span class="required">*</span>, обязательны для заполнения.</span>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>