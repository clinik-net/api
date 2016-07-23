<?php
namespace App\Collection;

use App\Adapter\Mongo;

class Lead extends Mongo {

    public function __construct()
    {
        $this->collection = "leads";
        parent::__construct();
    }
}