<?php

function plugin_tacticalrmmremote_get_context() {
   return 'plugin:tacticalrmmremote';
}

function plugin_tacticalrmmremote_get_default_template() {
   return '/takecontrol/{id}';
}

function plugin_tacticalrmmremote_get_config_file_path() {
   return dirname(__DIR__) . '/config.cfg';
}

function plugin_tacticalrmmremote_load_file_config() {
   static $cache = null;

   if (is_array($cache)) {
      return $cache;
   }

   $cache = [];
   $path = plugin_tacticalrmmremote_get_config_file_path();
   if (!is_readable($path)) {
      return $cache;
   }

   $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
   if (!is_array($lines)) {
      return $cache;
   }

   foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '') {
         continue;
      }

      $first_char = substr($line, 0, 1);
      if ($first_char === '#' || $first_char === ';') {
         continue;
      }

      $separator_pos = strpos($line, '=');
      if ($separator_pos === false) {
         continue;
      }

      $key = trim(substr($line, 0, $separator_pos));
      $value = trim(substr($line, $separator_pos + 1));
      if ($key !== '') {
         $cache[$key] = $value;
      }
   }

   return $cache;
}

function plugin_tacticalrmmremote_normalize_base_url($base_url) {
   $base_url = trim((string)$base_url);
   if ($base_url === '') {
      return '';
   }

   return rtrim($base_url, '/');
}

function plugin_tacticalrmmremote_get_base_url() {
   $file_config = plugin_tacticalrmmremote_load_file_config();
   if (!empty($file_config['base_url'])) {
      return plugin_tacticalrmmremote_normalize_base_url($file_config['base_url']);
   }

   $config = \Config::getConfigurationValues(plugin_tacticalrmmremote_get_context());
   return plugin_tacticalrmmremote_normalize_base_url(isset($config['base_url']) ? $config['base_url'] : '');
}

function plugin_tacticalrmmremote_get_url_template() {
   $default_template = plugin_tacticalrmmremote_get_default_template();
   $file_config = plugin_tacticalrmmremote_load_file_config();
   if (!empty($file_config['url_template'])) {
      $template = trim((string)$file_config['url_template']);
      return $template !== '' ? $template : $default_template;
   }

   $config = \Config::getConfigurationValues(plugin_tacticalrmmremote_get_context());
   $template = isset($config['url_template']) ? trim((string)$config['url_template']) : $default_template;
   return $template !== '' ? $template : $default_template;
}

function plugin_tacticalrmmremote_has_file_override() {
   $file_config = plugin_tacticalrmmremote_load_file_config();
   return !empty($file_config['base_url']) || !empty($file_config['url_template']);
}

function plugin_tacticalrmmremote_save_settings($base_url, $url_template) {
   $template = trim((string)$url_template);
   if ($template === '') {
      $template = plugin_tacticalrmmremote_get_default_template();
   }

   \Config::setConfigurationValues(plugin_tacticalrmmremote_get_context(), [
      'base_url'     => plugin_tacticalrmmremote_normalize_base_url($base_url),
      'url_template' => $template,
   ]);
}

function plugin_tacticalrmmremote_install_impl() {
   $context = plugin_tacticalrmmremote_get_context();
   $current = \Config::getConfigurationValues($context);

   if (!isset($current['base_url']) || !isset($current['url_template'])) {
      \Config::setConfigurationValues($context, [
         'base_url'     => plugin_tacticalrmmremote_normalize_base_url(isset($current['base_url']) ? $current['base_url'] : ''),
         'url_template' => isset($current['url_template']) && trim((string)$current['url_template']) !== ''
            ? trim((string)$current['url_template'])
            : plugin_tacticalrmmremote_get_default_template(),
      ]);
   }

   return true;
}

function plugin_tacticalrmmremote_uninstall_impl() {
   \Config::deleteConfigurationValues(plugin_tacticalrmmremote_get_context());
   return true;
}

function plugin_tacticalrmmremote_build_remote_url($remote_id) {
   $remote_id = trim((string)$remote_id);
   if ($remote_id === '') {
      return null;
   }

   if (preg_match('/^https?:\/\//i', $remote_id) === 1) {
      return $remote_id;
   }

   $template = plugin_tacticalrmmremote_get_url_template();
   $target = str_replace(
      ['{id}', '{raw_id}'],
      [rawurlencode($remote_id), $remote_id],
      $template
   );

   if (preg_match('/^https?:\/\//i', $target) === 1) {
      return $target;
   }

   $base_url = plugin_tacticalrmmremote_get_base_url();
   if ($base_url === '') {
      return null;
   }

   return rtrim($base_url, '/') . '/' . ltrim($target, '/');
}

function plugin_tacticalrmmremote_row_score($row) {
   $fields = ['type', 'provider', 'tool', 'comments', 'name'];

   foreach ($fields as $field) {
      if (empty($row[$field])) {
         continue;
      }

      $value = strtolower(trim((string)$row[$field]));
      if ($value === 'tacticalrmm') {
         return 1000;
      }
      if (strpos($value, 'tacticalrmm') !== false) {
         return 900;
      }
      if (strpos($value, 'tactical') !== false) {
         return 800;
      }
      if ($value === 'meshcentral' || $value === 'mesh') {
         return 200;
      }
      if (strpos($value, 'meshcentral') !== false || strpos($value, 'mesh') !== false) {
         return 150;
      }
   }

   return 0;
}

function plugin_tacticalrmmremote_extract_remote_id_from_row($row) {
   $fields = ['remoteid', 'remote_id', 'value', 'uid', 'identifier', 'name'];

   foreach ($fields as $field) {
      if (!empty($row[$field])) {
         $value = trim((string)$row[$field]);
         if ($value !== '') {
            return $value;
         }
      }
   }

   return null;
}

