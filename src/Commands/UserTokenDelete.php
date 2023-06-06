<?php

namespace SSOfy\Laravel\Commands;

use Illuminate\Console\Command;
use SSOfy\Laravel\UserTokenManager;

class UserTokenDelete extends Command
{
    protected $signature = 'ssofy:token-delete {token}';

    protected $description = 'Delete User Token';

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
        $this->userTokenManager->forget($this->argument('token'));

        $this->info('Token deleted successfully.');
    }
}
