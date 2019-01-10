<?php
/**
 * @var tiFy\Plugins\Multilingual\Contracts\MultilingualSite $site;
 */
?>

<?php
echo partial(
    'tag',
     [
        'tag'       => 'img',
        'attrs'     => $this->get('attrs', [])
     ]
);