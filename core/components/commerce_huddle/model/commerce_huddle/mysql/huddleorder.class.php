<?php
/**
 * @package commerce_huddle
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/huddleorder.class.php');
class HuddleOrder_mysql extends HuddleOrder {}
?>