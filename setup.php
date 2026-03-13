<?php

use Glpi\Plugin\Hooks;

define('PLUGIN_TACTICALRMMREMOTE_VERSION', '0.2.0');
define('PLUGIN_TACTICALRMMREMOTE_MIN_GLPI', '10.0.0');
define('PLUGIN_TACTICALRMMREMOTE_MAX_GLPI', '11.99.99');

function plugin_init_tacticalrmmremote() {
   global $PLUGIN_HOOKS;

   // Ensure plugin classes are available even when Composer autoload is not installed.
   $class_files = [
      __DIR__ . '/inc/Config.php',
      __DIR__ . '/inc/RemoteResolver.php',
      __DIR__ . '/inc/ComputerHook.php',
   ];

   foreach ($class_files as $file) {
      if (is_readable($file)) {
         require_once $file;
      }
   }

   $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['tacticalrmmremote'] = true;

   // Compatibility with multiple GLPI branches: use legacy string keys.
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
      'homepage'     => 'https://github.com/z0gg/glpi-tacticalrmmremote',
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
   return 'front/config.form.php';
}
