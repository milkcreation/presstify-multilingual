<?php

namespace tiFy\Plugins\Multilingual\Partial;

use tiFy\Partial\PartialController;
use tiFy\Plugins\Multilingual\Contracts\Multilingual;
use tiFy\Plugins\Multilingual\Contracts\MultilingualSite;

class MultilingualSelect extends PartialController
{
    /**
     * Liste des attributs de configuration.
     * @var array $attributes {
     *      @var string $before Contenu placé avant.
     *      @var string $after Contenu placé après.
     *      @var array $attrs Attributs de balise HTML.
     *      @var array $viewer Attributs de configuration du controleur de gabarit d'affichage.
     *      @var int|MultilingualSite Site associé.
     * }
     */
    protected $attributes = [
        'before'    => '',
        'after'     => '',
        'attrs'     => [],
        'viewer'    => [],
        'show_flag' => true
    ];

    /**
     * CONSTRUCTEUR.
     *
     * @param string $id Nom de qualification.
     * @param array $attrs Liste des attributs de configuration.
     *
     * @return void
     */
    public function __construct($id = null, $attrs = [])
    {
        parent::__construct($id, $attrs);

        $this->viewer_dir = app()->get('multilingual')->resourcesDir('/views/multilingual-select');
    }

    /**
     * @inheritdoc
     */
    public function parse($attrs = [])
    {
        parent::parse($attrs);


    }
}