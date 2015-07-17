<?php

class Users extends CActiveRecord
{

    const ROLE_ADMIN = 'admin';
    const ROLE_MODER = 'moderator';
    const ROLE_USER = 'user';
    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(
            array('name, service, service_id', 'required'),
            array('role', 'default', 'value'=>self::ROLE_USER),
            array('service', 'default', 'value'=>'self'),
            array('disabled', 'default', 'value'=>0),
            array('last_login', 'default', 'value'=>new CDbExpression('now()')),
        );
    }
    
    public function tableName()
    {
        return "{{users}}";
    }
    
    public function updateLastLogin()
    {
        $this->last_login = new CDbExpression('now()');
        $this->save(false);
    }

}
