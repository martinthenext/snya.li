<?php

class UserController extends Controller
{

    public $imagesAsset;

    public function actionLogin()
    {
        $this->registers();
        $serviceName = Yii::app()->request->getQuery('service');
        if (isset($serviceName)) {
            /** @var $eauth EAuthServiceBase */
            $eauth = Yii::app()->eauth->getIdentity($serviceName);
            $eauth->redirectUrl = Yii::app()->user->returnUrl;
            $eauth->cancelUrl = $this->createAbsoluteUrl('user/login');

            try {
                if ($eauth->authenticate()) {
                    //var_dump($eauth->getIsAuthenticated(), $eauth->getAttributes());
                    $identity = new EAuthUserIdentity($eauth);

                    // successful authentication
                    if ($identity->authenticate()) {

                        $criteria = new CDbCriteria();
                        $criteria->condition = 't.vk_user_id = :vk_user_id';
                        $criteria->params = array('vk_user_id' => $identity->id);
                        $exists = UsersBlacklist::model()->count($criteria);
                        
                        if ($exists < 1) {
                            Yii::app()->user->login($identity);
                            
                            $criteria = new CDbCriteria();
                            $criteria->condition = 't.vk_user_id = :vk_user_id';
                            $criteria->params = array('vk_user_id' => $identity->id);
                            if (Administrators::model()->count($criteria)) {
                                Yii::app()->user->setState('admin', true);
                            }
                            $eauth->redirect();
                        } else {
                            $eauth->cancel();
                        }
                        //var_dump($identity->id, $identity->name, Yii::app()->user->id);exit;
                    } else {
                        // close popup window and redirect to cancelUrl
                        $eauth->cancel();
                    }
                }

                // Something went wrong, redirect to login page
                $this->redirect(array('user/login'));
            } catch (EAuthException $e) {
                // save authentication error to session
                Yii::app()->user->setFlash('error', 'EAuthException: ' . $e->getMessage());

                // close popup window and redirect to cancelUrl
                $eauth->redirect($eauth->getCancelUrl());
            }
        }
        $this->render('login');
        // default authorization code through login/password ..
    }

    public function actionLogout()
    {
        Yii::app()->user->logout();
        Yii::app()->request->redirect(Yii::app()->user->returnUrl);
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
    }

    
    public function actionTest()
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 'match(:query)';
        $criteria->params = array('query'=>'продам');
        $results = Sphinx::model()->findAll($criteria);
        var_dump($results);
    }
}
