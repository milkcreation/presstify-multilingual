<?php

namespace tiFy\Plugins\Multilingual\Contracts;

interface Multilingual
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
     * Récupération de l'instance d'un site multilingue.
     *
     * @param int $blog_id Identifiant de qualification du site.
     *
     * @return MultilingualSite
     */
    public function get($blog_id);

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
