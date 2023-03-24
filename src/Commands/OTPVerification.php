<?php

namespace SSOfy\Laravel\Commands;

use Illuminate\Console\Command;
use SSOfy\Laravel\OTP;

class OTPVerification extends Command
{
    protected $signature = 'ssofy:otp-verify {token}';

    protected $description = 'Verify OTP Token';

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
        $userId = $this->otp->verify($this->argument('token'));

        if (false === $userId) {
            $this->error('Invalid Token');
            return -1;
        }

        $this->table([], [
            ['Status', 'Valid'],
            ['User Id', $userId],
        ]);
    }
}
