<?php

namespace App\Http\Controllers;

use App\Collection\User;
use Illuminate\Http\Request;
use Exception;
use App\Collection\TaskType;
use MongoId;
use Monolog\Handler\Mongo;

class TaskTypeController extends Controller
{
    public function getList(Request $request)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;
            $conditions = ['name' => ['$nin' => ['appointment']]];
            $public = $request->input('public', null);

            if (!empty($public)) {
                $conditions['public'] = filter_var($public, FILTER_VALIDATE_BOOLEAN);
            }

            $taskType = new TaskType();
            $data = iterator_to_array($taskType->find($conditions), false);

            foreach ($data as $index => $item) {
                $data[$index]['_id'] = (string)$item['_id'];
            }

        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }
}