function plugin_tacticalrmmremote_resolve_remote_id($computer) {
   global $DB;

   $computer_id = isset($computer->fields['id']) ? (int)$computer->fields['id'] : 0;
   if ($computer_id > 0) {
      $tables = ['glpi_remotemanagements', 'glpi_remote_managements'];

      foreach ($tables as $table) {
         if (!$DB->tableExists($table)) {
            continue;
         }

         $fields = array_keys($DB->listFields($table));
         if (!in_array('items_id', $fields, true) || !in_array('itemtype', $fields, true)) {
            continue;
         }

         $iterator = $DB->request([
            'FROM'  => $table,
            'WHERE' => [
               'items_id' => $computer_id,
               'itemtype' => 'Computer',
            ],
         ]);

         $best_id = null;
         $best_score = -1;

         foreach ($iterator as $row) {
            $candidate = plugin_tacticalrmmremote_extract_remote_id_from_row($row);
            if ($candidate === null) {
               continue;
            }

            $score = plugin_tacticalrmmremote_row_score($row);
            if ($score > $best_score) {
               $best_score = $score;
               $best_id = $candidate;
            }
         }

         if ($best_id !== null) {
            return $best_id;
         }
      }
   }

   $direct_fields = ['tacticalrmm_remote_id', 'tacticalrmm_id', 'remote_id', 'remoteid'];
   foreach ($direct_fields as $field) {
      if (!empty($computer->fields[$field])) {
         $value = trim((string)$computer->fields[$field]);
         if ($value !== '') {
            return $value;
         }
      }
   }

   return null;
}

function plugin_tacticalrmmremote_post_item_form_impl($params) {
   $item = isset($params['item']) ? $params['item'] : null;
   if (!($item instanceof \Computer) || empty($item->fields['id'])) {
      return;
   }

   $remote_id = plugin_tacticalrmmremote_resolve_remote_id($item);
   if ($remote_id === null) {
      return;
   }

   $target_url = plugin_tacticalrmmremote_build_remote_url($remote_id);
   if ($target_url === null) {
      return;
   }

   $container_id = 'plugin-tacticalrmmremote-action-' . (int)$item->fields['id'];
   $label = __('Open in TacticalRMM', 'tacticalrmmremote');
   $title = __('Open TacticalRMM Take Control session in a new tab', 'tacticalrmmremote');
   $details = __('Detected remote identifier', 'tacticalrmmremote') . ': ' . $remote_id;

   $js_container_id = json_encode($container_id);
   $js_remote_id = json_encode($remote_id);
   $js_target_url = json_encode($target_url);
   $js_title = json_encode($title);

   echo "<div id='" . htmlspecialchars($container_id, ENT_QUOTES) . "' class='my-2'>";
   echo "<a class='btn btn-primary' href='" . htmlspecialchars($target_url, ENT_QUOTES) . "' target='_blank' rel='noopener noreferrer' title='" . htmlspecialchars($title, ENT_QUOTES) . "'>";
   echo "<i class='ti ti-device-desktop-share me-1'></i>" . htmlspecialchars($label, ENT_QUOTES);
   echo "</a>";
   echo "<div class='text-muted mt-2'><small>" . htmlspecialchars($details, ENT_QUOTES) . "</small></div>";
   echo "</div>";

   echo "<script>
   (function() {
      var containerId = " . $js_container_id . ";
      var remoteId = " . $js_remote_id . ";
      var targetUrl = " . $js_target_url . ";
      var linkTitle = " . $js_title . ";

      function findActionsElement() {
         var nodes = document.querySelectorAll('button, a, .btn, .dropdown-toggle');
         for (var i = 0; i < nodes.length; i++) {
            var text = (nodes[i].textContent || '').replace(/\\s+/g, ' ').trim().toLowerCase();
            if (text === 'actions' || text.indexOf('actions') !== -1 || text === 'action' || text.indexOf('action') !== -1) {
               return nodes[i];
            }
         }
         return null;
      }

      function placeButton() {
         var container = document.getElementById(containerId);
         if (!container) {
            return;
         }

         var actions = findActionsElement();
         if (!actions || !actions.parentElement) {
            return;
         }

         var insertionTarget = actions.closest('.dropdown, .btn-group') || actions;
         var parent = insertionTarget.parentElement;
         if (!parent) {
            return;
         }

         parent.insertBefore(container, insertionTarget);
         container.classList.add('me-2');
      }

      function linkifyRemoteManagement() {
         var rows = document.querySelectorAll('tr');
         for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var rowText = (row.textContent || '').toLowerCase();
            if (rowText.indexOf('tacticalrmm') === -1 || rowText.indexOf(remoteId.toLowerCase()) === -1) {
               continue;
            }

            var cells = row.querySelectorAll('td');
            for (var j = 0; j < cells.length; j++) {
               var cell = cells[j];
               if ((cell.textContent || '').trim() !== remoteId) {
                  continue;
               }

               if (cell.querySelector('a')) {
                  return;
               }

               var link = document.createElement('a');
               link.href = targetUrl;
               link.target = '_blank';
               link.rel = 'noopener noreferrer';
               link.title = linkTitle;
               link.textContent = remoteId;
               link.className = 'link-primary';

               cell.textContent = '';
               cell.appendChild(link);
               return;
            }
         }
      }

      function refresh() {
         placeButton();
         linkifyRemoteManagement();
      }

      refresh();

      if (!window.pluginTacticalRmmRemoteObserver) {
         window.pluginTacticalRmmRemoteObserver = new MutationObserver(function() {
            refresh();
         });
         window.pluginTacticalRmmRemoteObserver.observe(document.body, {
            childList: true,
            subtree: true
         });
      }
   })();
   </script>";
}
