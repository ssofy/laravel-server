<?php

namespace SSOfy\Laravel;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as LaravelAuthServiceProvider;
use SSOfy\Laravel\Commands\OTPDelete;
use SSOfy\Laravel\Commands\OTPGeneration;
use SSOfy\Laravel\Commands\OTPVerification;
use SSOfy\Laravel\Middleware\SSOMiddleware;
use SSOfy\Laravel\Middleware\ResponseMiddleware;
use SSOfy\Laravel\Middleware\SignatureValidationMiddleware;
use SSOfy\Laravel\Repositories\APIRepository;
use SSOfy\Laravel\Repositories\ClientRepository;
use SSOfy\Laravel\Repositories\Contracts\APIRepositoryInterface;
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
            $this->app->configure('ssofy');
        }
    }

    public function boot()
    {
        $this->registerPublishes();

        $this->registerCommands();

        $this->registerPolicies();

        $this->setupBindings();

        $this->registerAuthProvider();

        $this->registerMiddleware();

        $this->registerRoutes();
    }

    public function registerCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            OTPGeneration::class,
            OTPVerification::class,
            OTPDelete::class,
        ]);
    }

    protected function registerPublishes()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // config
        $this->publishes([
            __DIR__ . '/../config/ssofy.php' => config_path('ssofy.php'),
        ], ['ssofy', 'ssofy:config']);

        // routes
        $this->publishes([
            __DIR__ . '/../routes/ssofy.php' => base_path('/routes/ssofy.php'),
        ], ['ssofy', 'ssofy:routes']);

        // views
        $this->publishes([
            __DIR__ . '/../scaffold/resources/views/' => base_path('resources/views/vendor/ssofy/'),
        ], ['ssofy', 'ssofy:views']);

        if (!class_exists('CreateUserSocialLinksTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_user_social_links_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_user_social_links_table.php'),
            ], ['ssofy', 'ssofy:migrations']);
        }
    }

    private function setupBindings()
    {
        $this->app->singleton(Context::class);

        $this->app->bind('ssofy', Context::class);

        $this->app->singleton(ClientRepositoryInterface::class, config('ssofy.repository.client', ClientRepository::class));
        $this->app->singleton(ScopeRepositoryInterface::class, config('ssofy.repository.scope', ScopeRepository::class));
        $this->app->singleton(UserRepositoryInterface::class, config('ssofy.repository.user', UserRepository::class));
        $this->app->singleton(OTPRepositoryInterface::class, config('ssofy.repository.otp', OTPRepository::class));
        $this->app->singleton(APIRepositoryInterface::class, config('ssofy.repository.api', APIRepository::class));
    }

    private function registerAuthProvider()
    {
        $auth = $this->app['auth'];

        $auth->provider('ssofy', function ($app, $config) {
            return $app->make(UserProvider::class, ['providerConfig' => $config]);
        });

        $auth->extend('ssofy', function ($app, $name, $config) use (&$auth) {
            $provider = $auth->createUserProvider($config['provider']);
            return $app->make(ServiceGuard::class, ['provider' => $provider]);
        });
    }

    private function registerMiddleware()
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('ssofy.signature', SignatureValidationMiddleware::class);
        $router->aliasMiddleware('ssofy.response', ResponseMiddleware::class);
        $router->aliasMiddleware('ssofy', SSOMiddleware::class);
    }

    private function registerRoutes()
    {
        $router = $this->app['router'];

        $routeFile = base_path('routes/ssofy.php');

        if (!file_exists($routeFile)) {
            return;
        }

        $router->prefix('/')
               ->middleware(ResponseMiddleware::class)
               ->group($routeFile);
    }
}
