<?php
/**
 * @var tiFy\Partial\PartialView $this
 */
?>
<?php echo partial(
    'dropdown',
    [
        'button'  => $this->get('button', ''),
        'items'   => $this->get('items', []),
        'attrs'   => $this->get('attrs', []),
        'classes' => [
            'button'    => 'MultilingualDropdown-button',
            'listItems' => 'MultilingualDropdown-items',
            'item'      => 'MultilingualDropdown-item',
        ],
    ]
);