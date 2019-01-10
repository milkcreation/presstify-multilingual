<?php

namespace tiFy\Plugins\Multilingual;

use tiFy\App\Container\AppServiceProvider;

class MultilingualServiceProvider extends AppServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * @internal requis. Tous les noms de qualification de services à traiter doivent être renseignés.
     * @var string[]
     */
    protected $provides = [
        'multilingual'
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('after_setup_theme', function() {
            if (is_multisite()) :
                $this->getContainer()->get('multilingual');
            endif;
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->getContainer()->share('multilingual', function () {
            return new Multilingual();
        });
    }
}
