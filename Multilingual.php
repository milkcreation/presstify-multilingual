<?php

/**
 * @name Multilingual
 * @desc Extension PresstiFy de gestion de sites multilingues
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @package presstify-plugins/multilingual
 * @namespace tiFy\Plugins\Multilingual
 * @version 2.0.0
 */

namespace tiFy\Plugins\Multilingual;

use tiFy\Kernel\Collection\Collection;
use tiFy\Plugins\Multilingual\Contracts\Multilingual as MultilingualContracts;
use tiFy\Plugins\Multilingual\Partial\MultilingualFlag\MultilingualFlag;

/**
 * Class Multilingual
 * @package tiFy\Plugins\Multilingual
 *
 * Activation :
 * ----------------------------------------------------------------------------------------------------
 * Dans config/app.php ajouter \tiFy\Plugins\Multilingual\Multilingual à la liste des fournisseurs de services
 *     chargés automatiquement par l'application.
 * ex.
 * <?php
 * ...
 * use tiFy\Plugins\Multilingual\MultilingualServiceProvider;
 * ...
 *
 * return [
 *      ...
 *      'providers' => [
 *          ...
 *          MultilingualServiceProvider::class
 *          ...
 *      ]
 * ];
 *
 * Configuration :
 * ----------------------------------------------------------------------------------------------------
 * Dans le dossier de config, créer le fichier multilingual.php
 * @see /vendor/presstify-plugins/multilingual/Resources/config/multilingual.php Exemple de configuration
 *
 * @see components/flag-icon-css
 */
class Multilingual extends Collection implements MultilingualContracts
{
    /**
     * Liste des sites disponibles.
     * @var MultilingualSite[]
     */
    protected $items = [];

    /**
     * Liste des langages disponibles.
     * @var array
     */
    protected $languages = [];

    /**
     * Liste des traductions disponibles.
     * @var array
     */
    protected $translations = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        if (!function_exists('wp_get_available_translations')) :
            require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        endif;

        $this->setLanguages();
        $this->setTranslations();
        $this->setSites();

        partial()->register('multilingual-flag', MultilingualFlag::class);

        if ($this->items) :
            add_action('admin_bar_menu', function (\WP_Admin_Bar $wp_admin_bar) {
                foreach ($this->items as $item) :
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

            if ($this->config('admin_enqueue_scripts')) :
                add_action('admin_enqueue_scripts', function () {
                   partial('multilingual-flag')->enqueue_scripts();
                });
            endif;

            if ($this->config('wp_enqueue_scripts')) :
                add_action('wp_enqueue_scripts', function () {
                    partial('multilingual-flag')->enqueue_scripts();
                });
            endif;
        endif;


        /**
        /// Définition de la gestion des traduction
        if ($translate = self::tFyAppConfig('translate')) :
            if (!empty($translate['admin'])) :
                if (isset($translate['admin']['dmz'])) :
                    self::$DMZ = $translate['admin']['dmz'];
                endif;
            endif;
            if (!empty($translate['post_type'])) :
                foreach ($translate['post_type'] as $post_type => $attrs) :
                    self::setTranslatePostType($post_type, $attrs);
                endforeach;
            endif;
        endif;

        // Déclaration des dépendances
        new Duplicate\PostType\PostType;
        new Switcher;
        require_once(self::tFyAppDirname() . '/Helpers.php');

        // Déclaration des événements
        $this->appAddAction('setup_theme');
        $this->appAddAction('wp_print_styles', 'print_styles');
        $this->appAddAction('admin_print_styles', 'print_styles');

        $this->appAddAction('tify_taboox_register_node');
        $this->appAddAction('tify_custom_columns_register');
        $this->appAddAction('tify_options_register_node');
         */
    }

    /**
     * Définition de la liste des langues disponibles.
     *
     * @return void
     */
    protected function setLanguages()
    {
        $this->languages = get_available_languages();
    }


    /**
     * Définition de la liste des sites disponibles.
     *
     * @return void
     */
    protected function setSites()
    {
        foreach(get_sites() as $wp_site) :
            $this->items[$wp_site->blog_id] = new MultilingualSite($wp_site, $this);
        endforeach;
    }

    /**
     * Définition de la liste des traductions disponibles.
     *
     * @return void
     */
    protected function setTranslations()
    {
        $this->translations = wp_get_available_translations();

        $this->translations['en_US'] = $this->translations['en_US'] ?? [
            'language'     => 'en_US',
            'english_name' => 'English (United States)',
            'native_name'  => 'English (United States)',
            'iso'          => [1 => 'en'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function config($key = null, $default = null)
    {
        return config("multilingual.$key", $default);
    }

    /**
     * @inheritdoc
     */
    public function getFlagPath($iso)
    {
        $path = ABSPATH . "/vendor/components/flag-icon-css/flags/4x3/{$iso}.svg";

        return file_exists($path) ? $path : null;
    }

    /**
     * @inheritdoc
     */
    public function getTranslation($locale)
    {
        return $this->translations[$locale] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function resourcesDir($path = '')
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists(__DIR__ . "/Resources{$path}"))
            ? __DIR__ . "/Resources{$path}"
            : '';
    }

    /**
     * @inheritdoc
     */
    public function resourcesUrl($path = '')
    {
        $cinfo = class_info($this);
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists($cinfo->getDirname() . "/Resources{$path}"))
            ? $cinfo->getUrl() . "/Resources{$path}"
            : '';
    }
}
