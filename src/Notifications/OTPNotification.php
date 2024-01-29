<?php

namespace SSOfy\Laravel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OTPNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $option;

    public $vars;

    public $code;

    public $channels;

    public function __construct($option, $vars, $code, $channels)
    {
        $this->option   = $option;
        $this->vars     = $vars;
        $this->code     = $code;
        $this->channels = $channels;
    }

    public function via($notifiable)
    {
        return $this->channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verification Code')
            ->view('vendor/ssofy/otp/email', [
                'code'     => $this->code,
                'option'   => $this->option,
                'vars'     => $this->vars,
                'settings' => $this->vars,
            ]);
    }

    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)->content($this->getSMSMessage());
    }

    public function toVonage($notifiable)
    {
        return (new VonageMessage)->content($this->getSMSMessage());
    }

    protected function getSMSMessage()
    {
        return trim(
            view('vendor/ssofy/otp/sms', [
                'code'     => $this->code,
                'option'   => $this->option,
                'vars'     => $this->vars,
                'settings' => $this->vars,
            ])
        );
    }
}
