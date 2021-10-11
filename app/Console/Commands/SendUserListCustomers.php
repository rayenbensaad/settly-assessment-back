<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\ListCustomersInThread;
use App\Mail\SendListCustomers;
use Illuminate\Support\Facades\Mail;

class SendUserListCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:listcustomers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the user an email of their customers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         
        $users = User::with('customers')->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new SendListCustomers($user->customers));

        }
         
        $this->info('Successfully sent weekly list to every admin.');
    }
}
