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
                'types' => 'jpg, gif, png',
                'allowEmpty' => true,
                'maxFiles' => self::MAX_FILES,
                'tooMany' => 'Вы пытаетесь загрузить слишком много файлов. Загрузить можно не более ' . self::MAX_FILES . '.',
                'on' => 'add',
            ),
            array('created', 'default', 'value' => time()),
        );
    }

    public function beforeSave()
    {
        if ($this->scenario == 'add' && is_array($this->images)) {
            foreach ($this->images as $image) {
                if (empty($image) || empty($image->size)) {
                    continue;
                }
                $model = new Images('save');
                $model->advert_id = $this->advert_id;
                $model->name = $image->filename;
                $model->mime_type = $image->type;
                $model->filesize = $image->size;
                $model->extension = $image->extensionName;


                if (!file_exists(Yii::app()->params->imagesStorage . '/' . $image->path) || !is_dir(Yii::app()->params->imagesStorage . '/' . $image->path)) {
                    mkdir(preg_replace("/\/$/isu", '', Yii::app()->params->imagesStorage . '/' . $image->path), 0755, true);
                }

                if ($model->save()) {
                    $image->saveAs(Yii::app()->params->imagesStorage . '/' . $image->path . $image->filename);
                }

                
            }
            return false;
        }

        return parent::beforeSave();
    }

    public function beforeValidate()
    {
        return parent::beforeValidate();
    }

    public function afterValidate()
    {
        return parent::afterValidate();
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
        Yii::app()->request->hostInfo = '//snya.li';
        return Yii::app()->request->hostInfo . '/images/' . mb_substr($this->name, 0, 2) . '/' . mb_substr($this->name, 2, 2) . '/' . $this->name;
    }

}
