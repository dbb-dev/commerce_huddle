<?php

use modmore\Commerce\Admin\Widgets\Form\TextField;

/**
 * @package commerce_huddle
 */
class HuddleShipment extends comOrderShipment
{

    public static function getFieldsForProduct(Commerce $commerce, comProduct $product, comDeliveryType $deliveryType)
    {
        $fields = [];

        $fields[] = new TextField($commerce, [
            'name' => 'properties[huddle_level_id]',
            'label' => $commerce->adapter->lexicon('commerce_huddle.huddle_level_id'),
            'description' => $commerce->adapter->lexicon('commerce_huddle.huddle_level_id_desc'),
            'emptyOption' => true,
            'value' => $product->getProperty('huddle_level_id')
        ]);

        return $fields;
    }
}