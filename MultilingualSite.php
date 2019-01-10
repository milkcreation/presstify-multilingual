<?php

namespace tiFy\Plugins\Multilingual;

use tiFy\Kernel\Params\ParamsBag;
use tiFy\Plugins\Multilingual\Contracts\MultilingualSite as MultilingualSiteContract;
use tiFy\Kernel\Tools;
use WP_Site;

class MultilingualSite extends ParamsBag implements MultilingualSiteContract
{
    /**
     * Instance de controleur principal.
     * @var Multilingual
     */
    protected $multilingual;

    /**
     * CONSTRUCTEUR
     *
     * @param ParamsBag $config Instance de traitement des attributs de configuration.
     *
     * @return void
     */
    public function __construct(WP_Site $wp_site, Multilingual $multilingual)
    {
        $this->multilingual = $multilingual;

        parent::__construct($wp_site->to_array());
    }

    /**
     * @inheritdoc
     */
    public function flagSrc()
    {
        return Tools::File()->imgBase64Src($this->get('flag'));
    }

    /**
     * @inheritdoc
     */
    public function parse($attrs = [])
    {
        parent::parse($attrs);

        $this->set('locale', get_blog_option($this->get('blog_id'), 'WPLANG')? : 'en_US');

        $this->set('translation', $this->multilingual->getTranslation($this->get('locale')));

        $flag = '';
        foreach($this->get('translation.iso') as $iso) :
            if ($flag = $this->multilingual->getFlagPath($iso)) :
                break;
            endif;
        endforeach;

        if (!$flag) :
            $iso = strtolower(substr($this->get('locale'), 3, 2));
            $flag = $this->multilingual->getFlagPath($iso);
        endif;

        $this->set('flag', $flag);
    }

    /**
     * @inheritdoc
     */
    public function url()
    {
        return get_site_url($this->get('blog_id'));
    }
}
