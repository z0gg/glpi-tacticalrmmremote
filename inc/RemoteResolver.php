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

      // If no explicit provider exists, still allow extraction as a fallback.
      return true;
   }

   private static function extractRemoteIdFromRow(array $row): ?string {
      $id_candidates = [
         'url',
         'link',
         'remoteid',
         'remote_id',
         'value',
         'uid',
         'identifier',
      ];

      foreach ($id_candidates as $field) {
         if (!empty($row[$field])) {
            return trim((string)$row[$field]);
         }
      }

      return null;
   }
}
