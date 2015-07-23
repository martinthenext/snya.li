<?php

class Redirect extends CBaseUrlRule
{
    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;

    public function createUrl($manager, $route, $params, $ampersand)
    {
        return false;
    }

    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        
        $search = preg_quote('/'.$pathInfo, '/');
        
        $source = file_get_contents('/var/www/snya.li/redirects.txt');
        
        if (preg_match('/'.$search.'#(?P<to>[^\n]*)/isu', $source, $matches)) {
            Yii::app()->request->redirect($matches['to'], true, self::MOVED_PERMANENTLY);
        }
        
        return false;  
    }
}
