<?php

use tiFy\Plugins\Multilingual\Contracts\Multilingual;

if (!function_exists('multilingual')) {
    /**
     * Récupération de l'instance du plugin Multilingual.
     *
     * @return Multilingual
     */
    function multilingual() : Multilingual
    {
        return app()->get('multilingual');
    }
}

/**
 * Affichage de l'interface de bascule de langage
 *
 * @param array $args
 * @param string $echo
 *
 * @return string
 */
function tify_multilingual_switcher($args = [], $echo = true)
{
    return tiFy\Plugins\Multilingual\Switcher::display($args, $echo);
}