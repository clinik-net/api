<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Collection\Lead;
use App\Collection\Task;
use MongoId;
use DateTime;
use Monolog\Handler\Mongo;

class AppointmentController extends Controller
{
    public function getList(Request $request, $id)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;

            $lead = new Lead();
            $conditions = ['companyId' => $this->user['companyId'], 'deleted' => false, '_id' => new MongoId($id)];
            $leadItem = $lead->findOne($conditions);

            if (empty($leadItem)) {
                $this->code = 404;
                throw new Exception('leadNotFound');
            }

            $task = new Task();
            $conditions = ['companyId' => $this->user['companyId'], 'deleted' => false, 'leadId' => $id];
            $data = iterator_to_array($task->find($conditions, ['companyId' => false, 'leadId' => false, 'deleted' => false])->sort(['createdAt' => -1]), false);

            foreach ($data as $index => $item) {
                $data[$index]['_id'] = (string)$item['_id'];
                $data[$index]['date'] = new DateTime($item['date']);
                $data[$index]['end'] = new DateTime($item['end']);
            }

            usort($data, function($a, $b){
                if ($a['date'] < $b['date']) return true;
            });

        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }
}