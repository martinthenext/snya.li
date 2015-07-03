<?php

class Controller extends CController
{

    protected $_clientScript;
    protected $_assetManager;
    protected $_city;
    protected $_type;
    public $cities;
    public $types;

    public function beforeAction($action)
    {
        
        $criteria = new CDbCriteria();
        $criteria->order = 't.title asc';
        $this->cities = Cities::model()->cache(86400)->findAll($criteria);

        
        $criteria = new CDbCriteria();
        $criteria->order = 't.title asc';
        $this->types = AdvertTypes::model()->cache(86400)->findAll($criteria);
        
        if (!Yii::app()->user->hasState('city_id')) {
            $city = '';
            if (empty(Yii::app()->request->getParam('api_result'))) {
                $ip = Yii::app()->request->getUserHostAddress();
                $rf = new ReflectionClass(\IgI\SypexGeo\SxGeo::class);
                $classFile = $rf->getFileName();
                $db_file = 'SxGeoCity.dat';
                if (!file_exists($db_file)) {
                    $db_file = dirname($classFile) . DIRECTORY_SEPARATOR . $db_file;
                }
                $geo = new \IgI\SypexGeo\SxGeo($db_file);
                $city = $geo->getCityFull($ip);
            } else {
                $apiResult = Yii::app()->request->getParam('api_result');
                $apiResult = @json_decode($apiResult);
                if (!empty($apiResult->response[0]->city)) {
                    if ($this->_city = Cities::model()->findByAttributes(array('vk_city_id'=>$apiResult->response[0]->city))) {
                        Yii::app()->user->setState('city_id', $this->_city->id);
                    }
                }
            }
            //var_dump($city);
            //exit();
            if (!empty($city)) {
                
                $cityModel = Cities::model()->findByAttributes(array('geo_city_id'=>$city['city']['id']));
                if (!$cityModel) {
                    
                    $cityCriteria = new CDbCriteria();
                    $cityCriteria->condition = 't.title like :title and t.with_geo = 0';
                    $cityCriteria->params = array(
                        'title'=>$city['city']['name_ru'],
                        //'region'=>$city['region']['name_ru'],
                    );
                    $cityCriteria->limit = 1;
                    $cityModel = Cities::model()->find($cityCriteria);
                    if ($cityModel) {
                        
                        $cityModel->with_geo = 1;
                        $cityModel->geo_city_id = $city['city']['id'];
                        $cityModel->link = preg_replace("/[^\w\-_]/isu", "-", strtolower($city['city']['name_en']));
                        $cityModel->geo_lat = $city['city']['lat'];
                        $cityModel->geo_lon = $city['city']['lon'];
                        $cityModel->geo_region_id = $city['region']['id'];
                        $cityModel->geo_region_link = preg_replace("/[^\w\-_]/isu", "-", strtolower($city['region']['name_en']));
                        $cityModel->geo_region_title = $city['region']['name_ru'];
                        $cityModel->save();
                    }
                }
                
                if ($cityModel) {
                    Yii::app()->user->setState('city_id', $cityModel->id);
                    $this->_city = $cityModel;
                }
            }
        } else if (empty($this->_city)) {
            $this->_city = Cities::model()->findByPk(Yii::app()->user->getState('city_id'));
        }
        
        if (empty($this->_city)) {
            $this->_city = Cities::model()->findByPk(1);
        }
        

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
            
            if (!empty($this->_city->link)) {
                $urlParams['city'] = $this->_city->link;
            }
            
            $navAdvertTypes[] = array(
                'label' => $advertType->title,
                'url' => Yii::app()->createAbsoluteUrl("items/index", $urlParams),
            );
        }

        return array(
            'label' => CHtml::image("/images/logo.png", $advertType->title),
            'items' => array(
                array(
                    'label' => !empty($this->_city->title) ? $this->_city->title : 'Выбрать город',
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
        );
    }

}
