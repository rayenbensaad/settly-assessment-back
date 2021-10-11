<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verification_code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verification_code)
    {
        $this->verification_code = $verification_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.verify')->with([
            'verification_code' => $this->verification_code
        ])->subject('Please verify your email address');
    }
}
