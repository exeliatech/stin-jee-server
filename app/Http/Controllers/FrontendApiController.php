<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use PHPExcel;
use PHPExcel_IOFactory;
use Log;
use App\ManagerBillingLog;
use App\Transaction;
use Validator;
use App\Batch;
use App\Device;
use App\Manager;
use App\Token;
use App\Http\Controllers\Controller;
use App\Specials;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Intervention\Image\ImageManagerStatic as Image;
use WindowsPhonePushNotification;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

class FrontendApiController extends Controller{

    // = = = = = = = = = = = = = = = = = = = MANAGERS = = = = = = = = = = = = = = = = = = = = = = //

    public function reserveTokens(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'tokens' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $manager = Manager::findOrNew($request->input('id'));

        for($i = 0; $i < $request->input('tokens'); $i++){
            Token::create(array(
                'object_id' => $this->generateId(),
                'country' => $manager->country,
                'email' => $manager->email,
                'ends_at' => Carbon::now()->addYear(),
                'manager' => $manager->object_id,
                ));
        }

        return response()->json(array('status' => 'success', 'tokens_length' => $request->input('tokens')));
    }


    public function downloadTokens(Request $request, $batch)
    {
        $batch = Batch::findOrNew($batch);

        if(!$batch->object_id){
            echo 'Batch not found!';
            exit;
        }

        $tokens = Token::query()->where('batch', '=', $batch->object_id)->get();
        if(!$tokens->count()){
            echo 'Tokens not found!';
            exit;
        }
        $data = array();
        header('Pragma: public');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Stin Jee Tokens - ' . date('j M Y') . ' - Batch '. $batch->object_id .'.csv"');

        $data[] = $tokens->count() . ' Stin Jee tokens for creating specials;';
        $data[] = ($request->input('purchased') ? 'Purchased' : 'Generated') . ' on ' . date('j F Y');
        $data[] = 'To upload specials, please go to: http://client.stinjee.com;';
        $data[] = ';';
        $data[] = 'Batch ID: ;'. $batch->object_id;
        $data[] = ';';
        $data[] = 'Token IDs;';
        $data[] = ';';
        foreach($tokens as $token){
            $data[] = $token->object_id.';';
        }
        $data = join("\r\n", $data);
        echo $data;
        exit();
    }



    // = = = = = = = = = = = = = = = = = = = Batch = = = = = = = = = = = = = = = = = = = = = = //

    public function generateTokens(Request $request)
    {

        //Detect manager
        $manager = Manager::findOrNew($request->input('manager'));

        if(!$manager->object_id){
            return response()->json(array('error' => 'manager not found', 'status' => 'error', 'success' => false));
        }

        //Check price
        $price = (($request->input('promotion') == 'false') ? $request->input('price') : 0);
        $token_price = 0.0;
        if($price > 0){
            $token_price = $price / $request->input('tokensNum');
        }

        //Create new banch if not found
        if($request->input('batch_id')){
            $batch = Batch::findOrNew($request->input('batch_id'));
        } else {
            $batch = Batch::create(array(
                'object_id' => $this->generateId(),
                'promotion' => (($request->input('promotion')) ? 1 : 0),
                'active' => 1,
                'country' => $manager->country,
                'ends_at' => Carbon::now()->addYear(),
                'email' => 'support@stinjee.com',
                'purchased_online' => 0,
            ));
        }

        if(!$batch->object_id){
            return response()->json(array('error' => 'batch not found', 'status' => 'error', 'success' => false));
        }


        //Generate tokens
        $tokens = array();
        for ($i = 0; $i < $request->input('tokensNum'); $i++) {
            $token = Token::create(array(
                'object_id' => $this->generateId(),
                'batch' => $batch->object_id,
                'country' => $manager->country,
                'ends_at' => Carbon::now()->addYear(),
                'email' => 'support@stinjee.com',
                'manager' => $manager->object_id,
                'price' => $token_price,
                'purchase_date' => Carbon::now(),
                'purchase_online' => 0,
            ));
            $tokens[] = $token->object_id;
        }

        //Add tokens to ManagerBillingLog
        $tokens = Token::query()->whereIn('object_id', $tokens)->get();
        if($tokens->count()){

            $log = ManagerBillingLog::query()->where('month', '=', date('Y-m-00'))->where('manager', '=', $manager->object_id)->get();
            if(!$log->count()){
                //Create new ManagerBillingLog if not exists
                $log = ManagerBillingLog::create(array(
                    'object_id' => $this->generateId(),
                    'month' => date('Y-m-00', time()),
                    'manager' => $manager->object_id,
                    'manager_name' => $manager->name,
                    'manager_email' => $manager->email,
                    'country' => $manager->country,
                    'status' => 0,
                ));
                $t_array = array();
            } else {
                $log = $log[0];
                $t_array = mbsplit(',', $log->tokens);
            }

            //Add tokens to ManagerBillingLog
            foreach ($tokens as $token) {
                $token->purchase_online = 1;
                $token->save();
                if (array_search($token->object_id, $t_array) === false) {
                    $t_array[] = $token->object_id;
                    $log->price = $log->price + $token->price;
                }
            }

            $log->tokens = join(', ', $t_array);
            $log->save();

            //Generate new invoice
            Transaction::create(array(
                'object_id' => $this->generateId(),
                'email' => 'support@stinjee.com',
                'country' => $manager->country,
                'person' => 'StinJee',
                'amount' => $price,
                'batch_id' => $batch->object_id,
                'tokens' => $request->input('tokensNum'),
                'online' => 0,
                'updated' => 1,

            ));
            $this->returnJSON(array('batchId' => $batch->object_id, 'results' => $request->input('tokensNum')));
            exit;
        }

        return response()->json(array('error' => 'error', 'status' => 'error', 'success' => false));
    }


