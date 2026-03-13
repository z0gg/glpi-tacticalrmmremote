<?php

require_once __DIR__ . '/inc/Config.php';

use Plugin\TacticalRMMRemote\Config;

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
