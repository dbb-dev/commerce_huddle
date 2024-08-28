<?php

namespace DBB\CommerceHuddle\Modules;

use DBB\CommerceHuddle\Client;
use HuddleCreateShipmentAction;
use HuddleOrder;
use HuddleShipment;
use modmore\Commerce\Admin\Widgets\Form\DescriptionField;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Dispatcher\EventDispatcher;
use modmore\Commerce\Modules\BaseModule;
use modmore\Commerce\Services\Scheduler\Interval;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class Huddle extends BaseModule
{
    public static ?Client $client = null;

    public static $nextOrderStatusId = null;

    /**
     * Return the name of the module
     * @return string
     */
    public function getName(): string
    {
        $this->adapter->loadLexicon('commerce_huddle:default');
        return $this->adapter->lexicon('commerce_huddle');
    }

    /**
     * Return the name of the author of this module.
     * @return string
     */
    public function getAuthor(): string
    {
        return "DBB";
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_huddle.description');
    }

    /**
     *
     * @param EventDispatcher $dispatcher
     * @return void
     */
    public function initialize(EventDispatcher $dispatcher)
    {
        self::$client = new Client(($this->config['webhook'] ?? ''));

        // Load our lexicon
        $this->adapter->loadLexicon('commerce_huddle:default');

        //Only set the order status id when it isnt empty
        if (isset($this->config['order_status_id']) && !empty($this->config['order_status_id'])) {
            self::$nextOrderStatusId = (int)$this->config['order_status_id'];
        }

        // Add the xPDO package, so Commerce can detect the derivative classes
        $root = dirname(__DIR__, 2);
        $path = $root . '/model/';

        $this->adapter->loadPackage('commerce_huddle', $path);

        //Load model class for access to static methods.
        $this->adapter->loadClass(HuddleShipment::class);
        $this->adapter->loadClass(HuddleCreateShipmentAction::class);

        $this->commerce->scheduler()->repeat([__CLASS__, 'processOrders'], new Interval());
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

        $fields[] = new TextField($this->commerce, [
            'name' => 'properties[webhook]',
            'label' => $this->adapter->lexicon('commerce_huddle.webhook'),
            'description' => $this->adapter->lexicon('commerce_huddle.webhook_desc'),
            'value' => $module->getProperty('webhook'),
        ]);

        $fields[] = new TextField($this->commerce, [
            'name' => 'properties[order_status_id]',
            'label' => $this->adapter->lexicon('commerce_huddle.order_status_id'),
            'description' => $this->adapter->lexicon('commerce_huddle.order_status_id_desc'),
            'value' => $module->getProperty('order_status_id'),
        ]);

        if (!$this->commerce->scheduler()->isAvailable()) {
            $fields[] = new DescriptionField($this->commerce, [
                'description' => $this->adapter->lexicon('commerce_huddle.scheduler_not_available'),
                'raw' => true,
            ]);
        }

        return $fields;
    }

    public static function processOrders(\Commerce $commerce): void
    {
        $c = $commerce->adapter->newQuery(HuddleOrder::class);
        $c->where([
            'status' => HuddleOrder::STATUS_NEW,
        ]);
        $c->sortby('created_on', 'ASC');

        foreach ($commerce->adapter->getIterator(HuddleOrder::class, $c) as $hudlleOrder) {
            $hudlleOrder->submit();
        }
    }
}
