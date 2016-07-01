<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use PHPExcel;
use PHPExcel_IOFactory;
use Log;
use App\ManagerBillingLog;
use App\Transaction;
use Validator;
use App\Manager;
use App\Token;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Intervention\Image\ImageManagerStatic as Image;

class FrontendApiTransactionController extends Controller
{


    //get all transactions
    public function getInvoicesList()
    {

        $months_array = array();
        $date = Carbon::now()->addMonths(-30);
        $now = Carbon::now()->addMonth();
        while ($date < $now) {
            $months_array[] = $date->yearIso . '-' . ($date->month);
            $date->addMonth();
        }

        $managers_array = array();
        $managers = Manager::query()->get();
        if ($managers->count()) {
            foreach ($managers as $manager) {
                $managers_array[] = array(
                    'id' => $manager->object_id,
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'country' => $manager->country,
                );
            }
        }
        $countries = array(
            'RU' => 'Russian Federation',
            'IT' => 'Italy',
            'HR' => 'Croatia',
            'CY' => 'Cyprus',
        );
        $logs = ManagerBillingLog::query()->where('created_at', '>', Carbon::now()->addMonths(-24))->orderBy('month', 'desc')->get();
        if ($logs->count()) {
            foreach ($logs as $log) {
                $month = substr($log->month, 0, -3);

                $log->tokens = mbsplit(',', $log->tokens);
                $log->price = floatval($log->price);
                $log->month = substr($month, 0, -2) . (int)substr($month, -2);
                $log->country_name = $countries[$log->country];
                $log->manager = array("__type" => "Pointer", "className" => "Manager", "objectId" => $log->manager);
                $log->objectId = $log->object_id;
            }
        }

        return response()->json(array('managers' => $managers_array, 'months' => $months_array, 'countries' => $countries, 'items' => $logs));
    }


    public function getTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required',
            'manager_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }


        $start = date('Y-m-d', strtotime($request->input('month') . '-00'));
        $s = strtotime($start);
        $end = date('Y-m-d', strtotime("+1 month", $s));

        $tokens = Token::query()->where('manager', '=', $request->input('manager_id'))->where('purchase_date', '>', $start)->where('purchase_date', '<', $end)->take(1000)->get();

        $batches = array();

        if ($tokens->count()) {
            foreach ($tokens as $t) {
                $batches[] = $t->batch;
            }
        }

