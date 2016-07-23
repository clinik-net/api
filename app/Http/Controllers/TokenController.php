<?php

namespace App\Http\Controllers;

use App\Collection\User;
use Illuminate\Http\Request;
use Exception;

class TokenController extends Controller
{
    public function getList(Request $request)
    {
        $code = 200;
        $data = [];
        try {
            $error = false;
            $authorizationHeader = $request->header('Authorization');
            if ($authorizationHeader === false || $authorizationHeader == null || $authorizationHeader == '') {
                $code = 400;
                throw new Exception('authMissing', 1);
            }

            $authorizationHeader = base64_decode(str_replace('Basic ', '', $authorizationHeader));
            if (!preg_match('/[^\:]*\:.*/i', $authorizationHeader)) {
                $code = 400;
                throw new Exception('invalidAuthHeader', 1);
            }
            $parts = explode(':', $authorizationHeader);

            $userModel = new User();
            $conditions = ['email' => $parts[0], 'password' => $parts[1], 'active' => true, 'deleted' => false];
            $data = $userModel->findOne($conditions, ['password' => 0, 'active' => 0, 'deleted' => 0]);

            if (empty($data))
            {
                $code = 401;
                throw new Exception('invalidUsername', 1);
            }

            $data['_id'] = (string) $data['_id'];
        } catch (Exception $e) {
            $error = true;
        }

        return response()->json(['error' => $error, 'code' => $code, 'data' => $data]);
    }
}
