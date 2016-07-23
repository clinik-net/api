<?php
namespace App\Collection;

use App\Adapter\Mongo;

class Company extends Mongo {

    public function __construct()
    {
        $this->collection = "companies";
        parent::__construct();
    }
}