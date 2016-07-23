<?php

namespace App\Http\Controllers;

use App\Collection\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Exception;

class Controller extends BaseController
{
    protected function authenticate(Request $request, $throwException = true) {
        $authorizationHeader = $request->header('Authorization');
        if ($authorizationHeader === false || $authorizationHeader == null || $authorizationHeader == '') {
            if ($throwException) {
                $this->code = 400;
                throw new Exception('authMissing', 1);    
            } else {
                return false;
            }
        }

        $authorizationHeader = base64_decode(str_replace('Basic ', '', $authorizationHeader));
        if (!preg_match('/[^\:]*\:.*/i', $authorizationHeader)) {
            $this->code = 400;
            throw new Exception('invalidAuthHeader', 1);
        }

        $parts = explode(':', $authorizationHeader);
        $userModel = new User();
        $conditions = ['email' => $parts[0], 'token' => $parts[1], 'active' => true, 'deleted' => false];
        $this->user = $userModel->findOne($conditions, ['password' => 0, 'active' => 0, 'deleted' => 0]);

        if (empty($this->user))
        {
            $this->code = 401;
            throw new Exception('invalidUsername', 1);
        }
    }

    protected $code = 200;
    protected $user;
}
