<?php
/**
 * @var tiFy\Partial\PartialView $this
 * @var tiFy\Plugins\Multilingual\MultilingualSite $item
 */
?>
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