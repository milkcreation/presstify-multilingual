<?php
/**
 * @var tiFy\Partial\PartialView $this
 * @var tiFy\Plugins\Multilingual\MultilingualSite $item
 */
?>
<a href="<?php echo $item->url(); ?>"
   class="MultilingualDropdown-itemLink"
   title="<?php _e('Visiter le site', 'tify'); ?>"
>
    <?php
    echo partial(
        'multilingual-flag',
        [
            'site' => $item->id(),
            'attrs' => [
                'class' => 'MultilingualDropdown-itemFlag'
            ]
        ]
    );
    ?>

    <?php echo $item->iso(1); ?>
</a>
