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
        $this->app->bind(Contracts\Client::class, function($app)
        {
            $client = $this->createClient();
            $client->setLogger($app->make('log'));

            return $client;
        });

        $project_root = __DIR__.'/../../../..';

        if (!$resources = realpath($project_root.'/resources'))
        {
            throw new UnexpectedValueException('could not locate resources directory');
        }

        $this->publishes([
            "$resources/config/weixin-payment.php" => config_path('weixin-payment.php'),
        ]);
    }

    /**
     * @param void
     * @return ITC\Weixin\Payment\Contracts\Client
     */
    private function createClient()
    {
        $client = new Client(config('weixin-payment'));
        $client->register(new Command\CreateUnifiedOrder());
        $client->register(new Command\CreateJavascriptParameters());

        return $client;
    }

}
