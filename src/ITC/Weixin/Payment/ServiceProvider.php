<?php namespace ITC\Weixin\Payment;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use GuzzleHttp\Client;

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
        $this->app->singleton(Contracts\Client::class, function($app)
        {
            return $this->createClient();
        });
    }

    /**
     * @param void
     * @return ITC\Weixin\Payment\Contracts\Client
     */
    private function createClient()
    {
        $client = new Client();

        $client->register('create-unified-order', new Command\CreateUnifiedOrder());

        return $client;
    }

}
