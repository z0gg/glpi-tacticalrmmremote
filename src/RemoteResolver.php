<?php

namespace Plugin\TacticalRMMRemote;

class RemoteResolver {
   /**
    * Resolve a TacticalRMM remote identifier from known fields or inventory tables.
    */
   public static function resolveFromComputer(\Computer $computer): ?string {
      $remote_id = self::resolveFromRemoteManagementTables((int)$computer->fields['id']);
      if ($remote_id !== null) {
         return $remote_id;
      }

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

      return null;
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

         $best_remote_id = null;
         $best_score = PHP_INT_MIN;

         foreach ($iterator as $row) {
            $remote_id = self::extractRemoteIdFromRow($row);
            if ($remote_id === null) {
               continue;
            }

            $score = self::scoreRemoteManagementRow($row);
            if ($score > $best_score) {
               $best_score = $score;
               $best_remote_id = $remote_id;
            }
         }

         if ($best_remote_id !== null) {
            return $best_remote_id;
         }
      }

      return null;
   }

   private static function scoreRemoteManagementRow(array $row): int {
      $signals = [];
      foreach (['type', 'provider', 'tool', 'name', 'comments'] as $field) {
         if (!empty($row[$field])) {
            $signals[] = strtolower(trim((string)$row[$field]));
         }
      }

      foreach ($signals as $signal) {
         if ($signal === 'tacticalrmm') {
            return 1000;
         }

         if (str_contains($signal, 'tacticalrmm')) {
            return 900;
         }

         if (str_contains($signal, 'tactical')) {
            return 800;
         }

         if ($signal === 'meshcentral' || $signal === 'mesh') {
            return 200;
         }

         if (str_contains($signal, 'meshcentral') || str_contains($signal, 'mesh')) {
            return 150;
         }
      }

      return 0;
   }

   private static function extractRemoteIdFromRow(array $row): ?string {
      $id_candidates = [
         'remoteid',
         'remote_id',
         'value',
         'uid',
         'identifier',
         'name',
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
      return $value !== '' ? $value : null;
   }
}
