<?php

namespace SSOfy\Laravel\Commands;

use Illuminate\Console\Command;
use SSOfy\Laravel\UserTokenManager;

class UserTokenVerification extends Command
{
    protected $signature = 'ssofy:token-verify {token}';

    protected $description = 'Verify User Token';

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
        $userId = $this->userTokenManager->verify($this->argument('token'));

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
