<?php

namespace App\Repositories;

class AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * get current model
     * @return $model
     */
    protected function getModel()
    {
        return app($this->model);
    }
    
    /**
     * get data by column
     * @param $columnName
     * @param $columnData
     * @return object
     */
    public function getByColumn($columnName, $columnData)
    {
        $data = $this->model->where($columnName, $columnData)->first();
        return $data;
    }

}