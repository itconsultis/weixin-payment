<?php namespace ITC\Weixin\Payment;

use UnexpectedValueException;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * @codeCoverageIgnore
 */
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

    /**
     * @param void
     * @return array
     */
    public static function compiles()
    {
        return [
            __DIR__.'/Contracts/HashGenerator.php', 
            __DIR__.'/Contracts/Serializer.php', 
            __DIR__.'/Contracts/Message.php', 
            __DIR__.'/Contracts/MessageFactory.php', 
            __DIR__.'/Contracts/Command.php', 
            __DIR__.'/Contracts/Client.php', 
            __DIR__.'/Command/Command.php',
            __DIR__.'/Command/CreateUnifiedOrder.php',
            __DIR__.'/Command/OrderQuery.php',
            __DIR__.'/DummyLogger.php',
            __DIR__.'/HashGenerator.php',
            __DIR__.'/XmlSerializer.php',
            __DIR__.'/Client.php',
        ];
    }
}
