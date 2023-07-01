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

    public $code;
    public $via;
    public $settings;

    public function __construct($code, $via, $settings)
    {
        $this->code     = $code;
        $this->via      = $via;
        $this->settings = $settings;
    }

    public function via($notifiable)
    {
        return $this->via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->view('vendor/ssofy/otp/email', [
            'code'     => $this->code,
            'settings' => $this->settings,
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
                'brand' => $this->brand,
                'code'  => $this->code,
            ])
        );
    }
}
