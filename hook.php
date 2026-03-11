<?php

use Plugin\TacticalRMMRemote\Config;

function plugin_tacticalrmmremote_install() {
   $current = \Config::getConfigurationValues(Config::CONTEXT);
   if (!isset($current[Config::KEY_BASE_URL])) {
      Config::saveBaseUrl('');
   }

   return true;
}

function plugin_tacticalrmmremote_uninstall() {
   \Config::deleteConfigurationValues(Config::CONTEXT);

   return true;
}
