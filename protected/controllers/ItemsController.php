<?php

class ItemsController extends Controller
{

    public $imagesAsset = '';

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
                'actions' => array('add'),
                'roles' => array('user'),
            ),
            array('deny',
                'actions' => array('add'),
                'roles' => array('guest'),
            ),
            array('allow',
                'users' => array('*'),
            ),
        );
    }

    public function beforeAction($action)
    {

        return parent::beforeAction($action);
    }

    public function actionCityChange($city)
    {
        if ($cityModel = Cities::model()->findByAttributes(array('link' => $city))) {
            City::setCurrentCity($cityModel->id);
        }

        City::redirect();
    }

    /**
     * Список объявлений по городу
     * @param string $type
     * @param string $city
     * @param int $vk
     * @throws CHttpException
     * 
     * @todo Просклонять города для description и keywords
     */
    public function actionIndex($type = null, $city = null)
    {

        if (empty($city) && !Yii::app()->request->isAjaxRequest) {
            if (!preg_match("/(yandex\.com|Googlebot)/isu", Yii::app()->request->getUserAgent())) {
                City::redirect();
            }
        }

        if (!empty($type)) {
            $this->_type = AdvertTypes::model()->findByAttributes(array('link' => $type));
        }

        $city = empty($city) ? 'moskva' : $city;

        if (!Cities::model()->countByAttributes(array('link' => $city))) {
            throw new CHttpException(404, 'Страница не найдена');
        }

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

        $criteria->addNotInCondition('t.city_id', array(0));
        $criteria->addCondition('t.enabled = 1');
        $criteria->order = 't.created desc';
        $criteria->with = array('city', 'type_data');

        $pageSize = 10;
        $view = 'index';

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
            ));
        }
    }

    public function actionItem($city, $type, $id)
    {

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

        $this->pageTitle = Yii::app()->name . ' &gt; ' . 'Объявления';
        $this->pageKeywords = $advert->keywords;
        $this->pageDescription = $advert->shortDescription;

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
        //$this->imagesAsset = $this->assetManager->publish(Yii::app()->basePath . '/images/');

        $this->clientScript->registerCssFile($bootstrap . '/css/bootstrap.min.css');
        $this->clientScript->registerCssFile($lightbox . '/bootstrap-media-lightbox.css');
        $this->clientScript->registerCssFile($stylesheet . '/layout.css');

        $this->clientScript->registerScriptFile($jquery . '/jquery.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($bootstrap . '/js/bootstrap.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($lightbox . '/bootstrap-media-lightbox.js?time=' . time(), CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($js . '/media.js?rand=' . time(), CClientScript::POS_HEAD);
    }

    public function actionSearch($search = null, $city = null, $type = null)
    {

        if (!empty($type)) {
            $this->_type = AdvertTypes::model()->findByAttributes(array('link' => $type));
        }

        if (preg_match("/(\?|&)/isu", Yii::app()->request->url)) {

            $params = array(
                'search' => trim($search),
            );

            if (!empty($city) && preg_match("/^[\w\-\_]+$/isu", $city)) {
                $params['city'] = $city;
            } else {
                $params['city'] = City::getModel()->link;
            }

            if (!empty($this->_type->link)) {
                $params['type'] = $this->_type->link;
            }

            Yii::app()->request->redirect(Yii::app()->createAbsoluteUrl("items/search", $params));
        }


        $this->_navbarWithoutSearch = true;



        $this->processPageRequest('page');
        $this->pageTitle = Yii::app()->name . ' &gt; ' . 'Поиск объявлений';
        $this->registers();




        $criteria = new CDbCriteria();

        $sphinx = new Sphinx();
        $escpedSearch = $sphinx->escape($search);
        $ids = array();

        $params = array();

        if (!empty(City::getModel()->id)) {
            $params['city_id'] = (int) City::getModel()->id;
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

    public function actionAdd()
    {
        $this->registers();
        $item = new Adverts('add');
        $imageModel = new Images('add');
        if (!empty(Yii::app()->request->getPost('Adverts'))) {
            $item->attributes = Yii::app()->request->getPost('Adverts');

            /**
             * @todo Отвязать объявления от vk
             */
            if (Yii::app()->user->model->service == 'vkontakte') {
                $item->vk_owner_id = Yii::app()->user->model->service_id;
            } else {
                Yii::app()->user->logout();
                Yii::app()->user->redirect('/');
            }

            $item->enabled = (int) Yii::app()->user->checkAccess('moderator');

            $api = new VkApi(4934698, '3djYV1o2nXEQCzydPGTn', '8b17eb5b67e4534cf64cc7ea70a8b488621d1bc38d48db89b77c9c9fa49499a9606d8aee6d34d72feb5d0');
            $userResult = $api->run('users.get', [
                'user_ids' => Yii::app()->user->model->service_id,
                'fields' => 'status,activities,interests,about,city,country,contacts,screen_name,photo_100',
                    ], false);
            $userResult = (array) $userResult;
            if (!empty($userResult[0])) {
                $item->vk_owner_avatar = !empty($userResult[0]->photo_100) ? $userResult[0]->photo_100 : '';
                $item->vk_owner_first_name = $userResult[0]->first_name;
                $item->vk_owner_last_name = $userResult[0]->last_name;
            }
            $imageModel->images = UploadedFile::getInstances($imageModel, 'images');

            if ($imageModel->validate() && $item->validate()) {

                $item->save(false);
                $imageModel->advert_id = $item->id;
                $imageModel->save(false);

                return $this->render('add_success');
            }
        }

        $this->render('add', array(
            'item' => $item,
            'image' => $imageModel,
        ));
    }

    public function actionTest()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 't.title asc';
        echo "<pre>";
        foreach (Cities::model()->findAll($criteria) as $city) {

            echo $city->title . ":\t " . $city->link . PHP_EOL;
        }
        echo "</pre>";
    }

}
