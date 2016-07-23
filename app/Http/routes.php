<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$app->get('/', function () use ($app) {
    return response()->json(['api' => 'Clinik.net API']);
});

$app->get('/token', 'TokenController@getList');
$app->get('/me', 'UserController@current');
$app->get('/company', 'CompanyController@getList');
$app->get('/company/{id}', 'CompanyController@get');
$app->put('/company/{id}', 'CompanyController@update');
$app->delete('/company/{id}', 'CompanyController@remove');
$app->post('/company', 'CompanyController@create');
$app->get('/company/{id}/users', 'CompanyController@getUsers');
$app->get('/lead', 'LeadController@getList');
$app->get('/lead/{id}', 'LeadController@get');
$app->put('/lead/{id}', 'LeadController@update');
$app->post('/lead', 'LeadController@create');
$app->delete('/lead/{id}', 'LeadController@remove');
$app->get('/lead/{id}/task', 'Lead\TaskController@getList');
$app->get('/lead/{id}/appointment', 'Lead\AppointmentController@getList');
$app->get('/task-type', 'TaskTypeController@getList');
$app->get('/task', 'TaskController@getList');
$app->post('/task', 'TaskController@create');
$app->post('/appointment', 'AppointmentController@create');
$app->get('/appointment', 'AppointmentController@getList');
$app->put('/appointment/{id}', 'AppointmentController@update');
$app->post('/user', 'UserController@create');
$app->delete('/user/{id}', 'UserController@remove');
$app->get('/user', 'UserController@getList');
$app->get('/user/{id}', 'UserController@get');
$app->put('/user/{id}', 'UserController@update');
/*
$app->post('/task-list/{id}/task', 'TodoTaskController@create');
$app->get('/task-list', 'TaskListController@getList');
$app->post('/task-list', 'TaskListController@create');
$app->delete('/task-list/{id}', 'TaskListController@remove');
$app->get('/task-list/{id}/task', 'TodoTaskController@getList');
$app->put('/todo-task/{id}', 'TodoTaskController@update');
$app->delete('/todo-task/{id}', 'TodoTaskController@remove');



$app->post('/location', 'LocationController@create');
$app->get('/location', 'LocationController@getList');
$app->get('/location/user', 'LocationController@getUserList');
$app->get('/project', 'ProjectController@getList');
$app->post('/project', 'ProjectController@create');
$app->get('/dashboard', 'DashboardController@getList');*/