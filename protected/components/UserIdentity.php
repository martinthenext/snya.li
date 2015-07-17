<?php

class UserIdentity extends EAuthUserIdentity
{
    protected $model;

    public function getService()
    {
        return $this->servive;
    }

    public function getModel()
    {
        return $this->model;
    }
    
    public function authenticate()
    {
        $result = parent::authenticate();
        
        if ($result === true) {
            $criteria = new CDbCriteria();
            $criteria->condition = 't.service = :service and t.service_id = :service_id';
            $criteria->params = array(
                'service' => $this->service->serviceName,
                'service_id' => (int) $this->id,
            );

            if ($this->model = Users::model()->find($criteria)) {
                $this->model->updateLastLogin();
            } else {
                $this->model = new Users();
                $this->model->service = $this->service->serviceName;
                $this->model->service_id = $this->id;
                $this->model->name = $this->name;
                $this->model->save(true);
            }
            
            $this->id = $this->model->id;
            $this->setState('id', $this->id);
        }

        return $result;
    }
}