    //TODO: add email
    public function sendEmailBatches(Request $request)
    {
        $data = '';
        foreach($request->input('batches') as $batch){
            $data .= '- http://client.stinjee.com/csv/tokens/' . $batch . '<br/>';
        }

        $this->returnJSON(array('success' => true));
        exit();
    }


    public function deactivateBatch($batch)
    {
        $batch = Batch::findOrNew($batch);

        if(!$batch->object_id){
            return response()->json(array('error' => 'batch not found', 'status' => 'error', 'success' => false));
        }

        $batch->active = 0;
        $batch->save();

        return response()->json(array('success' => true));
    }

    public function activateBatch($batch)
    {
        $batch = Batch::findOrNew($batch);

        if(!$batch->object_id){
            return response()->json(array('error' => 'batch not found', 'status' => 'error', 'success' => false));
        }

        $batch->active = 1;
        $batch->save();

        return response()->json(array('success' => true));
    }


    public function getBatchList(Request $request)
    {
        $data = array();
        $limit = ($request->input('limit')) ? $request->input('limit') : 20;
        $offset = ($request->input('offset')) ? $request->input('offset') : 0;
        $batch = Batch::query()->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();

        $batches_array = array();
        if($batch->count()){
            foreach($batch as $row){

                //tokens
                $tokens = Token::query()->where('batch', '=', $row->object_id)->get();
                $tokens_array = array();
                if($tokens->count()){
                    foreach($tokens as $token){
                        $tokens_array[] = $token->object_id;
                    }
                }

                //specials
                $specials = Specials::query()->where('batch', '=', $row->object_id)->where('status', '!=', 2)->get();
                $specials_array = array();
                if($specials->count()){
                    foreach($specials as $special){
                        $specials_array[] = array(
                            'id' => $special->object_id,
                            'tokenId' => $special->token,
                            'active' => ($special->active ? true : false),
                            'name' => $special->name,
                            'website' => $special->website,
                            'store' => $special->store,
                            'createdAt' => date('c', strtotime($special->created_at)),
                        );
                    }
                }

                $batches_array[] = array(
                    'id' => $row->object_id,
                    'active' => ($row->active ? true : false),
                    'tokens' => $tokens_array,
                    'specials' => $specials_array,
                    'createdAt' => date('c', strtotime($row->created_at)),
                );
            }
        }

        return response()->json($batches_array);
    }



