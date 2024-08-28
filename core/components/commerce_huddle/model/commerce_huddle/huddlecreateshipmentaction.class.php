<?php

/**
 * @package commercehuddle
 */
class HuddleCreateShipmentAction extends comStatusChangeAction
{

    public function process(comOrder $order, comStatus $oldStatus, comStatus $newStatus, comStatusChange $statusChange)
    {
        foreach ($order->getItems() as $orderItem) {
            $deliveryType = $orderItem->getOne('DeliveryType');

            if ($deliveryType && $deliveryType->get('shipment_type') === 'HuddleShipment') {
                $huddleOrder = $this->adapter->newObject(HuddleOrder::class);
                $huddleOrder->fromArray([
                    'order' => $order->get('id'),
                    'status' => HuddleOrder::STATUS_NEW,
                    'created_on' => time(),
                ]);

                return $huddleOrder->save();
            }
        }

        return true;
    }
}
