<?php

namespace App\Http\Controllers;

use App\Collection\User;
use Illuminate\Http\Request;
use Exception;
use App\Collection\Lead;
use MongoId;
use Monolog\Handler\Mongo;

class LeadController extends Controller
{
    public function getList(Request $request)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;

            if (!in_array('admin', $this->user['roles'])) {
                $conditions = ['createdBy' => (string) $this->user['_id'], 'deleted' => false];
            } else {
                $conditions = ['companyId' => $this->user['companyId'], 'deleted' => false];
            }

            
            $lead = new Lead();
            $data = iterator_to_array($lead->find($conditions, ['companyId' => false]), false);

            foreach ($data as $index => $item) {
                $data[$index]['_id'] = (string) $item['_id'];
            }

        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }

    public function get(Request $request, $id)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;
            $conditions = ['companyId' => $this->user['companyId'], 'deleted' => false, '_id' => new MongoId($id)];
            $lead = new Lead();
            $data = $lead->findOne($conditions, ['companyId' => false]);

            if (empty($data)) {
                $this->code = 404;
                throw new Exception('notFound');
            }

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

            if (!in_array('admin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            $conditions = ['companyId' => $this->user['companyId'], 'deleted' => false, '_id' => new MongoId($id)];
            $lead = new Lead();
            $item = $lead->findOne($conditions, ['companyId' => false]);

            if (empty($item)) {
                $this->code = 404;
                throw new Exception('notFound');
            }

            unset($item['_id']);
            $data = $request->getContent();
            $data = json_decode($data, true);
            $lead->update($conditions, ['$set' => $data]);
            $data['_id'] = $id;
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

            $lead = new Lead();
            $data = $request->getContent();
            $data = json_decode($data, true);
            $data['companyId'] = $this->user['companyId'];
            $data['createdAt'] = time();
            $data['createdBy'] = (string) $this->user['_id'];
            $data['active'] = true;
            $data['deleted'] = false;

            $lead->insert($data);
            $data['_id'] = (string) $data['_id'];

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

            $lead = new Lead();
            $conditions = ['companyId' => $this->user['companyId'], '_id' => new MongoId($id)];
            $data = $lead->remove($conditions);
        } catch (Exception $e) {
            $this->code = 500;
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }
}