    public function checkToken($batch, $token)
    {

        $batch = Batch::findOrNew($batch);
        if (!$batch->object_id || !$batch->active) {
            response()->json(array('success' => false, 'status' => 'error', 'error' => 'batch not found'));
        }

        $token = Token::findOrNew($token);
        if (!$token->object_id) {
            response()->json(array('success' => false, 'status' => 'error', 'error' => 'token lookap failed'));
        }

        $specials = Specials::query()->where('status', '!=', 2)->where('token', '=', $token->object_id)->get();
        if($specials->count()){
            response()->json(array('success' => false, 'status' => 'error', 'error' => 'special lookap failed'));
        }

        $data = json_encode(array('success' => true));
        header('Content-type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length:' . mb_strlen($data));
        echo $data;
        exit;

    }



    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tokens_num' => 'required|numeric',
            'country' => 'required',
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }
        if($request->input('country') != 'it') return response()->json(array('status'=> 'error', 'message' => 'incorrect_country'));
        $tokens_n = array(
            '5' => '36.60',
            '10' => '73.20',
            '20' => '146.40',
        );
        $tokens = Token::query()->where('country', '=', $request->input('country'))->where('batch', '=', '')->get();

        if(!$tokens->count() || $tokens->count() < $request->input('tokens_num')){
            return response()->json(array('error'=> 'no_available_tokens'));
        }

        if (env('APP_ENV') != 'production') {
            $config = config('paypal.sandbox');
        } else {
            $config = config('paypal.express');
        }

        $price = $tokens_n[$request->input('tokens_num')];
        $postFields = http_build_query(array(
            'USER' => $config['user'],
            'EMAIL' => $_POST['email'],
            'PWD' => $config['password'],
            'SIGNATURE' => $config['signature'],
            'VERSION' => $config['version'],
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_CUSTOM' => serialize(array('email' => $_POST['email'], 'country' => strtoupper($_POST['country']))),
            'PAYMENTREQUEST_0_AMT' => $price,
            'SOLUTIONTYPE' => 'Sole',
            'LOCALECODE' => 'us',
            'LANDINGPAGE' => 'Billing',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_ITEMAMT' => $price,
            'L_PAYMENTREQUEST_0_QTY0' => $_POST['tokens_num'],
            'L_PAYMENTREQUEST_0_AMT0' => floatval($price) / intval($_POST['tokens_num']),
            'L_PAYMENTREQUEST_0_NAME0' => 'StinJee Token',
            'L_PAYMENTREQUEST_0_NUMBER0' => 0,

            'RETURNURL' => url('/').'/api/pay_success',
            'CANCELURL' => url('/').'/api/pay_error',
            'METHOD' => 'SetExpressCheckout'
        ));

        $ch = curl_init('https://' . $config['endpoint'] . '/nvp');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        curl_close($ch);
        parse_str($res, $data);
        $data['pay_link'] = 'https://' . $config['host'] . '/cgi-bin/webscr?cmd=_express-checkout&token=' . $data['TOKEN'];
        $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        header('Content-type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length:' . mb_strlen($data));
        echo $data;
        exit;
    }


    public function payError()
    {
        return redirect('/#!/payment_error/payment_canceled');
    }


    public function paySuccess(Request $request)
    {

        if (env('APP_ENV') == 'production') {
            $config = config('paypal.express');
        } else {
            $config = config('paypal.sandbox');
        }

        $payerID = $request->get('PayerID');

        $postFields = http_build_query(array(
            'USER' => $config['user'],
            'PWD' => $config['password'],
            'SIGNATURE' => $config['signature'],
            'VERSION' => $config['version'],
            'TOKEN' => $request->get('token'),
            'METHOD' => 'GetExpressCheckoutDetails'
        ));

            $ch = curl_init('https://' . $config['endpoint'] . '/nvp');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close($ch);
            //-----------------
            parse_str($res, $data);
            if (isset($data['ACK']) && $data['ACK'] == 'Success') {

                $data['CUSTOM'] = unserialize(stripslashes($data['CUSTOM']));

                // payer info
                $first_name = $data['FIRSTNAME'];
                $last_name = $data['LASTNAME'];
                $country_code = $data['COUNTRYCODE'];
                $email = $data['CUSTOM']['email'];
                $country = $data['CUSTOM']['country'];
                $company = (isset($data['BUSINESS']) ? $data['BUSINESS'] : "");

                // proccess transaction
                $postFields = http_build_query(array(
                    'USER' => $config['user'],
                    'PWD' => $config['password'],
                    'SIGNATURE' => $config['signature'],
                    'VERSION' => $config['version'],
                    'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                    'PAYMENTREQUEST_0_AMT' => $data['PAYMENTREQUEST_0_AMT'],

                    'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
                    'PAYMENTREQUEST_0_ITEMAMT' => $data['PAYMENTREQUEST_0_ITEMAMT'],
                    'L_PAYMENTREQUEST_0_QTY0' => $data['L_PAYMENTREQUEST_0_QTY0'],
                    'L_PAYMENTREQUEST_0_AMT0' => $data['L_PAYMENTREQUEST_0_AMT0'],
                    'L_PAYMENTREQUEST_0_NAME0' => 'StinJee Token',
                    'L_PAYMENTREQUEST_0_NUMBER0' => 0,

                    'PAYERID' => $payerID,
                    'TOKEN' => $request->get('token'),
                    'METHOD' => 'DoExpressCheckoutPayment'
                ));

                $price = $data['PAYMENTREQUEST_0_AMT'];
                $tokensNum = $data['L_PAYMENTREQUEST_0_QTY0'];

                $ch = curl_init('https://' . $config['endpoint'] . '/nvp');
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $res = curl_exec($ch);
                curl_close($ch);
                parse_str($res, $data);

                if (isset($data['ACK']) && $data['ACK'] == 'Success') {
                    $url = $this->createTransaction(array(
                        'email' => $email,
                        'country' => $country,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'PayerID' => $payerID,
                        'tokensNum' => $tokensNum,
                        'price' => $price,
                        'company' => $company,
                        'country_code' => $country_code
                    ), $config);

                    return redirect($url);

                } else {
                    $this->errorEmail('FAIL, CAN\'T PROCCESS PAYMENT', 'cant proccess payment');
                }
            } else {
                $this->errorEmail('FAIL, CAN\'T PROCCESS PAYMENT', 'cant get payer info');
            }

            $this->errorEmail('FAIL, CAN\'T PROCCESS PAYMENT', 'unknown error');
    }

    public function payInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'batch_id' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $transaction = Transaction::findOrNew($request->input('transaction_id'));
        if(!$transaction->object_id || $transaction->batch_id != $request->input('batch_id')){
            return response()->json(array('error' => 'batch not found', 'status' => 'error'));
        }

        $batch = Batch::findOrNew($request->input('batch_id'));
        if(!$batch->object_id){
            return response()->json(array('error' => 'batch not found', 'status' => 'error', 'success' => false));
        }

        $data = array(
            'id' => $batch->object_id,
            'createdAt' => $batch->created_at,
            'specials' => array(),
            'tokens' => array(),
        );

        $specials  = Specials::query()->where('batch', '=', $batch->object_id)->get();
        if($specials->count()){
            foreach ($specials as $special) {
                if (empty($special->image) || strpos($special->image,'http://') !== false) {
                    $image = $special->image;
                } else {
                    $image = url('/').'/img/'.$special->image;
                }

                $data['specials'][] = array(
                    'id' => $special->object_id,
                    'status' => $special->status,
                    'name' => $special->name,
                    'createdAt' => date('c', strtotime($special->created_at)),
                    'smallImage' => $image,
                );
            }
        }

        $tokens = Token::query()->where('batch', '=', $batch->object_id)->get();
        if($tokens->count()){
            foreach($tokens as $token){
                $data['tokens'][] = $token->object_id;
            }
        }
        $transaction->updated = $transaction->updated ? true : false;
         return response()->json(array('status'=> 'success', 'batch' => $data, 'transaction' => $transaction));

    }

    public function updateTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'batch_id' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $transaction = Transaction::findOrNew($request->input('transaction_id'));
        if(!$transaction->object_id || $transaction->batch_id != $request->input('batch_id')){
            return response()->json(array('error' => 'batch not found', 'status' => 'error'));
        }

        $batch = Batch::findOrNew($request->input('batch_id'));
        if(!$batch->object_id){
            return response()->json(array('error' => 'batch not found', 'status' => 'error', 'success' => false));
        }

        $transaction->company = $request->input('company');
        $transaction->store = $request->input('store');
        $transaction->email = $request->input('email');
        $transaction->address = $request->input('address');
        $transaction->city = $request->input('city');
        $transaction->province = $request->input('province');
        $transaction->postal_id = $request->input('postal_id');
        $transaction->fiscal_id = $request->input('fiscal_id');
        $transaction->updated = 1;

        $transaction->save();
        
        if (env('APP_ENV') != 'production') {
            $config = config('paypal.sandbox');
        } else {
            $config = config('paypal.express');
        }
        // send email to administration
        Mail::send('email.invoice', ['transaction' => $transaction], function($message) use ($transaction, $config) {
            $message
                ->to($config['email'])
                ->from('no-reply@stinjee.com')
                ->subject("New invoice on StinJee");
        });

        return response()->json(array('transaction_id' => $transaction->object_id, 'status' => 'success'));
    }


    public function push(Request $request)
    {
        // Example: /push.php?key=stin_jee_2015&type=android&token=TOKEN&text=Test_message

        if ($request->get('key') != 'stin_jee_2015') die('no access');

        $text = (!$request->get('text')) ? 'Test push message' : $request->get('text');
        if (!$request->get('token')) return false;

        if ($request->get('type') == 'android') {
            //Android app
            PushNotification::app(config('push.android'))
                ->to($request->get('token'))
                ->send($text);
        } else if ($request->get('type') == 'ios') {
            //iOS app
            $token = strtolower($request->get('token'));
            PushNotification::app(config('push.ios'))
                ->to($token)
                ->send($text);
        } else if ($request->get('type') == 'wp') {
            //WP
            include base_path('/windowspush').'/windowsphonepush.php';
            $notify = new WindowsPhonePushNotification($request->get('token'));
            return $notify->push_toast($text, "");
        }
    }

}