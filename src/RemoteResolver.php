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
            return trim((string)$computer->fields[$field]);
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
      foreach (['name', 'type', 'provider', 'tool'] as $field) {
         if (!empty($row[$field]) && stripos((string)$row[$field], 'tactical') !== false) {
            return true;
         }
      }

      return true;
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

         $value = trim((string)$row[$field]);
         if ($value === '') {
            continue;
         }

         if (in_array($field, ['url', 'link'], true)) {
            $identifier = self::extractIdentifierFromUrl($value);
            if ($identifier !== null) {
               return $identifier;
            }
         }

         return $value;
      }

      return null;
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

      $last_segment = end($segments);
      if (!is_string($last_segment) || $last_segment === '') {
         return null;
      }

      return rawurldecode($last_segment);
   }
}
