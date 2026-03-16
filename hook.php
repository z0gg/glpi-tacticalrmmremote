<?php

function plugin_tacticalrmmremote_require_runtime() {
   require_once __DIR__ . '/src/Config.php';
   require_once __DIR__ . '/src/RemoteResolver.php';
   require_once __DIR__ . '/src/ComputerHook.php';
}

function plugin_tacticalrmmremote_post_item_form($params) {
   plugin_tacticalrmmremote_require_runtime();
   \Plugin\TacticalRMMRemote\ComputerHook::postItemForm((array)$params);
}

function plugin_tacticalrmmremote_install() {
   $context = 'plugin:tacticalrmmremote';
   $current = \Config::getConfigurationValues($context);
   if (!isset($current['base_url']) || !isset($current['url_template'])) {
      \Config::setConfigurationValues($context, [
         'base_url'     => trim((string)($current['base_url'] ?? '')),
         'url_template' => trim((string)($current['url_template'] ?? '/takecontrol/{id}')) ?: '/takecontrol/{id}',
      ]);
   }

   return true;
}

function plugin_tacticalrmmremote_uninstall() {
   \Config::deleteConfigurationValues('plugin:tacticalrmmremote');

   return true;
}
