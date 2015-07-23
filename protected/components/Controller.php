<?php

class Controller extends CController
{

    protected $_clientScript;
    protected $_assetManager;
    protected $_type;
    protected $_navbarWithoutSearch = false;
    public $cities;
    public $types;
    public $pageDescription = 'Поиск жилья Вконтакте без риелторов';
    public $pageKeywords = 'сдам, сниму, аренду, продам, куплю, квартиру, дом';

    public function beforeAction($action)
    {
        City::run();

        $criteria = new CDbCriteria();
        $criteria->order = 't.title asc';
        $criteria->condition = '(select count(a.id) from {{adverts}} a where a.city_id = t.id and a.enabled = 1) > 0';
        $this->cities = Cities::model()->cache(86400)->findAll($criteria);

        $criteria = new CDbCriteria();
        $criteria->order = 't.title asc';
        $this->types = AdvertTypes::model()->cache(86400)->findAll($criteria);

        header("xDevUserIP:" . Yii::app()->request->getUserHostAddress());
        return parent::beforeAction($action);
    }

    /**
     * 
     * @return CClientScript
     * @example $this->clientScript->registerCoreScript('jquery'); Служит для быстрого вызова ClientScript
     */
    public function getClientScript()
    {
        if (empty($this->_clientScript)) {
            $this->_clientScript = Yii::app()->getComponent('clientScript');
        }

        return $this->_clientScript;
    }

    /**
     * 
     * @return CAssetManager
     */
    public function getAssetManager()
    {
        if (empty($this->_assetManager)) {
            $this->_assetManager = Yii::app()->getComponent('assetManager');
        }

        return $this->_assetManager;
    }

    public function getNavbarOptions()
    {

        $navAdvertTypes = array();
        foreach ($this->types as $advertType) {
            $urlParams = array('type' => $advertType->link);

            if (!empty(City::getModel()->link)) {
                $urlParams['city'] = City::getModel()->link;
            }

            $navAdvertTypes[] = array(
                'label' => $advertType->title,
                'url' => Yii::app()->createAbsoluteUrl("items/index", $urlParams),
            );
        }

        $options = array(
            'label' => CHtml::image("/images/logo.png", $advertType->title),
            'items' => array(
                array(
                    'label' => !empty(City::getModel()->title) ? City::getModel()->title : 'Выбрать город',
                    'url' => '#',
                    'linkOptions' => array(
                        'onclick' => 'return false;',
                        'data-toggle' => "modal",
                        'data-target' => "#select-city"
                    ),
                ),
                array('label' => !empty($this->_type) ? $this->_type->title : 'Все объявления', 'url' => "#", 'items' => $navAdvertTypes),
            //array('label' => 'Вход', 'url' => array('site/login'), 'visible' => Yii::app()->user->isGuest),
            ),
            'withoutSearch' => $this->_navbarWithoutSearch,
        );


        $options['items'][] = array(
            'label' => 'Добавить объявление',
            'url' => Yii::app()->createUrl('items/add'),
            'linkOptions' => array(
                'class' => '',
            ),
        );


        if (Yii::app()->user->checkAccess('admin')) {
            $options['items'][] = array(
                'label' => 'Админка',
                'url' => Yii::app()->createUrl('admin/index'),
            );
        }

        if (!Yii::app()->user->isGuest) {
            $options['items'][] = array(
                'label' => 'Выход (' . Yii::app()->user->getState('name') . ')',
                'url' => Yii::app()->createUrl('user/logout'),
            );
        }

        return $options;
    }

}
