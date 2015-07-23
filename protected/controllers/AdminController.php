<?php

class AdminController extends Controller
{

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                // Разрешаем все действия пользователям с ролью admin
                'roles' => array('admin'),
            ),
            array('deny',
                // Запрещаем все действия всем остальным
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        $this->registers();
        $this->render('index');
    }

    /**
     * Добавляет источник в черный список
     * @param str $vk_user_id
     */
    public function actionAddBlacklist($vk_owner_id)
    {
        Adverts::model()->updateAll(array('enabled'=>0), 'vk_owner_id = :vk_owner_id', array('vk_owner_id'=>$vk_owner_id));
        $blacklist = new UsersBlacklist();
        $blacklist->comment = 'Из общего интерфейса';
        $blacklist->vk_user_id = (int) $vk_owner_id;
        $blacklist->save(false);
        echo "OK";
    }
    
    /**
     * Отключает объявление
     * @param int $item_id
     */
    public function actionDisableitem($item_id)
    {
        if ($item = Adverts::model()->findByPk($item_id)) {
            $item->enabled = 0;
            $item->save(false);
            echo "OK";
        }
    }

    function registers()
    {
        $bootstrap = $this->assetManager->publish(Yii::app()->params->vendorPath . '/twitter/bootstrap/dist/');
        $stylesheet = $this->assetManager->publish(Yii::app()->basePath . '/styles/');
        $jquery = $this->assetManager->publish(Yii::app()->params->vendorPath . '/components/jquery/');
        $js = $this->assetManager->publish(Yii::app()->basePath . '/js/');

        $this->clientScript->registerCssFile($bootstrap . '/css/bootstrap.min.css');
        $this->clientScript->registerCssFile($stylesheet . '/layout.css');

        $this->clientScript->registerScriptFile($jquery . '/jquery.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($bootstrap . '/js/bootstrap.min.js', CClientScript::POS_HEAD);
    }

}
