<?php

namespace tiFy\Plugins\Multilingual\Contracts;

use tiFy\Contracts\Kernel\Collection;

interface Multilingual extends Collection
{
    /**
     * Controleur de configuration de l'extension.
     * {@internal
     * - null $key Retourne l'instance du controleur de configuration.
     * - array $key Définition d'attributs de configuration.
     * - string $key Récupération de la valeur d'un attribut de configuration.
     * }
     *
     * @param null|array|string Clé d'indice (Syntaxe à point permise)|Liste des attributs de configuration à définir.
     * @param mixed $default Valeur de retour par défaut lors de la récupération d'un attribut.
     *
     * @return mixed|\tiFy\Kernel\Config\Config
     */
    public function config($key = null, $default = null);

    /**
     * Récupération du chemin vers le drapeau associé à code de langue iso.
     *
     * @param string $iso Code de langue iso. ISO 639-1|ISO 639-2|ISO 639-3
     * @see https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
     *
     * @return
     */
    public function getFlagPath($iso);

    /**
     * Récupération de la liste des attributs de traduction associé à une locale.
     *
     * @param string $locale
     *
     * @return array
     */
    public function getTranslation($locale);

    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesDir($path = '');

    /**
     * Récupération de l'url absolue vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesUrl($path = '');
}
