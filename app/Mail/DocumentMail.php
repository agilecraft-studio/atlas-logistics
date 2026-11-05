<?php

namespace App\Mail;

use App\Model\AtlasLogistics;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $customerName,
        public string $documentLabel,
        public string $documentNumber,
        public ?string $intro = null,
        public ?string $attachmentData = null,
        public ?string $attachmentName = null,
        public ?string $attachmentMime = null,
    ) {
    }

    public function build()
    {
        $from = AtlasLogistics::get('email') ?: config('mail.from.address');

        $mail = $this->subject($this->mailSubject)->markdown('emails.document');
        if ($from) {
            $mail->from($from);
        }
        if ($this->attachmentData !== null && $this->attachmentName !== null) {
            $mail->attachData($this->attachmentData, $this->attachmentName, [
                'mime' => $this->attachmentMime ?: 'application/pdf',
            ]);
        }

        return $mail;
    }
}
