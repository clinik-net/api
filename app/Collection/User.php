<?php
namespace App\Collection;

use App\Adapter\Mongo;

class User extends Mongo {

    public function __construct()
    {
        $this->collection = "users";
        parent::__construct();
    }
}