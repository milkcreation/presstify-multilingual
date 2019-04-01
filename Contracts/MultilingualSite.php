<?php

namespace tiFy\Plugins\Multilingual\Contracts;

use tiFy\Contracts\Kernel\ParamsBag;

interface MultilingualSite extends ParamsBag
{
    /**
     * Récupération de l'identifiant de qualification du site.
     *
     * @return int
     */
    public function id();

    /**
     * Récupération du code langue du site.
     *
     * @param int $code Numero de code de langue ISO 639. 1|2|3.
     * @see https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
     *
     * @return string
     */
    public function iso($code);

    /**
     * Récupération de la source de l'image du drapeau représentatif de la langue du site.
     *
     * @return string
     */
    public function flagSrc();
}
