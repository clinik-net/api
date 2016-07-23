<?php
namespace App\Collection;

use App\Adapter\Mongo;

class Appointment extends Mongo {

    public function __construct()
    {
        $this->collection = "tasks";
        parent::__construct();
    }
}