        $transactions = Transaction::query()->whereIn('batch_id', $batches)->orderBy('created_at', 'desc')->take(1000)->get();
        $tr = array();
        if ($transactions->count()) {

            foreach ($transactions as $t) {
                $t_ = array();
                $amount = 0;
                foreach ($tokens as $token) {
                    if ($token->batch == $t->batch_id) {
                        $t_[$token->object_id] = $token->price;
                        $amount += $token->price;
                    }
                }

                $tr[] = array(
                    'id' => $t->object_id,
                    'batch_id' => $t->batch_id,
                    'date' => date('Y-m-d H:i', strtotime($t->created_at)),
                    'buyer' => array(
                        'email' => $t->email,
                        'name' => $t->person
                    ),
                    'general' => array(
                        'amount' => floatval($t->amount),
                        'tokens' => $t->tokens
                    ),
                    'local' => array(
                        'amount' => $amount,
                        'tokens' => count($t_)
                    ),
                    'tokens' => $t_,
                    'online' => $t->online
                );
            }
        }
        $this->returnJSON(array('transactions' => $tr, 'success' => true));
    }


    //====Download invoice ========//


    public function invoiceReport(Request $request, $month, $manager)
    {
        $log = ManagerBillingLog::query()->where('month', '=', $month . '-00')->where('manager', '=', $manager)->get();

        header("Content-Type", "text/csv");
        $statuses = ['Owing', 'Invoiced', 'Paid'];
        $tokens = array();
        if ($log->count()) {
            $log = $log[0];
            header('Content-Disposition: attachment; filename="Stin Jee Report - ' . $month . ' - ' . $log->manager_name . '.csv";');
            $tokens[] = 'To upload specials, please go to: http://client.stinjee.com;';
            $tokens[] = ';';
            $tokens[] = 'Manager name: ' . $log->manager_name . ";";
            $tokens[] = 'Manager email: ' . $log->manager_email . ";";
            $tokens[] = ';';

            $tokens[] = 'Invoice month: ' . $month . ";";
            $tokens[] = 'Invoice status: ' . $statuses[$log->status] . ";";
            $tokens[] = ';';
            $tokens[] = 'Price: ' . $log->price . ";";
            $tokens[] = ';';
            $tokens[] = 'Token IDs;';
            $tokens[] = ';';
            $tokens[] = join("; \r\n", mbsplit(',', $log->tokens)) . ";";
        } else {
            header('Content-Disposition: attachment; filename="Stin Jee Report - ' . $month . '.csv";');
            $tokens[] = 'To upload specials, please go to: http://client.stinjee.com;';
            $tokens[] = ';';
            $tokens[] = 'Invoice month: ' . $month . ";";
            $tokens[] = ';';
            $tokens[] = 'There are no information for specified month and manager;';
        }
        $data = join("\r\n", $tokens);
        echo $data;
        exit();
    }


    public function transactionsExport(Request $request)
    {
        require_once '../phpexcel/PHPExcel.php';

        $phpexcel = new PHPExcel();
        $page = $phpexcel->setActiveSheetIndex(0);
        $page->setCellValue("A1", "Date");
        $page->setCellValue("B1", "Transaction Id");
        $page->setCellValue("C1", "Batch Id");
        $page->setCellValue("D1", "Buyer email");
        $page->setCellValue("E1", "Bayer name");
        $page->setCellValue("E1", "How sold");
        //$page->setCellValue("F1", "Manager tokens");
        $page->setCellValue("F1", "Tokens num");
        //$page->setCellValue("H1", "Manager amount");
        $page->setCellValue("G1", "Amount");
        $page->setCellValue("H1", "Tokens");
        $page->setTitle("Transactions");


        $start = strtotime($request->get('month') . "-1 00:00");
        $end = strtotime("+1 month", $start);
        $type = array();

        if ($request->get('type') === '0' || $request->get('type') === '1') {
            $type[] = (int)$request->get('type');
        } else {
            $type[] = 0;
            $type[] = 1;
        }

        $tokens = Token::query()->where('country', '=', $request->get('country'))->where('purchase_date', '>', date("Y-m-d H:i:s", $start))->where('purchase_date', '<', date("Y-m-d H:i:s", $end))->get();


        $batches = array();
        $total_amount = 0;
        $tokens_num = 0;
        if ($tokens->count()) {
            foreach ($tokens as $t) {
                $batches[] = $t->batch;
            }

            $transactions = Transaction::query()->whereIn('batch_id', $batches)->whereIn('online', $type)->orderBy('created_at', 'desc')->get();
            $tr = array();


            if ($transactions->count()) {
                foreach ($transactions as $t) {

                    $t_ = array();
                    $amount = 0;
                    foreach ($tokens as $token) {
                        if ($token->batch == $t->batch_id) {
                            $t_[$token->object_id] = $token->price;
                            $amount += $token->price;
                            $total_amount += $token->price;
                            $tokens_num++;
                        }
                    }

                    $tr[] = array(
                        'id' => $t->object_id,
                        'batch_id' => $t->batch_id,
                        'date' => date('Y-m-d H:i', strtotime($t->created_at)),
                        'buyer' => array(
                            'email' => $t->email,
                            'name' => $t->person
                        ),
                        'general' => array(
                            'amount' => floatval($t->amount),
                            'tokens' => $t->tokens
                        ),
                        'local' => array(
                            'amount' => $amount,
                            'tokens' => count($t_)
                        ),
                        'tokens' => $t_,
                        'online' => $t->online
                    );
                }

                foreach ($tr as $n => $i) {
                    $page->setCellValue("A" . ($n + 2), $i['date']);
                    $page->setCellValue("B" . ($n + 2), $i['id']);
                    $page->setCellValue("C" . ($n + 2), $i['batch_id']);
                    $page->setCellValue("D" . ($n + 2), $i['buyer']['email']);
                    $page->setCellValue("E" . ($n + 2), $i['buyer']['name']);
                    $page->setCellValue("E" . ($n + 2), $i['online'] == true ? 'Online' : 'Offline');
                    //$page->setCellValue("F" . ($n + 2), $i['local']['tokens']);
                    $page->setCellValue("F" . ($n + 2), $i['general']['tokens']);
                    //$page->setCellValue("H" . ($n + 2), $i['local']['amount']);
                    $page->setCellValue("G" . ($n + 2), $i['general']['amount']);
                    $page->setCellValue("H" . ($n + 2), implode(', ', array_keys($i['tokens'])));
                }

                $n = $n + 4;
                $page->setCellValue("A" . $n, "Country totals:");
                $page->setCellValue("A" . ($n + 1), "Amount");
                $page->setCellValue("B" . ($n + 1), "Tokens");
                $page->setCellValue("C" . ($n + 1), "Batches");


                $page->setCellValue("A" . ($n + 2), round($total_amount, 2));
                $page->setCellValue("B" . ($n + 2), $tokens_num);
                $page->setCellValue("C" . ($n + 2), count($transactions));

            } else {
                $page->setCellValue("B" . 4, 'Transactions not found');
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');

        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');
        // It will be called file.xls
        header('Content-Disposition: attachment; filename="transactions-' . $_GET['month'] . '-' . $_GET['country'] . '.xls"');
        // Write file to the browser
        $objWriter->save('php://output');
        exit;
    }

}