<?php
namespace App\Collection;

use App\Adapter\Mongo;

class TaskType extends Mongo {

    public function __construct()
    {
        $this->collection = "taskType";
        parent::__construct();
    }
}