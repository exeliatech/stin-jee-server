<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() use ($app) {
    return view('index');
});

//Auth
$app->get('private/login', function () {
    return view('login', ['error' => '']);
});

$app->post('private/login','AuthController@login');

$app->get('/private', ['middleware' => 'auth', function () {
    return view('private');
}]);

//Mobile API

$app->post('/api/get_specials_by_longlat','ApiController@getSpecialsByLonglat');
$app->post('/api/get_specials_info','ApiController@getSpecialsInfo');
$app->post('/api/subscribe','ApiController@subscribe');

//Frontend API

//Specials
$app->get('/api/specials/list/{param}','FrontendApiSpecialController@getSpecials');
$app->get('/api/specials/list/','FrontendApiSpecialController@getSpecials');
$app->get('/api/specials/f/{id}','FrontendApiSpecialController@getSpecial');
$app->get('/api/specials/{id}', 'FrontendApiSpecialController@getSpecial');
$app->post('/api/specials/import', 'FrontendApiSpecialController@importSpecials');

$app->post('/api/frontend/review_special','FrontendApiSpecialController@reviewSpecial'); // and update
$app->get('/api/specials/decline/{id}','FrontendApiSpecialController@declineSpecial');


$app->post('/api/frontend/reserve_tokens','FrontendApiController@reserveTokens');

//Managers
$app->get('/api/managers/list','FrontendApiManagerController@getManagersList');
$app->get('/api/manager/{id}','FrontendApiManagerController@getManager');
$app->post('/api/frontend/manager_create','FrontendApiManagerController@createManager');
$app->post('/api/frontend/manager_update','FrontendApiManagerController@updateManager');
$app->get('/api/manager/delete/{id}','FrontendApiManagerController@deleteManager');

//Transactions
$app->get('/api/invoices/list', 'FrontendApiTransactionController@getInvoicesList');
$app->post('/api/frontend/get_transactions', 'FrontendApiTransactionController@getTransactions');
$app->get('/api/invoice_report/{month}/{manager}', 'FrontendApiTransactionController@invoiceReport');
$app->get('/api/frontend/info_transactions', 'FrontendApiTransactionController@transactionsExport');

$app->get('/api/batch/list', 'FrontendApiController@getBatchList');
$app->post('/api/batch', 'FrontendApiController@generateTokens');
$app->post('/api/batch/email', 'FrontendApiController@sendEmailBatches'); //after generateTokens
$app->get('/api/batch/deactivate/{batch}', 'FrontendApiController@deactivateBatch');
$app->get('/api/batch/activate/{batch}', 'FrontendApiController@activateBatch');
$app->get('/csv/tokens/{batch}', 'FrontendApiController@downloadTokens');
$app->post('/api/pay', 'FrontendApiController@pay');
$app->get('/api/pay_success', 'FrontendApiController@paySuccess');
$app->get('/api/pay_error', 'FrontendApiController@payError');

$app->post('/api/pay_info', 'FrontendApiController@payInfo');
$app->post('/api/update_transaction', 'FrontendApiController@updateTransaction');
$app->post('/api/pay', 'FrontendApiController@pay');
$app->get('/push', 'FrontendApiController@push');
//public part
$app->get('/api/batch/{id}', 'FrontendApiSpecialController@getBatchSpecials');
$app->get('/api/batch/specials/{type}/{id}', 'FrontendApiSpecialController@getBatchSpecialsByType');
$app->get('/api/is_valid/{batch}/{token}/', 'FrontendApiController@checkToken');

$app->post('/api/fontend/add_special', 'FrontendApiSpecialController@addSpecial');
