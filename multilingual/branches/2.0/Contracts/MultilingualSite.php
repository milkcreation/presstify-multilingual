<?php

namespace tiFy\Plugins\Multilingual\Contracts;

use tiFy\Contracts\Kernel\ParamsBag;

interface MultilingualSite extends ParamsBag
{
    /**
     * Récupération de la source de l'image du drapeau représentatif de la langue du site.
     *
     * @return string
     */
    public function flagSrc();
}
