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

class TaskController extends Controller
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

            $startDate = $request->input('startDate', null);
            if (!empty($startDate)) {
                if ($startDate === 'now') {
                    $now = new DateTime();
                    $conditions['$or'] = [
                        ['date' => ['$gte' => $now->format(DateTime::ISO8601)]]
                    ];
                }
            }

            $endDate = $request->input('endDate', null);
            if (!empty($endDate)) {
                if ($endDate === 'now') {
                    $now = new DateTime();
                    //$conditions['end'] = ['$or' => [['$lte' => $now->format(DateTime::ISO8601)], ['status' => 'done']]];
                    $conditions['$or'] = [
                        ['date' => ['$lte' => $now->format(DateTime::ISO8601)]]
                    ];
                }
            }

            $data = iterator_to_array($task->find($conditions, ['companyId' => false, 'leadId' => false, 'deleted' => false])->sort(['createdAt' => -1, 'createdAt.sec' => -1]), false);

            foreach ($data as $index => $item) {
                $data[$index]['_id'] = (string)$item['_id'];
                if (!is_int($item['createdAt'])) {
                    $data[$index]['createdAt'] = $item['createdAt']->sec;
                }

                if ($item['type']['name'] === 'appointment') {
                    $data[$index]['date'] = new DateTime($item['date']);
                    $data[$index]['end'] = new DateTime($item['end']);
                }
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
}