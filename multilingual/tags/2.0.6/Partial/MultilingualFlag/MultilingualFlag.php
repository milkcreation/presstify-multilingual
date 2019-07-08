<?php

namespace tiFy\Plugins\Multilingual\Partial\MultilingualFlag;

use tiFy\Contracts\Partial\PartialFactory as PartialFactoryContract;
use tiFy\Partial\PartialFactory;
use tiFy\Partial\PartialView;
use tiFy\Plugins\Multilingual\Contracts\MultilingualSite;

class MultilingualFlag extends PartialFactory
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
        'site'      => 0
    ];

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        add_action(
            'init',
            function () {
                wp_register_style(
                    'MultilingualFlag',
                    app()->get('multilingual')->resourcesUrl('/assets/partial/multilingual-flag/css/styles.css'),
                    [],
                    190110
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('MultilingualFlag');
    }

    /**
     * @inheritdoc
     */
    public function parse(): PartialFactoryContract
    {
        parent::parse();

        $this->set('attrs.class', sprintf($this->get('attrs.class', '%s'), 'MultilingualFlag'));

        $site = $this->get('site');
        if (!$site instanceof MultilingualSite) :
            $site = app()->get('multilingual')->get((int) $site);
        endif;
        $this->set('site', $site);

        if ($site) :
            if ($src = $site->flagSrc()) :
                $this->set('attrs.src', $src);
            endif;
        endif;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parseDefaults(): PartialFactoryContract
    {
        foreach($this->get('view', []) as $key => $value) :
            $this->viewer()->set($key, $value);
        endforeach;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function viewer($view = null, $data = [])
    {
        if (!$this->viewer) :
            $viewer_dir = app()->get('multilingual')->resourcesDir('/views/multilingual-flag');

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