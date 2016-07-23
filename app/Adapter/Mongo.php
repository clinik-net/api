<?php
/**
 * Mongo.php
 *
 * PHP version 5
 *
 * @category Api
 * @package  Adapter
 * @author   Osvaldo Garcia <osvaldo@kadoo.mx>
 */
namespace App\Adapter;

use MongoCollection;
use MongoClient;
use MongoId;

/**
 * The Mongo class, this is the Database class
 *
 * @category Api
 * @package  Adapter
 * @author   Osvaldo Garcia <osvaldo@kadoo.mx>
 **/
abstract class Mongo extends MongoCollection
{

    /**
     * The constructor
     */
    public function __construct()
    {
        try {
            $client = new MongoClient(env('DBURL'));
            $this->link_id = $client->selectDb(env('DBNAME'));
        } catch (MongoConnectionException $e) {
            error_log($e->getMessage());
        }

        parent::__construct($this->link_id, $this->collection);
    }

    protected $link_id;
    protected $collection;
}