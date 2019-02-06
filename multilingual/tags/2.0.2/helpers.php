<?php
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