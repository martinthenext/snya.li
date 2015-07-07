<?php
class Cities extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return "{{cities}}";
    }
    
    public function relations()
    {
        return array(
            'adverts'=>array(self::HAS_MANY, 'Adverts', 'city_id'),
        );
    }

    public function rules()
    {
        return array(
            array('title, link', 'length', 'min'=>2, 'max'=>200, 'allowEmpty'=>'false'),
            array('title, link', 'unique'),
            array('area, region', 'default', 'value'=>''),
            array('vk_city_id', 'required', 'on'=>'update_vk'),
        );
    }
    
    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->setAttribute('link', Yii::app()->urlManager->translitUrl($this->title));
        }
        return parent::beforeValidate();
    }
}

