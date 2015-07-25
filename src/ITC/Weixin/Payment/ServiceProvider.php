<?php namespace ITC\Weixin\Payment;

use UnexpectedValueException;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

    /**
     * Lifecycle moment
     * @param void
     * @return void
     */
    public function register()
    {
        // no-op
    }

    /**
     * Lifecycle moment
     * @param void
     * @return void
     */
    public function boot()
    {
        $this->registerClient();
        $this->registerResources();
    }

    /**
     * Registers the Client on the service container
     * @param void
     * @return void
     */
    private function registerClient()
    {
        $this->app->bind(Contracts\Client::class, function($app)
        {
            $client = Client::instance(config('weixin-payment'));
            $client->setLogger($app->make('log'));

            return $client;
        });
    }

    /**
     * This satisfies vendor:publish requirements
     * @param void
     * @return
     */
    private function registerResources()
    {
        $project_root = __DIR__.'/../../../..';

        if (!$resources = realpath($project_root.'/resources'))
        {
            throw new UnexpectedValueException('could not locate resources directory');
        }

        $this->publishes([
            "$resources/config/weixin-payment.php" => config_path('weixin-payment.php'),
        ]);
    }
}
