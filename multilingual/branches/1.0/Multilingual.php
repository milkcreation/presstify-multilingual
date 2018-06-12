<?php
/*
Plugin Name: Multilingual
Plugin URI: http://presstify.com/multilingual
Description: Gestion de site multilingue basée sur Wordpress multisite
Version: 1.0.1
Author: Milkcreation
Author URI: http://milkcreation.fr
Text Domain: tify
*/

namespace tiFy\Plugins\Multilingual;

use tiFy\Lib\Country;

class Multilingual extends \tiFy\Environment\Plugin
{
    /**
     * Listes des sites
     */
    private static $Sites = [];

    /**
     *
     * @var array
     */
    private static $Locales = [];

    /**
     *
     */
    private static $Flags = [];

    /**
     *
     */
    public static $AvailableLanguages = [];

    /**
     *
     */
    public static $Translations = [];

    /**
     * Liste des types de post traductibles
     */
    private static $TranslatePostType = [];

    /**
     * Administration de la page par défaut
     */
    private static $DMZ = true;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        if (!is_multisite()) :
            return;
        endif;

        parent::__construct();

        // Définition des variables d'environnement
        /// Définition des traduction de locales
        if (!function_exists('wp_get_available_translations')) {
            require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        }

        self::$AvailableLanguages = get_available_languages();
        self::$Translations = wp_get_available_translations();
        self::$Translations['en_US'] = [
            'language'     => 'en_US',
            'english_name' => 'English (United States)',
            'native_name'  => 'English (United States)',
            'iso'          => [1 => 'en'],
        ];

        /// Définition de la liste des sites
        $this->_setSites();

        /// Définition des locales
        $this->_setLocales();

