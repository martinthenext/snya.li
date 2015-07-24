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

}
