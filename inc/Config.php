<?php

namespace Plugin\TacticalRMMRemote;

class Config {
   public const CONTEXT = 'plugin:tacticalrmmremote';
   public const KEY_BASE_URL = 'base_url';
   public const DEFAULT_BASE_URL = '';

   public static function getBaseUrl(): string {
      $config = \Config::getConfigurationValues(self::CONTEXT);
      $value = $config[self::KEY_BASE_URL] ?? self::DEFAULT_BASE_URL;

      return self::normalizeBaseUrl((string)$value);
   }

   public static function saveBaseUrl(string $base_url): void {
      \Config::setConfigurationValues(self::CONTEXT, [
         self::KEY_BASE_URL => self::normalizeBaseUrl($base_url),
      ]);
   }

   public static function buildTakeControlUrl(string $remote_id): ?string {
      $base_url = self::getBaseUrl();
      $remote_id = trim($remote_id);
      if ($base_url === '' || $remote_id === '') {
         return null;
      }

      return sprintf('%s/takecontrol/%s', $base_url, rawurlencode($remote_id));
   }

   private static function normalizeBaseUrl(string $base_url): string {
      $base_url = trim($base_url);
      if ($base_url === '') {
         return '';
      }

      return rtrim($base_url, '/');
   }
}
