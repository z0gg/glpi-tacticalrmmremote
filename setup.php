<?php

require_once __DIR__ . '/src/Config.php';

use Glpi\Plugin\Hooks;

define('PLUGIN_TACTICALRMMREMOTE_VERSION', '0.2.0');
define('PLUGIN_TACTICALRMMREMOTE_MIN_GLPI', '10.0.0');
define('PLUGIN_TACTICALRMMREMOTE_MAX_GLPI', '11.99.99');

function plugin_init_tacticalrmmremote() {
   global $PLUGIN_HOOKS;

   // Ensure plugin classes are available even when Composer autoload is not installed.
   $class_files = [
      __DIR__ . '/src/Config.php',
      __DIR__ . '/src/RemoteResolver.php',
      __DIR__ . '/src/ComputerHook.php',
   ];

   foreach ($class_files as $file) {
      if (is_readable($file)) {
         require_once $file;
      }
   }

   $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['tacticalrmmremote'] = true;
   $PLUGIN_HOOKS['config_page']['tacticalrmmremote'] = 'front/config.php';
   $PLUGIN_HOOKS['menu_toadd']['tacticalrmmremote'] = [
      'config' => 'Plugin\\TacticalRMMRemote\\Config',
   ];

   $PLUGIN_HOOKS['post_item_form']['tacticalrmmremote'] = [
      'Computer' => 'Plugin\\TacticalRMMRemote\\ComputerHook::postItemForm',
   ];
}

function plugin_version_tacticalrmmremote() {
   return [
      'name'         => __('TacticalRMM Remote', 'tacticalrmmremote'),
      'version'      => PLUGIN_TACTICALRMMREMOTE_VERSION,
      'author'       => 'z0gg',
      'license'      => 'GPLv2+',
      'homepage'     => Plugin\TacticalRMMRemote\Config::GITHUB_URL,
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_TACTICALRMMREMOTE_MIN_GLPI,
            'max' => PLUGIN_TACTICALRMMREMOTE_MAX_GLPI,
         ]
      ]
   ];
}

function plugin_tacticalrmmremote_check_prerequisites() {
   return version_compare(GLPI_VERSION, PLUGIN_TACTICALRMMREMOTE_MIN_GLPI, '>=')
      && version_compare(GLPI_VERSION, PLUGIN_TACTICALRMMREMOTE_MAX_GLPI, '<=');
}

function plugin_tacticalrmmremote_check_config($verbose = false) {
   return true;
}

function plugin_tacticalrmmremote_have_config() {
   return true;
}

function plugin_tacticalrmmremote_getConfigPage() {
   return 'front/config.php';
}
