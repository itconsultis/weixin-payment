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
        $this->app->singleton(Contracts\Client::class, function($app)
        {
            return $this->createClient();
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
        $client = new Client([
            'app_id' => config('weixin-payment.app_id'),
            'mch_id' => config('weixin-payment.mch_id'),
            'secret' => config('weixin-payment.hash_secret'),
            'public_key_path' => config('weixin-payment.public_key_path'),
            'private_key_path' => config('weixin-payment.private_key_path'),
        ]);

        $client->register(new Command\CreateUnifiedOrder());
        $client->register(new Command\CreateJavascriptParameters());

        return $client;
    }

    

}
