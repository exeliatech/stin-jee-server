<?php namespace App\Http\Controllers;

use App\Manager;
use App\ManagerBillingLog;
use App\Specials;
use App\Token;
use App\Batch;
use App\Transaction;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;
use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;


class Controller extends BaseController
{

    public function generateId()
    {
        $str = explode(' ', microtime());
        $str[0] = (float)$str[0];
        $fractional = explode('.', $str[0]);
        $str[0] = (int)$fractional[1];
        return Hashids::encode($str);
    }


    public function returnJSON($data = array())
    {
        $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        header('Content-type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length:' . mb_strlen($data));
        echo $data;
        exit;
    }


    public function errorEmail($error, $status, $info = array())
    {
        Mail::send('email.payment_fail', array('info' => $info, 'status' => $status, 'error' => $error), function($message) use ($info) {
            $emails = explode(",", env('EMAILS'));
            $message
                ->to($emails)
                ->from('no-reply@stinjee.com')
                ->sender('no-reply@stinjee.com', 'Stinjee Support')
                ->subject('Sale Stin Jee tokens');
        });
        header('Location: '.url('/').'/#!/payment_error/' . $error);
        exit;
    }


    public function createTransaction($info = array(), $config)
    {
        $batch = Batch::create(array(
            'object_id' => $this->generateId(),
            'promotion' => 0,
            'active' => 1,
            'country' => strtoupper($info['country']),
            'ends_at' => Carbon::now()->addYear(),
            'email' => $info['email'],
            'purchased_online' => 1,
        ));
        if (!$batch->object_id) $this->errorEmail('FAIL, CAN\'T CREATE TOKENS', 'cant create tokens', $info);

        //add free tokens
        if($info['tokensNum'] == 10) $info['tokensNum'] += 2;
        if($info['tokensNum'] == 20) $info['tokensNum'] += 5;

        $tokens = Token::query()->where('country', '=', $batch->country)->where('batch', '=', '')->take($info['tokensNum'])->get();

        if (!$tokens->count() || $tokens->count() != $info['tokensNum']) {
            $this->errorEmail('FAIL, CAN\'T CREATE TOKENS', 'not enough tokens', $info);
        }

        $tokensArray = array();
        foreach ($tokens as $token) {
            $tokensArray[] = $token->object_id;
            $token->batch = $batch->object_id;
            $token->purchase_date = Carbon::now();
            $token->price = round(floatval($info['price']) / intval($info['tokensNum']), 2);
            $token->purchase_online = 1;
            $token->save();

            $logs = ManagerBillingLog::query()->where('manager', '=', $token->manager)->where('month', '=', date('Y-m-00', time()))->get();
            if ($logs->count()) {
                $log = $logs[0];

                $logTokens = mbsplit(',', $log->tokens);
                $logTokens[] = $token->object_id;
                $logTokensUniq = array_unique($logTokens);

                if (count($logTokensUniq) != count($logTokens)) {
                    $log->tokens = explode(',', $logTokens);
                    $log->price += $token->price;
                    $log->save();
                }
            } else {
                //create new bill log
                $manager = Manager::findOrNew($token->manager);
                $log = ManagerBillingLog::create(array(
                    'object_id' => $this->generateId(),
                    'month' => date('Y-m-00', time()),
                    'manager' => $token->manager,
                    'manager_name' => $manager->name,
                    'manager_email' => $manager->email,
                    'country' => $manager->country,
                    'status' => 0,
                    'tokens' => $token->object_id,
                    'price' => $token->price,
                ));
            }
        }

        // write transaction to database
        $transaction = Transaction::create(array(
            'object_id' => $this->generateId(),
            'email' => $info['email'],
            'country' => $info['country_code'],
            'person' => $info['first_name'] . ' ' . $info['last_name'],
            'amount' => $info['price'],
            'batch_id' => $batch->object_id,
            'tokens' => $info['tokensNum'],
            'company' => $info['company'],
            'online' => 1,
            'updated' => 0,

        ));

        $date = date("j F Y");
        $csvName = "Stin Jee Tokens - $date - Batch {$batch->object_id}.csv";
        $csv = array();

        $csv[] = $tokens->count() . ' Stin Jee tokens for creating specials;';
        $csv[] = "Purchased on $date;";
        $csv[] = 'To upload specials, please go to: http://production.stinjee.com;';
        $csv[] = ';';
        $csv[] = 'Batch ID: ; ' . $batch->object_id;
        $csv[] = ';';
        $csv[] = 'Token IDs;';
        $csv[] = ';';

        foreach ($tokens as $token)
            $csv[] = $token->object_id . ';';

        $csv = implode(PHP_EOL, $csv);

        if ($info['country_code'] == 'IT') {
            $view = 'payment_it';
            $subject = 'Conferma aquisto gettoni Stin Jee';
        } else {
            $subject = 'Sale Stin Jee tokens';
            $view = 'payment_en';
        }


        Mail::send('email.'.$view, array('info' => $info, 'transaction' => $transaction), function($message) use ($subject, $info, $config, $csvName, $csv) {

            $message
                ->to($info['email'])
                ->bcc($config['email'])
                ->from('no-reply@stinjee.com')
                ->sender('no-reply@stinjee.com', 'Stinjee Support')
                ->subject($subject)
                ->attachData($csv, $csvName);
        });

        return '/#!/payment_success/' . $batch->object_id . '/' . $transaction->object_id;
    }
}
