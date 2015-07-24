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
                'actions' => array('add', 'upload'),
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

        if (!$cityModel = Cities::model()->findByAttributes(array('link' => $city))) {
            throw new CHttpException(404, 'Страница не найдена');
        }

        $this->processPageRequest('page');

        $this->pageTitle = '';

        if (!empty($this->_type)) {
            $this->pageTitle .= $this->_type->subtitle . ' ';
        }

        $this->pageTitle .= 'жилье в ' . $cityModel->subtitle;

        $this->pageTitle = mb_strtoupper(mb_substr($this->pageTitle, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($this->pageTitle, 1);

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

        $advert = Adverts::model()->with(array('city', 'type_data'))->find($criteria);



        if (!$advert) {
            throw new CHttpException(404, 'Объявление не найдено.');
        }

        $this->_type = $advert->type_data;

        $this->pageTitle = $advert->type_data->subtitle . ' ' . (!empty($advert->action) ? ' ' . $advert->action->subtitle : 'жилье') . ' в ' . $advert->city->subtitle;

        if (!empty($advert->metro->title)) {
            $this->pageTitle .= ' м. ' . $advert->metro->title;
        }

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

        return (object) array(
                    'bootstrap' => $bootstrap,
                    'stylesheet' => $stylesheet,
                    'jquery' => $jquery,
                    'lightbox' => $lightbox,
                    'js' => $js,
        );
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
        $registers = $this->registers();
        $this->clientScript->registerScriptFile($registers->js . '/jquery.ui.widget.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($registers->js . '/jquery.iframe-transport.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($registers->js . '/jquery.fileupload.js', CClientScript::POS_HEAD);

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

            $item->enabled = 1;

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

            if ($item->validate()) {

                $item->save(false);

                $images = Yii::app()->request->getPost('Images');
                $userForm = Yii::app()->cache->get($item->formId);

                if (!empty($userForm['images']) && !empty($images) && is_array($images)) {
                    foreach ($userForm['images'] as $key => $image) {
                        if (in_array($image['id'], $images)) {
                            $imageModel = Images::model()->findByPk($image['id']);
                            if ($imageModel && $imageModel->advert_id == 0 && $imageModel->form_id == $item->formId) {
                                $imageModel->advert_id = $item->id;
                                /**
                                 * @todo Добавить валидацию
                                 */
                                $imageModel->save(false);
                            }
                        } else {
                            unset($userForm['images'][$key]);
                            Yii::app()->cache->set($item->formId, $userForm);
                        }
                    }
                }

                $item->refresh();
                Yii::app()->request->redirect(Yii::app()->createAbsoluteUrl('items/item', array('city' => $item->city->link, 'type' => $item->type_data->link, 'link' => $item->link, 'id' => $item->id)));
                exit();
            }
        } else {
            $item->formId = md5(time() . microtime() . Yii::app()->user->id);
        }

        $userForm = Yii::app()->cache->get($item->formId);
        if (empty($userForm['images'])) {
            $userForm = array('images' => array());
        }
        Yii::app()->cache->set($item->formId, $userForm);

        $this->render('add', array(
            'item' => $item,
            'image' => $imageModel,
            'userForm' => $userForm,
        ));
    }

    public function actionUpload($formId)
    {
        if (!preg_match("/^\w{32}$/isu", $formId)) {
            exit();
        }

        $result = array(
            'files' => array(),
        );

        $model = new Images('upload');
        $model->images = UploadedFile::getInstances($model, 'images');

        $imageIds = array();

        if ($model->validate()) {
            foreach ($model->images as $image) {
                if (empty($image) || empty($image->size) || empty($image->name)) {
                    continue;
                }

                $model = new Images('upload');
                $model->advert_id = 0;
                $model->form_id = $formId;
                $model->name = $image->filename;
                $model->mime_type = $image->type;
                $model->filesize = $image->size;
                $model->extension = $image->extensionName;


                if (!file_exists(Yii::app()->params->imagesStorage . '/' . $image->path) || !is_dir(Yii::app()->params->imagesStorage . '/' . $image->path)) {
                    mkdir(preg_replace("/\/$/isu", '', Yii::app()->params->imagesStorage . '/' . $image->path), 0755, true);
                }

                $image->saveAs(Yii::app()->params->imagesStorage . '/' . $image->path . $image->filename);

                if ($model->validate()) {
                    $model->save(false);
                    $model->refresh();

                    $result['files'][] = array(
                        'size' => $model->filesize,
                        'type' => $model->mime_type,
                        'url' => $model->src,
                        'id' => $model->id,
                    );
                    $imageIds[] = $model->id;
                    $userForm = Yii::app()->cache->get($formId);
                    if (empty($userForm['images'])) {
                        $userForm = array('images' => array());
                    }
                    $userForm['images'] = array_merge($userForm['images'], $result['files']);
                    Yii::app()->cache->set($formId, $userForm);
                }
            }


            echo json_encode($result);
        }
    }

}
