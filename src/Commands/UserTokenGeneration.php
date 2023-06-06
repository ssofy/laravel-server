<?php

namespace SSOfy\Laravel\Commands;

use Illuminate\Console\Command;
use SSOfy\Laravel\UserTokenManager;

class UserTokenGeneration extends Command
{
    protected $signature = 'ssofy:token-gen {user}';

    protected $description = 'Generate User Token';

    /**
     * @var UserTokenManager
     */
    private $userTokenManager;

    public function __construct(UserTokenManager $userTokenManager)
    {
        parent::__construct();

        $this->userTokenManager = $userTokenManager;
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
            $token = $this->userTokenManager->randomDigitsToken($this->argument('user'), $ttl, $len);
        } else {
            $token = $this->userTokenManager->randomStringToken($this->argument('user'), $ttl, $len);
        }

        $this->table([], [
            ['Token', $token],
            ['TTL', $ttl],
            ['Length', $len],
        ]);
    }
}
