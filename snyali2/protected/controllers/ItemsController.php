<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);
class ItemsController extends Controller
{

    public $imagesAsset = '';

    public function beforeAction($action)
    {

        return parent::beforeAction($action);
    }

    public function actionIndex($type = null, $city = null, $vk = null)
    {
        $this->processPageRequest('page');
        $this->pageTitle = Yii::app()->name . ' &gt; ' . 'Объявления';
        $this->registers();



        $criteria = new CDbCriteria();
        if (!empty($city)) {
            $criteria->compare('city.link', $city);
        }

        if (!empty($type)) {
            $criteria->compare('type_data.link', $type);
        }

        $criteria->compare('t.enabled', 1);
        $criteria->addNotInCondition('t.city_id', array(0));
        $criteria->addCondition('t.enabled = 1');
        $criteria->order = 't.created desc';
        $criteria->with = array('city', 'type_data');

        //$adverts = Adverts::model()->with(array('city', 'type_data'))->findAll($criteria);

        $pageSize = 10;
        $view = 'index';
        if ($vk) {
            $view = 'index_iframe';
            $pageSize = 5;
        }

        $dataProvider = new CActiveDataProvider('Adverts', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
                'pageVar' => 'page'
            ),
        ));

        $this->_type = AdvertTypes::model()->findByAttributes(array('link' => $type));


        if (!Yii::app()->request->isAjaxRequest) {
            if (!empty($city)) {
                $this->_city = Cities::model()->findByAttributes(array('link' => $city));
            } else if (!empty($this->_city)) {
                Yii::app()->request->redirect(Yii::app()->createAbsoluteUrl('items/index', array('city' => $this->_city->link)));
            }
        }

        if (Yii::app()->request->isAjaxRequest) {
            $this->renderPartial('_loop', array(
                'dataProvider' => $dataProvider,
            ));
            Yii::app()->end();
        } else {
            $this->render($view, array(
                'dataProvider' => $dataProvider,
            ));
        }
    }

    public function actionItem($city, $type, $id)
    {
        $this->pageTitle = Yii::app()->name . ' &gt; ' . 'Объявления';
        // Регаем bootstrap
        $bootstrap = $this->assetManager->publish(Yii::app()->params->vendorPath . '/twitter/bootstrap/dist/');
        $stylesheet = $this->assetManager->publish(Yii::app()->basePath . '/styles/');
        $jquery = $this->assetManager->publish(Yii::app()->params->vendorPath . '/components/jquery/');
        $lightbox = $this->assetManager->publish(Yii::app()->params->vendorPath . '/bootstrap-plus/bootstrap-media-lightbox/');

        $this->clientScript->registerCssFile($bootstrap . '/css/bootstrap.min.css');
        $this->clientScript->registerCssFile($lightbox . '/bootstrap-media-lightbox.css');
        $this->clientScript->registerCssFile($stylesheet . '/layout.css');

        $this->clientScript->registerScriptFile($jquery . '/jquery.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($bootstrap . '/js/bootstrap.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($lightbox . '/bootstrap-media-lightbox.min.js', CClientScript::POS_HEAD);

        $criteria = new CDbCriteria();
        $criteria->compare('city.link', $city);
        $criteria->compare('type_data.link', $type);
        $criteria->compare('t.id', $id);
        $criteria->compare('t.enabled', 1);

        $advert = Adverts::model()->with(array('city', 'type_data'))->find($criteria);

        $this->_type = $advert->type_data;

        if (!$advert) {
            throw new CHttpException(404, 'Объявление не найдено.');
        }

        $this->render('item', array(
            'advert' => $advert,
        ));
    }

    protected function processPageRequest($param = 'page')
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST[$param]))
            $_GET[$param] = Yii::app()->request->getPost($param);
    }

    protected function registers()
    {
        // Регаем bootstrap
        $bootstrap = $this->assetManager->publish(Yii::app()->params->vendorPath . '/twitter/bootstrap/dist/');
        $stylesheet = $this->assetManager->publish(Yii::app()->basePath . '/styles/');
        $jquery = $this->assetManager->publish(Yii::app()->params->vendorPath . '/components/jquery/');
        $lightbox = $this->assetManager->publish(Yii::app()->params->vendorPath . '/bootstrap-plus/bootstrap-media-lightbox/');
        $js = $this->assetManager->publish(Yii::app()->basePath . '/js/');
        $this->imagesAsset = $this->assetManager->publish(Yii::app()->basePath . '/images/');

        $this->clientScript->registerCssFile($bootstrap . '/css/bootstrap.min.css');
        $this->clientScript->registerCssFile($lightbox . '/bootstrap-media-lightbox.css');
        $this->clientScript->registerCssFile($stylesheet . '/layout.css');

        $this->clientScript->registerScriptFile($jquery . '/jquery.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($bootstrap . '/js/bootstrap.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($lightbox . '/bootstrap-media-lightbox.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($js . '/media.js?rand=' . time(), CClientScript::POS_HEAD);
    }

    public function actionSearch($search = null, $city = null, $type = null)
    {

        $this->processPageRequest('page');
        $this->pageTitle = Yii::app()->name . ' &gt; ' . 'Поиск объявлений';
        $this->registers();


        

        $criteria = new CDbCriteria();

        $sphinx = new Sphinx();
        $escpedSearch = $sphinx->escape($search);
        $ids = array();

        $params = array();
        
        if ($cityState = Yii::app()->user->getState('city_id')) {
            $params['city_id'] = (int) $cityState;
        }
        
        if (!empty($city)) {
            if ($city = Cities::model()->findByAttributes(array('link' => $city))) {
                $params['city_id'] = $city->id;
            }
        }
        
        if (!empty($type)) {
            if ($type = AdvertTypes::model()->findByAttributes(array('link' => $type))) {
                $params['type'] = $type->id;
            }
        }

        $count = $sphinx->count($escpedSearch, $params);
        if ($count > 0) {
            $ids = $sphinx->search($escpedSearch, $params);
        }
        $criteria->addInCondition('t.id', $ids);

        $criteria->addNotInCondition('t.city_id', array(0));
        $criteria->addCondition('t.enabled = 1');
        $criteria->order = 't.created desc';
        $criteria->with = array('city', 'type_data');

        //$adverts = Adverts::model()->with(array('city', 'type_data'))->findAll($criteria);

        $pageSize = 10;
        $view = 'search';

        $dataProvider = new CActiveDataProvider('Adverts', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
                'pageVar' => 'page'
            ),
        ));



        if (Yii::app()->request->isAjaxRequest) {
            $this->renderPartial('_loop', array(
                'dataProvider' => $dataProvider,
            ));
            Yii::app()->end();
        } else {
            $this->render($view, array(
                'dataProvider' => $dataProvider,
                'totalFound' => $count,
                'search' => htmlentities($search),
                'params' => $params,
            ));
        }
    }

}
