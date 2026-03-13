<?php

use Plugin\TacticalRMMRemote\Config;
use Plugin\TacticalRMMRemote\RemoteResolver;

include('../../../inc/includes.php');
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/RemoteResolver.php';

Session::checkLoginUser();

$computer_id = (int)($_GET['computer_id'] ?? 0);
if ($computer_id <= 0) {
   Html::header(__('TacticalRMM Remote', 'tacticalrmmremote'), $_SERVER['PHP_SELF'], 'assets', 'computer');
   echo "<div class='alert alert-danger'>" . htmlspecialchars(__('Missing or invalid Computer ID.', 'tacticalrmmremote'), ENT_QUOTES) . "</div>";
   Html::footer();
   exit;
}

$computer = new Computer();
if (!$computer->getFromDB($computer_id)) {
   Html::header(__('TacticalRMM Remote', 'tacticalrmmremote'), $_SERVER['PHP_SELF'], 'assets', 'computer');
   echo "<div class='alert alert-danger'>" . htmlspecialchars(__('Computer not found.', 'tacticalrmmremote'), ENT_QUOTES) . "</div>";
   Html::footer();
   exit;
}

$remote_id = RemoteResolver::resolveFromComputer($computer);
if ($remote_id === null) {
   Html::header(__('TacticalRMM Remote', 'tacticalrmmremote'), $_SERVER['PHP_SELF'], 'assets', 'computer');
   echo "<div class='alert alert-warning'>" . htmlspecialchars(__('No TacticalRMM remote identifier was found for this Computer.', 'tacticalrmmremote'), ENT_QUOTES) . "</div>";
   Html::footer();
   exit;
}

$inspection = Config::inspectTakeControlUrl($remote_id);
if ($inspection['url'] !== null && $inspection['issues'] === []) {
   header('Location: ' . $inspection['url']);
   exit;
}

$reasons = [];
foreach ($inspection['issues'] as $issue) {
   if ($issue === 'api_target') {
      $reasons[] = __('Target URL points to an API or inventory endpoint.', 'tacticalrmmremote');
   } elseif ($issue === 'host_mismatch') {
      $reasons[] = __('Target URL host does not match the configured TacticalRMM base URL.', 'tacticalrmmremote');
   } elseif ($issue === 'missing_target') {
      $reasons[] = __('Unable to build a valid TacticalRMM URL from the detected identifier.', 'tacticalrmmremote');
   }
}

global $CFG_GLPI;
$computer_url = $CFG_GLPI['root_doc'] . '/front/computer.form.php?id=' . $computer_id;

Html::header(__('TacticalRMM Remote', 'tacticalrmmremote'), $_SERVER['PHP_SELF'], 'assets', 'computer');

echo "<div class='center' style='max-width: 900px'>";
echo "<div class='alert alert-warning'>";
echo htmlspecialchars(__('The generated remote URL looks unsafe and was not opened automatically.', 'tacticalrmmremote'), ENT_QUOTES);
echo "</div>";

echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>" . htmlspecialchars(__('Remote launch blocked', 'tacticalrmmremote'), ENT_QUOTES) . "</th></tr>";
echo "<tr class='tab_bg_1'><td>" . htmlspecialchars(__('Resolved remote identifier', 'tacticalrmmremote'), ENT_QUOTES) . "</td><td><code>" . htmlspecialchars($remote_id, ENT_QUOTES) . "</code></td></tr>";
echo "<tr class='tab_bg_1'><td>" . htmlspecialchars(__('Generated remote URL', 'tacticalrmmremote'), ENT_QUOTES) . "</td><td><code>" . htmlspecialchars((string)($inspection['url'] ?? ''), ENT_QUOTES) . "</code></td></tr>";
echo "<tr class='tab_bg_1'><td>" . htmlspecialchars(__('Reason', 'tacticalrmmremote'), ENT_QUOTES) . "</td><td>";
if ($reasons === []) {
   echo htmlspecialchars(__('Unable to build a valid TacticalRMM URL from the detected identifier.', 'tacticalrmmremote'), ENT_QUOTES);
} else {
   foreach ($reasons as $reason) {
      echo "<div>" . htmlspecialchars($reason, ENT_QUOTES) . "</div>";
   }
}
echo "</td></tr>";
echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
echo "<a class='btn btn-secondary me-2' href='" . htmlspecialchars($computer_url, ENT_QUOTES) . "'>";
echo "<i class='ti ti-arrow-left me-1'></i>" . htmlspecialchars(__('Back to Computer', 'tacticalrmmremote'), ENT_QUOTES);
echo "</a>";
if (!empty($inspection['url'])) {
   echo "<a class='btn btn-outline-warning' target='_blank' rel='noopener noreferrer' href='" . htmlspecialchars($inspection['url'], ENT_QUOTES) . "'>";
   echo "<i class='ti ti-external-link me-1'></i>" . htmlspecialchars(__('Open anyway', 'tacticalrmmremote'), ENT_QUOTES);
   echo "</a>";
}
echo "</td></tr>";
echo "</table>";
echo "</div>";

Html::footer();
