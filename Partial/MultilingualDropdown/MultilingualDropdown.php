<?php

namespace tiFy\Plugins\Multilingual\Partial\MultilingualDropdown;

use tiFy\Contracts\Partial\PartialFactory as PartialFactoryContract;
use tiFy\Partial\PartialFactory;
use tiFy\Partial\PartialView;
use tiFy\Plugins\Multilingual\Contracts\Multilingual;
use tiFy\Plugins\Multilingual\Contracts\MultilingualSite;

class MultilingualDropdown extends PartialFactory
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
     * @inheritdoc
     */
    public function boot(): void
    {
        add_action('init', function () {
            wp_register_style(
                'MultilingualDropdown',
                app()->get('multilingual')->resourcesUrl('/assets/partial/multilingual-Dropdown/css/styles.css'),
                [],
                190111
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('MultilingualDropdown');
        partial('dropdown')->enqueue_scripts();
        partial('multilingual-flag')->enqueue_scripts();
    }

    /**
     * @inheritdoc
     */
    public function parse() : PartialFactoryContract
    {
        parent::parse();

        $this->set('attrs.class', sprintf($this->get('attrs.class', '%s'), 'MultilingualDropdown'));

        /** @var Multilingual $multilingual */
        $multilingual = app()->get('multilingual');

        $current = get_current_blog_id();
        $this->set('button', (string) $this->viewer('button', ['item' => $multilingual->get($current)]));

        $sites = $multilingual->collect()->filter(function(MultilingualSite $item) {
            return $item['deleted'] !== '1' && $item['archived'] !== '1';
        });

        $items = [];
        foreach($sites as $item) :
            /** @var MultilingualSite $item */
            if ($item->get('blog_id') != $current) :
                $items[$item->get('blog_id')] = (string) $this->viewer('item', compact('item'));
            endif;
        endforeach;

        $this->set('items', $items);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function parseDefaults() : PartialFactoryContract
    {
        foreach($this->get('view', []) as $key => $value) :
            $this->viewer()->set($key, $value);
        endforeach;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function viewer($view = null, $data = [])
    {
        if (!$this->viewer) :
            $viewer_dir = app()->get('multilingual')->resourcesDir('/views/multilingual-dropdown');

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