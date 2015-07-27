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
        $adverts = Adverts::model()->with(array('city' => array('joinType' => 'inner join')))->findAll($criteria);

        $replaces = array(
            'snimu' => 'sdat-v-arendu',
            'sdam-v-arendu' => 'snyat',
            'kuplju' => 'prodat',
            'prodam' => 'kupit',
        );

        $replaces = array_flip($replaces);
        var_dump($replaces);
        foreach ($adverts as $data) {
            $source_url = Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $replaces[$data->type_data->link], 'link' => $data->link, 'id' => $data->id));
            $destination_url = Yii::app()->createAbsoluteUrl('items/item', array('city' => $data->city->link, 'type' => $data->type_data->link, 'link' => $data->link, 'id' => $data->id));

            $source_url = str_replace('https://snya.li', '', $source_url);
            $destination_url = str_replace('https://snya.li', '', $destination_url);

            file_put_contents($filename, $source_url . '#' . $destination_url . PHP_EOL, FILE_APPEND);
        }
    }

    public function actionImages()
    {
        include 'WideImage/WideImage.php';

        $attachments = Attachments::model()->findAll();

        foreach ($attachments as $attachment) {
            $src = $attachment->getSrc_lightbox();


            $filename = tempnam(sys_get_temp_dir(), 'attach-tmp-download');
            $handle = fopen($filename, 'w');

            $ch = curl_init($src);
            curl_setopt($ch, CURLOPT_FILE, $handle);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);

            fclose($handle);

            $mime = mime_content_type($filename);

            if (preg_match("/^image\/(?P<extension>\w+)$/isu", $mime, $matches)) {
                $md5File = md5_file($filename);

                $path = Yii::app()->params['imagesStorage'] . '/' . mb_substr($md5File, 0, 2) . '/' . mb_substr($md5File, 2, 2);

                echo $path.PHP_EOL;
                
                if (!file_exists($path) || !is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                rename($filename, $path . '/' . $md5File . '.' . $matches['extension']);
                chmod($path . '/' . $md5File . '.' . $matches['extension'], 0777);

                try {
                    $wideImage = WideImage::load($path . '/' . $md5File . '.' . $matches['extension']);
                    $watermark = WideImage::load(Yii::app()->params['webRoot'] . '/images/logo.png');
                    $wideImage = $wideImage->merge($watermark, "right", "bottom");
                    $wideImage->saveToFile($path . '/' . $md5File . '.' . $matches['extension']);

                    $thumbSize = Images::IMAGE_THUMB;

                    $resized = $wideImage->resize($thumbSize[0], $thumbSize[1], 'fill');
                    $resized->saveToFile($path . '/' . $md5File . '_' . $thumbSize[0] . 'x' . $thumbSize[0] . '.' . $matches['extension']);
                } catch (Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }

                $model = new Images('import');
                $model->advert_id = $attachment->advert_id;
                $model->name = $md5File . '.' . $matches['extension'];
                $model->extension = $matches['extension'];
                $model->mime_type = $mime;
                $model->filesize = filesize($path . '/' . $md5File . '.' . $matches['extension']);
                if ($model->validate()) {
                    $model->save(false);
                }
            }

            @unlink($filename);
        }
    }

}
