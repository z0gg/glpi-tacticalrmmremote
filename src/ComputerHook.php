<?php

namespace Plugin\TacticalRMMRemote;

class ComputerHook {
   /**
    * GLPI display hook: append a row to the Computer form when a remote ID exists.
    *
    * @param array $params
    */
   public static function postItemForm(array $params): void {
      $item = $params['item'] ?? null;
      if (!($item instanceof \Computer) || empty($item->fields['id'])) {
         return;
      }

      $remote_id = RemoteResolver::resolveFromComputer($item);
      if ($remote_id === null) {
         return;
      }

      $takecontrol_url = Config::buildTakeControlUrl($remote_id);
      if ($takecontrol_url === null) {
         return;
      }

      $container_id = 'plugin-tacticalrmmremote-action';
      $label = __('Open in TacticalRMM', 'tacticalrmmremote');
      $title = __('Open TacticalRMM Take Control session in a new tab', 'tacticalrmmremote');
      $details = __('Detected remote identifier', 'tacticalrmmremote') . ': ' . $remote_id;
      $js_container_id = json_encode($container_id, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
      $js_remote_id = json_encode($remote_id, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
      $js_takecontrol_url = json_encode($takecontrol_url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
      $js_title = json_encode($title, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

      echo "<div id='" . htmlspecialchars($container_id, ENT_QUOTES) . "' class='my-2'>";
      echo "<a class='btn btn-primary' href='" . htmlspecialchars($takecontrol_url, ENT_QUOTES) . "' target='_blank' rel='noopener noreferrer' title='" . htmlspecialchars($title, ENT_QUOTES) . "'>";
      echo "<i class='ti ti-device-desktop-share me-1'></i>" . htmlspecialchars($label, ENT_QUOTES);
      echo "</a>";
      echo "<div class='text-muted mt-2'><small>" . htmlspecialchars($details, ENT_QUOTES) . "</small></div>";
      echo "</div>";

      echo "<script>
      (function() {
         var containerId = " . $js_container_id . ";
         var remoteId = " . $js_remote_id . ";
         var targetUrl = " . $js_takecontrol_url . ";
         var linkTitle = " . $js_title . ";

         function getActionAnchor() {
            var candidates = Array.prototype.slice.call(document.querySelectorAll('button, a, .btn, .dropdown-toggle'));
            for (var i = 0; i < candidates.length; i++) {
               var text = (candidates[i].textContent || '').trim().toLowerCase();
               if (text === 'actions' || text.indexOf('actions') !== -1) {
                  return candidates[i];
               }
            }
            return null;
         }

         function placeButton() {
            var container = document.getElementById(containerId);
            if (!container) {
               return;
            }

            var actionAnchor = getActionAnchor();
            if (!actionAnchor || !actionAnchor.parentElement) {
               return;
            }

            var insertionTarget = actionAnchor.closest('.dropdown, .btn-group') || actionAnchor;
            var parent = insertionTarget.parentElement;
            if (!parent) {
               return;
            }

            if (container.parentElement !== parent || container.nextElementSibling !== insertionTarget) {
               parent.insertBefore(container, insertionTarget);
            }

            container.classList.add('me-2');
         }

         function linkifyRemoteManagementRow() {
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
            linkifyRemoteManagementRow();
         }

         refresh();

         if (!window.pluginTacticalRmmRemoteObserver) {
            window.pluginTacticalRmmRemoteObserver = new MutationObserver(function() {
               refresh();
            });
            window.pluginTacticalRmmRemoteObserver.observe(document.body, {childList: true, subtree: true});
         }
      })();
      </script>";
   }
}
