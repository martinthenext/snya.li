<?php

class Sphinx extends CModel
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function getDbConnection()
    {
        return Yii::app()->sphinx;
    }

    public function escape($string)
    {
        $from = array('\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '=', "'", "\x00", "\n", "\r", "\x1a");
        $to = array('\\\\', '\\\(', '\\\)', '\\\|', '\\\-', '\\\!', '\\\@', '\\\~', '\\\"', '\\\&', '\\\/', '\\\^', '\\\$', '\\\=', "\\'", "\\x00", "\\n", "\\r", "\\x1a");
        return str_replace($from, $to, $string);
    }

    public function search($query, $params = array())
    {
        $query = "SELECT * FROM adverts WHERE MATCH('{$query}') and enabled = 1";
        
        if (!empty($params['city_id'])) {
            $query .= ' and city_id = '.$params['city_id'];
        } 
        
        if (!empty($params['type'])) {
            $query .= ' and type = '.$params['type'];
        } 
        
        $command = $this->getDbConnection()->createCommand($query);
        $result = $command->queryAll(true);
        $result = array_map(function($value){
            return (int) $value['id'];
        }, $result);
        return $result;
    }

    public function count($query, $params = array())
    {
        $query = "SELECT count(*) FROM adverts WHERE MATCH('{$query}') and enabled = 1";
        if (!empty($params['city_id'])) {
            $query .= ' and city_id = '.$params['city_id'];
        } 
        
        if (!empty($params['type'])) {
            $query .= ' and type = '.$params['type'];
        } 
        
        $command = $this->getDbConnection()->createCommand($query);
        return $command->queryScalar();
    }
    
    public function attributeNames()
    {
        
    }

}
