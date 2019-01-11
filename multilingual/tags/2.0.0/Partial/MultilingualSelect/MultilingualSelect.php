<?php

namespace tiFy\Plugins\Multilingual\Partial\MultilingualSelect;

use tiFy\Partial\PartialController;
use tiFy\Partial\PartialView;
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
        'viewer'    => []
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
    }

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action(
            'init',
            function () {
                wp_register_style(
                    'MultilingualSelect',
                    app()->get('multilingual')->resourcesUrl('/assets/partial/multilingual-select/css/styles.css'),
                    [],
                    190111
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('MultilingualSelect');
        partial('dropdown')->enqueue_scripts();
        partial('multilingual-flag')->enqueue_scripts();
    }

    /**
     * @inheritdoc
     */
    public function parse($attrs = [])
    {
        parent::parse($attrs);

        /** @var Multilingual $multilingual */
        $multilingual = app()->get('multilingual');

        $current = get_current_blog_id();
        $this->set('button', (string) $this->viewer('item', ['item' =>$multilingual->get($current)]));

        $items = [];
        foreach($multilingual->all() as $item) :
            /** @var MultilingualSite $item */
            if ($item->get('blog_id') != $current) :
                $items[$item->get('blog_id')] = (string) $this->viewer('item', compact('item'));
            endif;
        endforeach;
        $this->set('items', $items);
    }

    /**
     * {@inheritdoc}
     */
    public function viewer($view = null, $data = [])
    {
        if (!$this->viewer) :
            $viewer_dir = app()->get('multilingual')->resourcesDir('/views/multilingual-select');

            $this->viewer = view()
                ->setDirectory(is_dir($viewer_dir) ? $viewer_dir : null)
                ->setController(PartialView::class)
                ->setOverrideDir(
                    (($override_dir = $this->get('viewer.override_dir')) && is_dir($override_dir))
                        ? $override_dir
                        : (is_dir($viewer_dir) ? $viewer_dir : __DIR__)
                )
                ->set('partial', $this);
        endif;

        if (func_num_args() === 0) :
            return $this->viewer;
        endif;

        return $this->viewer->make("_override::{$view}", $data);
    }
}