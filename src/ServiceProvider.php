<?php

namespace SSOfy\Laravel;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as LaravelAuthServiceProvider;
use SSOfy\Laravel\Commands\UserTokenDelete;
use SSOfy\Laravel\Commands\UserTokenGeneration;
use SSOfy\Laravel\Commands\UserTokenVerification;
use SSOfy\Laravel\Middleware\ResponseMiddleware;
use SSOfy\Laravel\Middleware\SignatureVerificationMiddleware;
use SSOfy\Laravel\Repositories\ClientRepository;
use SSOfy\Laravel\Repositories\Contracts\ClientRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\OTPRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\ScopeRepositoryInterface;
use SSOfy\Laravel\Repositories\Contracts\UserRepositoryInterface;
use SSOfy\Laravel\Repositories\OTPRepository;
use SSOfy\Laravel\Repositories\ScopeRepository;
use SSOfy\Laravel\Repositories\UserRepository;

class ServiceProvider extends LaravelAuthServiceProvider
{
    public function register()
    {
        if ($this->app instanceof \Laravel\Lumen\Application) {
            $this->app->configure('ssofy-server');
        }
    }

    public function boot()
    {
        $this->registerPublishes();

        $this->registerCommands();

        $this->registerPolicies();

        $this->setupBindings();

        $this->registerMiddleware();

        $this->registerRoutes();
    }

    public function registerCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            UserTokenGeneration::class,
            UserTokenVerification::class,
            UserTokenDelete::class,
        ]);
    }

    protected function registerPublishes()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // config
        $this->publishes([
            __DIR__ . '/../config/ssofy-server.php'      => config_path('ssofy-server.php'),
        ], ['ssofy', 'ssofy:config']);

        // routes
        $this->publishes([
            __DIR__ . '/../routes/ssofy-server.php' => base_path('/routes/ssofy.php'),
        ], ['ssofy', 'ssofy:routes']);

        // views
        $this->publishes([
            __DIR__ . '/../scaffold/resources/views/' => base_path('resources/views/vendor/ssofy/'),
        ], ['ssofy', 'ssofy:views']);

        // migrations
        if (!class_exists('CreateUserSocialLinksTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/add_missing_columns_to_users_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_add_missing_columns_to_users_table.php'),
                __DIR__ . '/../database/migrations/create_user_social_links_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_user_social_links_table.php'),
            ], ['ssofy', 'ssofy:migrations']);
        }
    }

    private function setupBindings()
    {
        $this->app->singleton(ClientRepositoryInterface::class, config('ssofy-server.repository.client', ClientRepository::class));
        $this->app->singleton(ScopeRepositoryInterface::class, config('ssofy-server.repository.scope', ScopeRepository::class));
        $this->app->singleton(UserRepositoryInterface::class, config('ssofy-server.repository.user', UserRepository::class));
        $this->app->singleton(OTPRepositoryInterface::class, config('ssofy-server.repository.otp', OTPRepository::class));
    }

    private function registerMiddleware()
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('ssofy.signature', SignatureVerificationMiddleware::class);
        $router->aliasMiddleware('ssofy.response', ResponseMiddleware::class);
    }

    private function registerRoutes()
    {
        $router = $this->app['router'];

        $routeFile = base_path('routes/ssofy-server.php');

        if (!file_exists($routeFile)) {
            return;
        }

        $router->prefix('/')
               ->middleware(ResponseMiddleware::class)
               ->group($routeFile);
    }
}
