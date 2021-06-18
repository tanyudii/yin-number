<?php

namespace tanyudii\YinNumber;

use Illuminate\Support\ServiceProvider;
use tanyudii\YinNumber\Services\YinNumberService;

class YinNumberServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind("yin-number-service", function () {
            return new YinNumberService();
        });

        $this->mergeConfigFrom(
            __DIR__ . "/../assets/yin-number.php",
            "yin-number"
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . "/../assets/migrations" => database_path(
                        "migrations"
                    ),
                ],
                "yin-number-migrations"
            );

            $this->publishes(
                [
                    __DIR__ . "/../assets/yin-number.php" => config_path(
                        "yin-number.php"
                    ),
                ],
                "yin-number-config"
            );
        }
    }
}
