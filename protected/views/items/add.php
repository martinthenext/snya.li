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
    });
</script>
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
            ),
        ));
        ?>

        <?= $form->errorSummary($item, null, null, ['class' => 'alert alert-danger']); ?>
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
            <?= $form->labelEx($item, 'text', ['class' => 'control-label']) ?>
            <?= $form->textArea($item, 'text', ['class' => 'form-control']); ?>
        </div>

        <span class="help-block">Поля, отмеченные звездочкой <span class="required">*</span>, обязательны для заполнения.</span>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>