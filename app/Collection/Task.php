<?php
namespace App\Collection;

use App\Adapter\Mongo;

class Task extends Mongo {

    public function __construct()
    {
        $this->collection = "tasks";
        parent::__construct();
    }
}