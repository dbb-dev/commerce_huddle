<?php
/* @var modX $modx */

if ($transport->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_INSTALL:
            $modx = &$transport->xpdo;

            $corePath = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path') . 'components/commerce/');
            $commerce = &$modx->getService('commerce', 'Commerce', $corePath . 'model/commerce/', ['isSetup' => true]);

            $path = MODX_CORE_PATH . 'components/commerce_huddle/model/';
            if (!$commerce->adapter->loadPackage('commerce_huddle', $path)) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load commerce_huddle model');
            }

            $manager = $modx->getManager();
            $logLevel = $modx->setLogLevel(modX::LOG_LEVEL_ERROR);

            $objects = array(
                HuddleOrder::class,
            );
            foreach ($objects as $obj) {
                $manager->createObjectContainer($obj);
            }

            // For database updates, we only want absolutely fatal errors.
            $modx->setLogLevel(modX::LOG_LEVEL_FATAL);
            // @todo future db migrations here
            // Return log level to normal.
            $modx->setLogLevel($logLevel);

            break;
    }
}

return true;
