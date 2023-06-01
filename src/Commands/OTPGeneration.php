<?php

namespace SSOfy\Laravel\Commands;

use Illuminate\Console\Command;
use SSOfy\Laravel\OTP;

class OTPGeneration extends Command
{
    protected $signature = 'ssofy:otp-gen {user}';

    protected $description = 'Generate OTP Token';

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
        $type = $this->choice('Token type?', [
            1 => 'characters',
            2 => 'numbers'
        ], 1);

        $ttl = intval($this->ask('TTL (seconds)', 60 * 60));
        $ttl = $ttl <= 0 ? 60 * 60 : $ttl;

        $len = intval($this->ask('Length', 32));
        $len = $len <= 0 ? 32 : $len;

        if ('numbers' === $type) {
            $token = $this->otp->randomDigitsOTP($this->argument('user'), $ttl, $len);
        } else {
            $token = $this->otp->randomStringOTP($this->argument('user'), $ttl, $len);
        }

        $this->table([], [
            ['Token', $token],
            ['TTL', $ttl],
            ['Length', $len],
        ]);
    }
}
