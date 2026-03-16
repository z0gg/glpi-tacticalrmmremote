<?php

function plugin_tacticalrmmremote_require_functions() {
   require_once __DIR__ . '/inc/functions.php';
}

function plugin_tacticalrmmremote_post_item_form($params) {
   plugin_tacticalrmmremote_require_functions();
   plugin_tacticalrmmremote_post_item_form_impl($params);
}

function plugin_tacticalrmmremote_install() {
   plugin_tacticalrmmremote_require_functions();
   return plugin_tacticalrmmremote_install_impl();
}

function plugin_tacticalrmmremote_uninstall() {
   plugin_tacticalrmmremote_require_functions();
   return plugin_tacticalrmmremote_uninstall_impl();
}
