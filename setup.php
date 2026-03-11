<?php

use Glpi\Plugin\Hooks;

define('PLUGIN_TACTICALRMMREMOTE_VERSION', '0.1.0');
define('PLUGIN_TACTICALRMMREMOTE_MIN_GLPI', '10.0.0');
define('PLUGIN_TACTICALRMMREMOTE_MAX_GLPI', '11.99.99');

function plugin_init_tacticalrmmremote() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['tacticalrmmremote'] = true;

   // Add your GLPI hooks here
   // Example:
   // $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['tacticalrmmremote'] = [
   //    'Computer' => 'Plugin\\TacticalRMMRemote\\ComputerHook::preItemForm'
   // ];
}

function plugin_version_tacticalrmmremote() {
   return [
      'name'         => 'TacticalRMM Remote',
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
   return true;
}

function plugin_tacticalrmmremote_check_config($verbose = false) {
   return true;
}
