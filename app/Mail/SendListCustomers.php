<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendListCustomers extends Mailable
{
    use Queueable, SerializesModels;

    public $customers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mailslist-customers')->with([
            'customers' => $this->customers
        ])->subject('List of Customers');
    }
}
