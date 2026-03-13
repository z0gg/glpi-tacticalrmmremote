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

      global $CFG_GLPI;

      $launch_url = $CFG_GLPI['root_doc'] . '/plugins/tacticalrmmremote/front/remote.php?computer_id=' . (int)$item->fields['id'];

      $label = __('Open in TacticalRMM', 'tacticalrmmremote');
      $title = __('Open TacticalRMM Take Control session in a new tab', 'tacticalrmmremote');
      $details = __('Detected remote identifier', 'tacticalrmmremote') . ': ' . $remote_id;

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . htmlspecialchars(__('TacticalRMM remote access', 'tacticalrmmremote'), ENT_QUOTES) . "</td>";
      echo "<td>";
      echo "<a class='btn btn-primary' href='" . htmlspecialchars($launch_url, ENT_QUOTES) . "' target='_blank' rel='noopener noreferrer' title='" . htmlspecialchars($title, ENT_QUOTES) . "'>";
      echo "<i class='ti ti-device-desktop-share me-1'></i>" . htmlspecialchars($label, ENT_QUOTES);
      echo "</a>";
      echo "<div class='text-muted mt-2'><small>" . htmlspecialchars($details, ENT_QUOTES) . "</small></div>";
      echo "</td>";
      echo "</tr>";
   }
}
