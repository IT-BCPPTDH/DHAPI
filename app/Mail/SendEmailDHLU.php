<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEmailDHLU extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $data2;

    public function __construct($cekLog, $result)
    {
        $this->data = $cekLog;
        $this->data2 = $result;
    }

    public function build()
    {
        return $this->subject('Operator Line Up Log')->view('SendEmailDHLU');
    }
}
