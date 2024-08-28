<?php

use DBB\CommerceHuddle\Modules\Huddle as Module;
use GuzzleHttp\Exception\GuzzleException;
use modmore\Commerce\Gateways\Helpers\GatewayHelper;
use modmore\Commerce\Exceptions\StatusChangeException;

/**
 * @package commerce_huddle
 */
class HuddleOrder extends comSimpleObject
{
    public const STATUS_NEW = 'new';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ERROR = 'error';

    public function submit()
    {
        $client = Module::$client;

        if (!$client) {
            return;
        }

        $order = $this->getOne('Order');

        if (!($order instanceof comOrder)) {
            $this->adapter->log(modX::LOG_LEVEL_ERROR, 'Huddle order record ' . $this->get('id') . ' does not have associated comOrder, cannot process.');
            return;
        }

        $shippingAddress = $order->getShippingAddress();

        $firstName = $shippingAddress->get('firstname');
        $lastName = $shippingAddress->get('lastname');
        $fullName = $shippingAddress->get('fullname');
        GatewayHelper::normalizeNames($firstName, $lastName, $fullName);

        $email = $shippingAddress->get('email');
        $changeOrderStatus = true;

        foreach ($order->getItems() as $orderItem) {
            $product = $orderItem->getProduct();
            $deliveryType = $orderItem->getOne('DeliveryType');

            if ($product && $deliveryType && $deliveryType->get('shipment_type') === 'HuddleShipment') {
                try {
                    $data = [
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'email' => $email,
                        'level_id' => $product->getProperty('huddle_level_id', null)
                    ];

                    $response = $client->send($data);

                    $rawData = (string)$response->getBody();
                    $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);

                    if (!isset($data['status'])) {
                        $this->set('status', self::STATUS_ERROR);
                        $this->set('error_on', time());
                        $this->set('error_log', 'Did not get the expected response back of the webhook');
                        $this->save();

                        return;
                    }
                } catch (GuzzleException | JsonException $e) {
                    $this->adapter->log(modX::LOG_LEVEL_ERROR, get_class($e) . ' submitting order', '', 'HuddleOrder::submit', __FILE__, __LINE__);

                    $this->set('status', self::STATUS_ERROR);
                    $this->set('error_on', time());
                    $this->set('error_log', $e->getMessage());
                    $this->save();

                    return;
                }
            } else {
                $changeOrderStatus = false;
            }
        }

        //Move to order to next step
        if ($changeOrderStatus === true && Module::$nextOrderStatusId !== null) {
            $this->changeOrderStatus($order, Module::$nextOrderStatusId);
        }

        $this->set('status', self::STATUS_SUBMITTED);
        $this->set('submitted_on', time());
        $this->save();
    }

    protected function changeOrderStatus(comOrder $order, $changeId)
    {
        $this->commerce->setWorkingContext($order->get('context'), true);

        $status = $order->getStatus();

        if ($status && $availableChanges = $status->getAvailableChanges()) {
            foreach ($availableChanges as $change) {
                if ($change->get('id') === $changeId) {
                    try {
                        $change->processChange($order);
                    } catch (StatusChangeException $e) {
                        $this->adapter->log(modX::LOG_LEVEL_ERROR, get_class($e) . ' Failed to change status; ' . $e->getMessage(), '', 'HuddleOrder::setOrderStatus', __FILE__, __LINE__);
                    }
                }
            }
        }
    }
}
