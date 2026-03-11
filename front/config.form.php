<?php

use Plugin\TacticalRMMRemote\Config;

include('../../../inc/includes.php');

Session::checkRight('config', READ);

if (isset($_POST['update'])) {
   Session::checkRight('config', UPDATE);
   Html::checkCSRF();

   $base_url = (string)($_POST['base_url'] ?? '');
   Config::saveBaseUrl($base_url);
   Session::addMessageAfterRedirect(__('Configuration saved'));
   Html::redirect($CFG_GLPI['root_doc'] . '/plugins/tacticalrmmremote/front/config.form.php');
}

Html::header(__('TacticalRMM Remote', 'tacticalrmmremote'), $_SERVER['PHP_SELF'], 'config', 'plugins');

$base_url = Config::getBaseUrl();

echo "<form method='post' action='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "'>";
echo "<div class='center' style='max-width: 820px'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>" . htmlspecialchars(__('TacticalRMM settings', 'tacticalrmmremote'), ENT_QUOTES) . "</th></tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>" . htmlspecialchars(__('TacticalRMM base URL', 'tacticalrmmremote'), ENT_QUOTES) . "</td>";
echo "<td><input type='url' class='form-control' name='base_url' value='" . htmlspecialchars($base_url, ENT_QUOTES) . "' placeholder='https://rmm.example.com' size='60'></td>";
echo "</tr>";
echo "<tr class='tab_bg_1'>";
echo "<td colspan='2' class='center'>";
echo "<button class='btn btn-primary' type='submit' name='update'>" . htmlspecialchars(_sx('button', 'Save'), ENT_QUOTES) . "</button>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
Html::closeForm();

Html::footer();
