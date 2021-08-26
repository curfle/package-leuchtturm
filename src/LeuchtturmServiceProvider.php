<?php

namespace Leuchtturm;

use Curfle\Support\ServiceProvider;

class LeuchtturmServiceProvider extends ServiceProvider{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->app->singleton("Leuchtturm", function(){
            return new LeuchtturmManager();
        });
    }
}