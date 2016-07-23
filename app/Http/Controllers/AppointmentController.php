<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Collection\Appointment;
use App\Collection\Task;
use App\Collection\Lead;
use MongoId;
use DateTime;
use DateTimeZone;
use Monolog\Handler\Mongo;

class AppointmentController extends Controller
{
    public function getList(Request $request)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;

            $lead = new Lead();
            $task = new Task();
            $conditions = ['companyId' => $this->user['companyId'], 'deleted' => false, 'type.name' => 'appointment'];
            $data = iterator_to_array($task->find($conditions, ['companyId' => false, 'deleted' => false])->sort(['createdAt' => -1]), false);

            foreach ($data as $index => $item) {
                //$mexico = new DateTimeZone('UTC');
                $start = new DateTime($item['date']);
                $end = new DateTime($item['end']);
                //$start->setTimezone($mexico);
                //$end->setTimezone($mexico);
                $data[$index]['_id'] = (string)$item['_id'];
                $data[$index]['date'] = $start;
                $data[$index]['end'] = $end;
                $data[$index]['lead'] = $lead->findOne(['_id' => new MongoId($item['leadId'])], ['companyId' => false, '_id' => false, 'active' => false, 'deleted' => false]);
            }

            usort($data, function($a, $b){
                if ($a['createdAt'] < $b['createdAt']) return true;
            });

        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }

    public function create(Request $request)
    {
        $this->code = 201;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;
            $task = new Task();
            $data = $request->getContent();
            $data = json_decode($data, true);
            $data['companyId'] = $this->user['companyId'];
            $data['createdAt'] = time();
            $data['createdBy'] = (string) $this->user['_id'];
            $data['userId'] = (string) $this->user['_id'];
            $data['active'] = true;
            $data['deleted'] = false;

            $task->insert($data);
            $data['_id'] = (string) $data['_id'];

        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }

    public function update(Request $request, $id)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;
            $task = new Task();
            $data = $request->getContent();
            $data = json_decode($data, true);
            unset($data['_id']);
            $task->update(['_id' => new MongoId($id)], ['$set' => $data]);
        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }

    public function remove(Request $request, $id)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;

            $task = new Task();
            $conditions = ['companyId' => $this->user['companyId'], '_id' => new MongoId($id)];
            $data = $task->remove($conditions);
        } catch (Exception $e) {
            $this->code = 500;
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }
}
