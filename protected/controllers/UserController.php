<?php

class UserController extends Controller
{

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

                    $identity = new UserIdentity($eauth);

                    if ($identity->authenticate()) {

                        Yii::app()->user->login($identity);

                        $eauth->redirect();

                        //var_dump($identity->id, $identity->name, $identity->service, Yii::app()->user->id);exit;
                    } else {
                        $eauth->cancel();
                    }
                }

                $this->redirect(array('user/login'));
            } catch (EAuthException $e) {
                Yii::app()->user->setFlash('error', 'EAuthException: ' . $e->getMessage());
                $eauth->redirect($eauth->getCancelUrl());
            }
        }
        $this->render('login');
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

        $this->clientScript->registerCssFile($bootstrap . '/css/bootstrap.min.css');
        $this->clientScript->registerCssFile($lightbox . '/bootstrap-media-lightbox.css');
        $this->clientScript->registerCssFile($stylesheet . '/layout.css');

        $this->clientScript->registerScriptFile($jquery . '/jquery.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($bootstrap . '/js/bootstrap.min.js', CClientScript::POS_HEAD);
        $this->clientScript->registerScriptFile($lightbox . '/bootstrap-media-lightbox.min.js', CClientScript::POS_HEAD);
    }
}
