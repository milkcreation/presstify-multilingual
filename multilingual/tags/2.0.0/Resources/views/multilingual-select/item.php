<?php
/**
 * @var tiFy\Partial\PartialView $this
 * @var tiFy\Plugins\Multilingual\MultilingualSite $item
 */
?>
<a href="<?php echo $item->url(); ?>" class="MultilingualSelect-itemLink" title="<?php _e('Visiter le site', ''); ?>">
    <?php
    echo partial(
        'multilingual-flag',
        [
            'site' => $item->id(),
            'attrs' => [
                'class' => 'MultilingualSelect-itemFlag'
            ]
        ]
    );
    ?>
    <?php echo $item->iso(1); ?>
</a>
