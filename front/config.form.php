<?php

use Plugin\TacticalRMMRemote\Config;

include('../../../inc/includes.php');

Session::checkRight('config', READ);

if (isset($_POST['update'])) {
   Session::checkRight('config', UPDATE);
   Html::checkCSRF();

   $base_url = (string)($_POST['base_url'] ?? '');
   $url_template = (string)($_POST['url_template'] ?? Config::DEFAULT_URL_TEMPLATE);
   Config::saveSettings($base_url, $url_template);
   Session::addMessageAfterRedirect(__('Configuration saved'));
   Html::redirect($CFG_GLPI['root_doc'] . '/plugins/tacticalrmmremote/front/config.php');
}

Html::header(__('TacticalRMM Remote', 'tacticalrmmremote'), $_SERVER['PHP_SELF'], 'config', 'plugins');

$base_url = Config::getBaseUrl();
$url_template = Config::getUrlTemplate();
$resource_links = Config::getResourceLinks();

echo "<form method='post' action='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<div class='center' style='max-width: 820px'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>" . htmlspecialchars(__('TacticalRMM settings', 'tacticalrmmremote'), ENT_QUOTES) . "</th></tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>" . htmlspecialchars(__('TacticalRMM base URL', 'tacticalrmmremote'), ENT_QUOTES) . "</td>";
echo "<td><input type='url' class='form-control' name='base_url' value='" . htmlspecialchars($base_url, ENT_QUOTES) . "' placeholder='https://rmm.example.com' size='60'></td>";
echo "</tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>" . htmlspecialchars(__('Remote URL template', 'tacticalrmmremote'), ENT_QUOTES) . "</td>";
echo "<td><input type='text' class='form-control' name='url_template' value='" . htmlspecialchars($url_template, ENT_QUOTES) . "' placeholder='/takecontrol/{id}' size='60'>";
echo "<small class='text-muted d-block mt-1'>" . htmlspecialchars(__('Use {id} for URL-encoded ID or {raw_id} for raw value. Put a full URL to support MeshCentral links.', 'tacticalrmmremote'), ENT_QUOTES) . "</small></td>";
echo "</tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>" . htmlspecialchars(__('Resources', 'tacticalrmmremote'), ENT_QUOTES) . "</td>";
echo "<td>";
foreach ($resource_links as $link) {
   echo "<a class='btn btn-outline-secondary me-2 mb-2' target='_blank' rel='noopener noreferrer' href='" . htmlspecialchars($link['url'], ENT_QUOTES) . "' title='" . htmlspecialchars($link['title'], ENT_QUOTES) . "'>";
   echo "<i class='" . htmlspecialchars($link['icon'], ENT_QUOTES) . " me-1'></i>";
   echo htmlspecialchars($link['label'], ENT_QUOTES);
   echo "</a>";
}
echo "<div class='text-muted'><small>" . htmlspecialchars(__('Project links and documentation for this plugin.', 'tacticalrmmremote'), ENT_QUOTES) . "</small></div>";
echo "</td>";
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
