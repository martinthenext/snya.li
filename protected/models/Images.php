<?php

class Images extends CActiveRecord
{

    const MAX_FILES = 10;
    const IMAGE_BIG = array(1024, 1024);
    const IMAGE_THUMB = array(100, 100);

    public $images;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{images}}";
    }

    public function rules()
    {
        return array(
            array('images', 'file',
                'types' => 'jpg, png',
                'allowEmpty' => true,
                'on' => 'upload',
            ),
            array('name', 'unique',
                'allowEmpty' => false,
                'attributeName' => 'name',
                'caseSensitive' => false,
                'className' => 'Images',
                'on' => 'upload',
            ),
            array('name', 'unique',
                'criteria' => array(
                    'condition' => 't.advert_id = :advert_id',
                    'params' => array('advert_id' => $this->advert_id),
                ),
                'allowEmpty' => false,
                'attributeName' => 'name',
                'caseSensitive' => false,
                'className' => 'Images',
                'on' => 'import',
            ),
            array('created', 'default', 'value' => time()),
        );
    }

    public function attributeLabels()
    {
        return array(
            'images[]' => 'Фотографии',
            'images' => 'Фотографии',
        );
    }

    public function getSrc()
    {

        return '//snya.li/images/' . mb_substr($this->name, 0, 2) . '/' . mb_substr($this->name, 2, 2) . '/' . $this->name;
    }

    public function getThumb()
    {
        include_once 'WideImage/WideImage.php';
        $name = preg_replace("/^(\w+)\.\w+/isu", "$1", $this->name);
        $thumbSize = self::IMAGE_THUMB;
        $thumbName = $name . '_' . $thumbSize[0] . 'x' . $thumbSize[1] . '.' . $this->extension;
        $path = Yii::app()->params['imagesStorage'] . '/' . mb_substr($this->name, 0, 2) . '/' . mb_substr($this->name, 2, 2) . '/';

        if (!file_exists($path . $thumbName)) {
            $wideImage = WideImage::load($path . $this->name);
            $watermark = WideImage::load(Yii::app()->params['webRoot'] . '/images/logo.png');

            $thumbSize = Images::IMAGE_THUMB;

            $resized = $wideImage->resize($thumbSize[0], $thumbSize[1], 'fill');
            $resized->saveToFile($path . $thumbName);
        }

        return '//snya.li/images/' . mb_substr($this->name, 0, 2) . '/' . mb_substr($this->name, 2, 2) . '/' . $thumbName;
    }

}
