<?php

require_once __DIR__ . '/src/Config.php';
require_once __DIR__ . '/src/RemoteResolver.php';
require_once __DIR__ . '/src/ComputerHook.php';

use Plugin\TacticalRMMRemote\Config;

function plugin_tacticalrmmremote_post_item_form($params) {
   \Plugin\TacticalRMMRemote\ComputerHook::postItemForm((array)$params);
}

function plugin_tacticalrmmremote_install() {
   $current = \Config::getConfigurationValues(Config::CONTEXT);
   if (!isset($current[Config::KEY_BASE_URL]) || !isset($current[Config::KEY_URL_TEMPLATE])) {
      Config::saveSettings(
         $current[Config::KEY_BASE_URL] ?? '',
         $current[Config::KEY_URL_TEMPLATE] ?? Config::DEFAULT_URL_TEMPLATE
      );
   }

   return true;
}

function plugin_tacticalrmmremote_uninstall() {
   \Config::deleteConfigurationValues(Config::CONTEXT);

   return true;
}
