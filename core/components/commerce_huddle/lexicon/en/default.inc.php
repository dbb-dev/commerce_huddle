<?php
$_lang['commerce_huddle'] = 'Huddle';
$_lang['commerce_huddle.description'] = 'A Huddle integration for Commerce.';

// Delivery type
$_lang['commerce.HuddleShipment'] = 'Huddle Shipment';

$_lang['commerce_huddle.webhook'] = 'Webhook';
$_lang['commerce_huddle.webhook_desc'] = 'The webhook for sending the order info to Huddle';
$_lang['commerce_huddle.order_status_id'] = 'Next Order Status';
$_lang['commerce_huddle.order_status_id_desc'] = 'When the order only contain Huddle shipments, push the order to the next status. When blank the order will keep it\'s orginal status.';
$_lang['commerce_huddle.huddle_level_id'] = 'Huddle access level ID';
$_lang['commerce_huddle.huddle_level_id_desc'] = 'The access level ID can be found in Huddle (Toegangsniveaus >> Toegangsniveau ID)';

$_lang['commerce_huddle.scheduler_not_available'] = '<p class="ui error message" style="display: block">The cron job required for the Commerce scheduler is not yet set up, or is misconfigured. The scheduler is required for sending order data to Huddle in the background. <a href="https://docs.modmore.com/en/Commerce/v1/Scheduler/index.html" target="_blank" rel="noopener">Learn more in the documentation</a></p>';
