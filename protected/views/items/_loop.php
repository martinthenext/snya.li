<?php

$this->widget('zii.widgets.CListView', array(
    'dataProvider' => $dataProvider,
    'itemView' => '_advert',
    'ajaxUpdate' => true,
    'template' => "{items}\n{pager}",
    'pager' => array(
        'header'=>'',
        'htmlOptions' => array(
            'class' => 'hidden',
            'style'=>'display: none;'
        )
    ),
));
?>