        /// Définition des drapeaux par pays de site
        $this->_setFlags();

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
        $this->appAddAction('admin_bar_menu', null, 99);
        $this->appAddAction('tify_taboox_register_node');
        $this->appAddAction('tify_custom_columns_register');
        $this->appAddAction('tify_options_register_node');
    }

    /**
     * EVENEMENTS
     */
    /**
     * A l'initialisation du thème
     */
    final public function setup_theme()
    {
        if (!get_option('tify_multilingual_adminlang', false)) {
            return;
        }

        add_filter('locale', function ($locale = null) {
            if (is_admin()) {
                $locale = get_option('tify_multilingual_adminlang', 0);
            }
            return $locale;
        });
    }

    /**
     * Styles de la barre d'administration
     */
    final public function print_styles()
    {
        ?>
        <style type="text/css">#wp-admin-bar-my-sites .tify_multilingual-flag {
                position: absolute;
                top: 50%;
                margin-top: -9px;
                left: 10px;
                width: 30px;
                height: 18px;
                vertical-align: middle;
                margin-right: 5px;
            }

            #wp-admin-bar-my-sites .tify_multilingual-entry > a.ab-item {
                padding-left: 45px;
            }</style><?php
    }

    /**
     * Personnalisation de la barre d'administration
     * @see http://fr.wikipedia.org/wiki/ISO_3166-1
     * @see http://wpcentral.io/internationalization/
     */
    final public function admin_bar_menu($wp_admin_bar)
    {
        foreach (self::getSites() as $site) :
            $blog_id = $site->blog_id;
            $locale = ($_locale = get_blog_option($blog_id, 'WPLANG')) ? $_locale : 'en_US';

            $wp_admin_bar->add_node(
                [
                    'id'     => 'blog-' . $blog_id,
                    'title'  => self::getFlag($blog_id) . get_blog_option($blog_id, 'blogname'),
                    'parent' => 'my-sites-list',
                    'href'   => get_admin_url($blog_id),
                    'meta'   => [
                        'class' => 'tify_multilingual-entry',
                    ],
                ]
            );
        endforeach;
    }

    /**
     * Déclaration de taboox
     */
    final public function tify_taboox_register_form()
    {
        //tify_taboox_register_form( 'tiFy_Multilingual_MenuSwitcher_Taboox', $this );
        //tify_taboox_register_form( 'tiFy_Multilingual_AdminLang_Taboox', $this );
    }

    /**
     * Déclaration des boîte de saisie
     */
    final public function tify_taboox_register_node()
    {
        foreach (self::getTranslatePostType() as $post_type => $attrs) :
            tify_taboox_register_node(
                $post_type,
                [
                    'id'    => 'tiFyMultilingualTranslate',
                    'title' => __('Traductions', 'tify'),
                    'cb'    => '\tiFy\Plugins\Multilingual\Taboox\PostType\Translate\Admin\Translate',
                    'order' => 0,
                ]
            );
        endforeach;
    }

    /**
     *
     */
    final public function tify_custom_columns_register()
    {
        foreach (self::getTranslatePostType() as $post_type => $attrs) :
            tify_custom_columns_register(
                '\tiFy\Plugins\Multilingual\CustomColumns\PostType\Translate\Translate',
                [
                    'position' => 3,
                ],
                'post_type',
                $post_type
            );
        endforeach;
    }

    /**
     * Personnalisation de la page par défaut
     */
    final public function tify_options_register_node()
    {
        if (!self::$DMZ || empty(self::$TranslatePostType)) {
            return;
        }

        tify_options_register_node([
            'id'    => 'tiFyMultilingualDMZ',
            'title' => __('Traduction', 'tify'),
            'cb'    => '\tiFy\Plugins\Multilingual\Taboox\Options\DMZ\Admin\DMZ',
        ]);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Définition de la liste des sites gérant le multilangage
     */
    private function _setSites()
    {
        return self::$Sites = \get_sites();
    }

    /**
     * Définition des locales
     */
    private function _setLocales()
    {
        $locales = [];
        foreach (self::getSites() as $site) :
            $locales[$site->blog_id] = ($locale = get_blog_option($site->blog_id, 'WPLANG')) ? $locale : 'en_US';
        endforeach;

        return self::$Locales = $locales;
    }

    /**
     * Définition des drapeaux de site
     */
    private function _setFlags()
    {
        foreach (self::getSites() as $site) :
            $country = strtolower(substr(self::getLocale($site->blog_id), 3, 2));

            if (!$flag = Country::getFlagImgSrc($country)) :
                continue;
            endif;

            self::$Flags[$site->blog_id] = $flag;
        endforeach;
    }

    /**
     * Récupération de la liste des sites du réseau multilangage
     */
    final public static function getSites($args = [])
    {
        $sites = self::$Sites;

        extract($args);

        if (!empty($exclude)) :
            if (!is_array($exclude)) {
                $exclude = explode(',', $exclude);
            }

            $exclude = array_map('absint', $exclude);
            foreach ($sites as $k => $site) :
                if (!in_array($site->blog_id, $exclude)) {
                    continue;
                }
                unset($sites[$k]);
            endforeach;
        endif;

        return $sites;
    }

    /**
     * Récupération des traductions
     */
    final public static function getTranslations()
    {
        return self::$Translations;
    }

    /**
     * Récupération de la traduction d'une locale
     */
    final public static function getTranslation($locale)
    {
        if (isset(self::$Translations[$locale])) {
            return self::$Translations[$locale];
        }
    }

    /**
     * Récupération de la locale d'un site
     */
    final public static function getLocale($blog_id)
    {
        if (!isset(self::$Locales[$blog_id])) {
            return;
        }

        return self::$Locales[$blog_id];
    }

    /**
     * Récupération du drapeau d'un site
     */
    final public static function getFlag($blog_id, $attrs = [])
    {
        if (!isset(self::$Flags[$blog_id])) :
            return;
        endif;
        $src = self::$Flags[$blog_id];

        $flag = "<img src=\"{$src}\"";
        foreach ($attrs as $k => $v) :
            if (in_array($k, ['src', 'class'])) :
                continue;
            endif;
            $flag .= " {$k}=\"{$v}\"";
        endforeach;
        $flag .= " class=\"flag tify_multilingual-flag tify_multilingual-flag-" . self::getLocale($blog_id) . "\"";
        $flag .= "/>";

        return $flag;
    }

    /**
     * Déclaration des types de post traductibles
     */
    final public static function setTranslatePostType($post_type, $attrs)
    {
        if (!isset(self::$TranslatePostType[$post_type])) {
            self::$TranslatePostType[$post_type] = $attrs;
        }
    }

    /**
     * Vérification d'existance d'un type de post traductible
     */
    final public static function hasTranslatePostType($post_type)
    {
        return isset(self::$TranslatePostType[$post_type]);
    }

    /**
     * Récupération des attributs de type de post traductible
     */
    final public static function getTranslatePostType($type = null)
    {
        if (!$type) {
            return self::$TranslatePostType;
        }
        if (isset(self::$TranslatePostType[$type])) {
            return self::$TranslatePostType[$type];
        }

        return false;
    }
}
