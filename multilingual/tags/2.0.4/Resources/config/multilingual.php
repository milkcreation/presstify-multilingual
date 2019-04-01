<?php

/**
 * Exemple de configuration.
 */

return [
    /**
     * Chargement automatique des scripts de l'interface d'administration.
     * {@internal Webpack : "import 'presstify-plugins/multilingual/Resources/assets/index.js';" }
     *
     * @var boolean
     */
    'admin_enqueue_scripts' => true,

    /**
     * Chargement automatique des scripts de l'interface utilisateur.
     * {@internal Webpack : "import 'presstify-plugins/multilingual/Resources/assets/index.js';" }
     *
     * @var boolean
     */
    'wp_enqueue_scripts'    => true
];