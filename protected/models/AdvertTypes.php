<?php
class AdvertTypes extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return "{{advert_types}}";
    }

}
