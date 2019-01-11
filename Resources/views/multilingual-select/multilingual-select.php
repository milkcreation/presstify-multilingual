<?php
/**
 * @var tiFy\Partial\PartialView $this
 */
?>
<?php echo partial(
    'dropdown',
    [
        'button' => $this->get('button', ''),
        'items' => $this->get('items', []),
        'attrs' => [
            'class' => 'MultilingualSelect'
        ],
        'classes' => [
            'button'    => 'MultilingualSelect-button',
            'listItems' => 'MultilingualSelect-items',
            'item'      => 'MultilingualSelect-item'
        ]
    ]
);