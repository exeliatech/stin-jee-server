<?php namespace App\Console;

use App\Manager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Specials;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {

            //get today expired specials
            $beginOfDay = strtotime("midnight", time());
            $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

            $specials = Specials::query()
                ->where('ends_at', '>', date("Y-m-d H:i:s", $beginOfDay))
                ->where('ends_at', '<', date("Y-m-d H:i:s", $endOfDay))
                ->where('active', '=', 1)->get();

            if(!$specials->count()){
                Mail::send('email.noexpired', array(), function($message) {
                    $message
                        ->to(array('paolo@stinjee.com'))
                        ->from('no-reply@stinjee.com')
                        ->subject('No specials expiring today');
                });
                return false;
            }

            foreach($specials as $special){
                $managerMails = array('paolo@stinjee.com');

                //getting manager emails
                $managers =  Manager::query()->where('country', '=', $special->country_code)->get();

                if($managers->count()){
                    foreach($managers as $manager){
                        $managerMails[] = $manager->email;
                    }
                }
                $managerMails = array_unique($managerMails);

                Mail::send('email.expired', ['special' => $special], function($message) use ($managerMails, $special) {
                    $message
                        ->to($managerMails)
                        ->from('no-reply@stinjee.com')
                        ->subject("Special expiring today: $special->country_code, " . $special->object_id);
                });
            }

        })->daily();
    }
}
