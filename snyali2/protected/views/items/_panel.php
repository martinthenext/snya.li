<div class="panel panel-default">
    <div class="panel-heading">
        <span class="glyphicon glyphicon-flag"></span>
        Авторизация
    </div>
    <div class="panel-body">
        <?php if (!Yii::app()->user->id): ?>
            <a href="<?= Yii::app()->createAbsoluteUrl('user/login', array('service' => 'vkontakte')) ?>">Войти через vk.com</a>
        <?php else: ?> 
            <a href="<?= Yii::app()->createAbsoluteUrl('user/logout') ?>">Выйти (
                <?php if (Yii::app()->user->hasState('admin')): ?>
                    <span class="glyphicon glyphicon-king"></span>
                <?php endif; ?>
                <?= Yii::app()->user->name ?> )</a>
        <?php endif; ?>
    </div>
</div>
