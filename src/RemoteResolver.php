<?php

namespace Plugin\TacticalRMMRemote;

class RemoteResolver {
   /**
    * Resolve a TacticalRMM remote identifier from known fields or inventory tables.
    */
   public static function resolveFromComputer(\Computer $computer): ?string {
      $direct_fields = [
         'tacticalrmm_remote_id',
         'tacticalrmm_id',
         'remote_id',
         'remoteid',
      ];

      foreach ($direct_fields as $field) {
         if (!empty($computer->fields[$field])) {
            return self::normalizeRemoteValue((string)$computer->fields[$field]);
         }
      }

      return self::resolveFromRemoteManagementTables((int)$computer->fields['id']);
   }

   private static function resolveFromRemoteManagementTables(int $computer_id): ?string {
      global $DB;

      $table_candidates = [
         'glpi_remotemanagements',
         'glpi_remote_managements',
      ];

      foreach ($table_candidates as $table) {
         if (!$DB->tableExists($table)) {
            continue;
         }

         $fields = array_keys($DB->listFields($table));
         if (!in_array('items_id', $fields, true) || !in_array('itemtype', $fields, true)) {
            continue;
         }

         $criteria = [
            'items_id' => $computer_id,
            'itemtype' => 'Computer',
         ];

         $iterator = $DB->request([
            'FROM'  => $table,
            'WHERE' => $criteria,
         ]);

         foreach ($iterator as $row) {
            if (!self::looksLikeTacticalRmmRow($row)) {
               continue;
            }

            $remote_id = self::extractRemoteIdFromRow($row);
            if ($remote_id !== null) {
               return $remote_id;
            }
         }
      }

      return null;
   }

   private static function looksLikeTacticalRmmRow(array $row): bool {
      $positive_patterns = [
         'tactical',
         'tacticalrmm',
         'takecontrol',
         'meshcentral',
         'mesh',
      ];

      foreach (['name', 'type', 'provider', 'tool'] as $field) {
         if (empty($row[$field])) {
            continue;
         }

         $value = strtolower((string)$row[$field]);
         foreach ($positive_patterns as $pattern) {
            if (str_contains($value, $pattern)) {
               return true;
            }
         }
      }

      foreach (['url', 'link'] as $field) {
         if (empty($row[$field])) {
            continue;
         }

         $value = strtolower((string)$row[$field]);
         if (str_contains($value, '/api/') || str_contains($value, 'inventory')) {
            return false;
         }

         foreach ($positive_patterns as $pattern) {
            if (str_contains($value, $pattern)) {
               return true;
            }
         }
      }

      return !empty($row['remoteid']) || !empty($row['remote_id']);
   }

   private static function extractRemoteIdFromRow(array $row): ?string {
      $id_candidates = [
         'remoteid',
         'remote_id',
         'value',
         'uid',
         'identifier',
         'url',
         'link',
      ];

      foreach ($id_candidates as $field) {
         if (empty($row[$field])) {
            continue;
         }

         $value = self::normalizeRemoteValue((string)$row[$field]);
         if ($value !== null) {
            return $value;
         }
      }

      return null;
   }

   public static function normalizeRemoteValue(string $value): ?string {
      $value = trim($value);
      if ($value === '') {
         return null;
      }

      if (preg_match('/^https?:\/\//i', $value) !== 1) {
         return $value;
      }

      return self::extractIdentifierFromUrl($value) ?? $value;
   }

   private static function extractIdentifierFromUrl(string $value): ?string {
      if (preg_match('/^https?:\/\//i', $value) !== 1) {
         return null;
      }

      $path = (string)(parse_url($value, PHP_URL_PATH) ?? '');
      if ($path === '') {
         return null;
      }

      $segments = array_values(array_filter(explode('/', trim($path, '/')), static function ($segment) {
         return $segment !== '';
      }));
      if ($segments === []) {
         return null;
      }

      $ignored_segments = [
         'api',
         'v1',
         'v2',
         'v3',
         'agents',
         'agent',
         'inventory',
         'mesh',
         'meshcentral',
         'control',
         'takecontrol',
         'remote',
      ];

      for ($index = count($segments) - 1; $index >= 0; $index--) {
         $segment = rawurldecode((string)$segments[$index]);
         if ($segment === '') {
            continue;
         }

         if (in_array(strtolower($segment), $ignored_segments, true)) {
            continue;
         }

         return $segment;
      }

      return null;
   }
}
