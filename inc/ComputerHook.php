<?php

namespace Plugin\TacticalRMMRemote;

class ComputerHook {
   /**
    * Display TacticalRMM action after the computer form.
    *
    * @param \Computer $item
    * @param array      $options
    */
   public static function postItemForm($item, array $options = []): void {
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

      $label = __('Open in TacticalRMM', 'tacticalrmmremote');
      $title = __('Open TacticalRMM Take Control session in a new tab', 'tacticalrmmremote');

      echo "<div id='plugin-tacticalrmmremote-action' class='my-2'>";
      echo "<a class='btn btn-primary' href='" . htmlspecialchars($takecontrol_url, ENT_QUOTES) . "' target='_blank' rel='noopener noreferrer' title='" . htmlspecialchars($title, ENT_QUOTES) . "'>";
      echo "<i class='ti ti-rectangle-vertical-filled me-1'></i>" . htmlspecialchars($label, ENT_QUOTES);
      echo "</a>";
      echo '</div>';

      // Improve visibility for read-only profiles by moving the button near top action areas when available.
      echo "<script>
      (function(){
         var action = document.getElementById('plugin-tacticalrmmremote-action');
         if (!action) { return; }
         var target = document.querySelector('.asset .header-title')
            || document.querySelector('.card-header')
            || document.querySelector('.mainpage .center');
         if (target) {
            target.appendChild(action);
         }
      })();
      </script>";
   }
}
