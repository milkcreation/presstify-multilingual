<?php

namespace tiFy\Plugins\Multilingual;

use tiFy\App\Container\AppServiceProvider;
use tiFy\Plugins\Multilingual\Contracts\Multilingual as MultilingualContract;
use tiFy\Plugins\Multilingual\Contracts\MultilingualSite as MultilingualSiteContract;
use tiFy\Plugins\Multilingual\Partial\MultilingualFlag\MultilingualFlag;
use tiFy\Plugins\Multilingual\Partial\MultilingualDropdown\MultilingualDropdown;

class MultilingualServiceProvider extends AppServiceProvider
{
    /**
     * @var MultilingualContract
     */
    protected $multilingual;

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
                if (!function_exists('wp_get_available_translations')) :
                    require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
                endif;

                $this->multilingual = $this->getContainer()->get('multilingual');

                partial()->register('multilingual-flag', MultilingualFlag::class);
                partial()->register('multilingual-dropdown', MultilingualDropdown::class);

                /** @var MultilingualContract $multilingual */
                if ($this->multilingual->exists()) :
                    add_action('admin_bar_menu', function (\WP_Admin_Bar $wp_admin_bar) {
                        foreach ($this->multilingual->all() as $item) :
                            /** @var MultilingualSiteContract $item */
                            $wp_admin_bar->add_node(
                                [
                                    'id'     => 'blog-' . $item->get('blog_id'),
                                    'title'  => partial('multilingual-flag', ['site' => $item]) .
                                                get_blog_option($item->get('blog_id'), 'blogname'),
                                    'parent' => 'my-sites-list',
                                    'href'   => get_admin_url($item->get('blog_id')),
                                    'meta'   => [
                                        'class' => 'Multilingual-siteEntry',
                                    ]
                                ]
                            );
                        endforeach;
                    }, 99);

                    if (config('multilingual.admin_enqueue_scripts')) :
                        add_action('admin_enqueue_scripts', function () {
                            partial('multilingual-flag')->enqueue_scripts();
                        });
                    endif;

                    if (config('multilingual.wp_enqueue_scripts')) :
                        add_action('wp_enqueue_scripts', function () {
                            partial('multilingual-flag')->enqueue_scripts();
                        });
                    endif;
                endif;

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
