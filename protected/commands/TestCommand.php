<?php

class TestCommand extends CConsoleCommand
{
    
    public function beforeAction($action, $params)
    {

        $_SERVER = array(
            'HTTP_HOST' => 'snya.li',
            'SERVER_NAME' => 'snya.li',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SCRIPT_FILENAME' => '/index.php',
            'SCRIPT_NAME' => 'index.php',
            'HTTPS' => 'on',
        );

        Yii::app()->urlManager->baseUrl = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'];

        date_default_timezone_set("Europe/Moscow");
        return parent::beforeAction($action, $params);
    }
    
    public function actionRedirects()
    {
        $filename = '/var/www/snya.li/redirects.txt';

        $criteria = new CDbCriteria();
        $criteria->condition = 't.enabled';
        $adverts = Adverts::model()->with(array('city'=>array('joinType'=>'inner join')))->findAll($criteria);
        
        $replaces = array(
            'snimu'=>'sdat-v-arendu',
            'sdam-v-arendu'=>'snyat',
            'kuplju'=>'prodat',
            'prodam'=>'kupit',
        );

        $replaces = array_flip($replaces);
        var_dump($replaces);
        foreach ($adverts as $data) {
            $source_url = Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $replaces[$data->type_data->link], 'link' => $data->link, 'id' => $data->id));
            $destination_url = Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id));
            
            $source_url = str_replace('https://snya.li', '', $source_url);
            $destination_url = str_replace('https://snya.li', '', $destination_url);
            
            file_put_contents($filename, $source_url.'#'.$destination_url.PHP_EOL, FILE_APPEND);
        }
    }

}
