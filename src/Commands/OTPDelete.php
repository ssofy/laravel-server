<?php

namespace SSOfy\Laravel\Commands;

use Illuminate\Console\Command;
use SSOfy\Laravel\OTP;

class OTPDelete extends Command
{
    protected $signature = 'ssofy:otp-delete {token}';

    protected $description = 'Delete OTP Token';

    /**
     * @var OTP
     */
    private $otp;

    public function __construct(OTP $otp)
    {
        parent::__construct();

        $this->otp = $otp;
    }

    public function handle()
    {
        $this->otp->forget($this->argument('token'));

        $this->info('Token deleted successfully.');
    }
}
