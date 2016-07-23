<?php

namespace App\Http\Controllers;

use App\Collection\User;
use Illuminate\Http\Request;
use Exception;
use App\Collection\Company;
use MongoId;
use Monolog\Handler\Mongo;
use Mailgun\Mailgun;

class CompanyController extends Controller
{
    public function getList(Request $request)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;

            if (!in_array('superadmin', $this->user['roles']) && !in_array('admin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            if (in_array('superadmin', $this->user['roles'])) {
                $conditions = ['deleted' => false];
            } else {
                $conditions = ['administrators' => ['$in' => [(string)$this->user['_id']]], 'deleted' => false];
            }

            $company = new Company();
            $user = new User();
            $data = iterator_to_array($company->find($conditions, ['adminId' => false]), false);

            foreach ($data as $index => $item) {
                $data[$index]['_id'] = (string)$item['_id'];
                $data[$index]['users'] = $user->count(['companyId' => (string)$item['_id']]);
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

            if (!in_array('superadmin', $this->user['roles']) && !in_array('admin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            if (in_array('superadmin', $this->user['roles'])) {
                $conditions = ['deleted' => false, '_id' => new MongoId($id)];
            } else {
                $conditions = ['administrators' => ['$in' => [(string)$this->user['_id']]], 'deleted' => false, '_id' => new MongoId($id)];
            }

            $company = new Company();
            $user = new User();
            $data = $company->findOne($conditions, ['adminId' => false]);
            $data['_id'] = (string)$data['_id'];
            $data['users'] = $user->count(['companyId' => (string)$data['_id']]);
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
        $error = false;
        try {
            $this->authenticate($request);

            if (!in_array('superadmin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            $data = $request->getContent();
            $data = json_decode($data, true);

            $user = new User();
            $admin = $user->findOne(['email' => $data['admin']['email']]);

            if (!empty($admin)) {
                $this->code = 409;
                throw new Exception('duplicatedUser');
            }

            $plainPassword = uniqid();
            $data['admin']['password'] = sha1($plainPassword);
            $data['admin']['active'] = true;
            $data['admin']['deleted'] = false;
            $data['admin']['createdBy'] = $this->user['_id'];
            $data['admin']['createdAt'] = time();
            $data['admin']['token'] = sha1(time() . $data['admin']['email'] . rand(0,10));
            $data['admin']['roles'] = ['admin'];

            $user->insert($data['admin']);
            $data['administrators'] = [(string) $data['admin']['_id']];
            $admin = $data['admin'];
            unset($data['admin']);

            $company = new Company();
            $data['createdAt'] = time();
            $data['createdBy'] = (string) $this->user['_id'];
            $data['active'] = true;
            $data['deleted'] = false;
            $data['usersNumber'] = 2;

            $isUnique = false;
            do {
                $data['code'] = uniqid();
                $validation = $company->findOne(['code' => $data['code']]);
                if (empty($validation)) $isUnique = true;
            } while(!$isUnique);

            $company->insert($data);
            $data['_id'] = (string) $data['_id'];
            $user->update(['_id' => $admin['_id']], ['$set' => ['companyId' => (string) $data['_id']]]);

            //Creates the admin
            $client = new \Http\Adapter\Guzzle6\Client();
            $mg = new Mailgun(env('MAILGUN_KEY'), $client);
            $domain = env('MAILGUN_DOMAIN');
            $html = "
            <h2>Ahora eres administrador de {$data['name']}</h2>

            <p>Puedes empezar a usar el sistema inmediatamente, a continuación te mandamos tus datos de acceso, recuerda cambiar tu contraseña al entrar a la plataforma por una que puedas recordar.</p>
            <table>
                <tr>
                    <th style='text-align: left'>Usuario:</th>
                    <td>{$admin['email']}</td>
                </tr>
                <tr>
                    <th style='text-align: left;'>Contraseña:</th>
                    <td>{$plainPassword}</td>
                </tr>
            </table>

            <p>Recuerda que si tienes cualquier dificultad siempre puedes usar el menu de ayuda o escribirnos directamente a " . env('INFO_MAIL') . " donde con gusto te responderemos lo antes posible.</p>
            ";
            $mg->sendMessage($domain, array('from' => env('MAILGUN_FROM'),
                'to'      => $admin['email'],
                'subject' => 'Bienvenido a ' . env('SYSTEM_NAME'),
                'html'    => $html));
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
        $error = false;
        try {
            $this->authenticate($request);

            if (!in_array('superadmin', $this->user['roles']) && !in_array('admin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            if (in_array('superadmin', $this->user['roles'])) {
                $conditions = ['_id' => new MongoId($id)];
            } else {
                $conditions = ['_id' => new MongoId($id), 'administrators' => ['$in' => [(string)$this->user['_id']]]];
            }

            $data = $request->getContent();
            $data = json_decode($data, true);

            $company = new Company();
            unset($data['_id']);
            unset($data['createdAt']);
            unset($data['createdBy']);
            $company->update($conditions, ['$set' => $data]);
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
        $error = false;
        try {
            $this->authenticate($request);

            if (!in_array('superadmin', $this->user['roles']) && !in_array('admin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            if (in_array('superadmin', $this->user['roles'])) {
                $conditions = ['_id' => new MongoId($id)];
            } else {
                $conditions = ['_id' => new MongoId($id), 'administrators' => ['$in' => [(string)$this->user['_id']]]];
            }

            $user = new User();
            $user->update(['companyId' => $id], ['$set' => ['deleted' => true, 'active' => false]], ['multi' => true]);
            $company = new Company();
            $company->update(['_id' => new MongoId($id)], ['$set' => ['deleted' => true, 'active' => false]]);
        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }

    public function getUsers(Request $request, $id)
    {
        $this->code = 200;
        $data = [];
        try {
            $this->authenticate($request);
            $error = false;

            if (!in_array('superadmin', $this->user['roles']) && !in_array('admin', $this->user['roles'])) {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            $company = new Company();
            $infoCompany = $company->findOne(['_id' => new MongoId($id), 'deleted' => false]);

            if(empty($infoCompany)) {
                $this->code = 404;
                throw new Exception('noCompanyFound');
            }

            if (in_array('superadmin', $this->user['roles'])) {
                $conditions = ['companyId' => $id, 'deleted' => false];
            } elseif(in_array($this->user['_id'], $infoCompany['administrators'])) {
                $conditions = ['companyId' => $id, 'deleted' => false];
            } else {
                $this->code = 401;
                throw new Exception('forbidden');
            }

            $user = new User();
            $data = iterator_to_array($user->find($conditions), false);

            foreach ($data as $index => $item) {
                $data[$index]['_id'] = (string)$item['_id'];
                $data[$index]['createdBy'] = $user->findOne(['_id' => $item['createdBy']], ['email' => true, 'name' => true, '_id' => false]);
            }

        } catch (Exception $e) {
            $error = true;
            $data = $e->getMessage();
        }

        return response()->json(['error' => $error, 'code' => $this->code, 'data' => $data], $this->code);
    }
}