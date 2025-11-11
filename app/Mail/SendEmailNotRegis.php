<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEmailNotRegis extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $count;

    public function __construct($NotRegis, $Count)
    {
        $this->data = $NotRegis;
        $this->count = $Count;
    }

    public function build()
    {
        return $this->subject('Operator Line Up Log')->view('SendEmailNotRegis');
    }

}
