<?php

namespace Plugin\TacticalRMMRemote;

class Config {
   public const CONTEXT = 'plugin:tacticalrmmremote';
   public const KEY_BASE_URL = 'base_url';
   public const KEY_URL_TEMPLATE = 'url_template';
   public const DEFAULT_BASE_URL = '';
   public const DEFAULT_URL_TEMPLATE = '/takecontrol/{id}';
   public const GITHUB_URL = 'https://github.com/z0gg/glpi-tacticalrmmremote';
   public const ISSUES_URL = 'https://github.com/z0gg/glpi-tacticalrmmremote/issues';
   public const README_URL = 'https://github.com/z0gg/glpi-tacticalrmmremote#readme';

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

   public static function getUrlTemplate(): string {
      $config = \Config::getConfigurationValues(self::CONTEXT);
      $value = trim((string)($config[self::KEY_URL_TEMPLATE] ?? self::DEFAULT_URL_TEMPLATE));

      return $value !== '' ? $value : self::DEFAULT_URL_TEMPLATE;
   }

   public static function saveUrlTemplate(string $template): void {
      $template = trim($template);
      if ($template === '') {
         $template = self::DEFAULT_URL_TEMPLATE;
      }

      \Config::setConfigurationValues(self::CONTEXT, [
         self::KEY_URL_TEMPLATE => $template,
      ]);
   }

   public static function saveSettings(string $base_url, string $url_template): void {
      $url_template = trim($url_template);
      if ($url_template === '') {
         $url_template = self::DEFAULT_URL_TEMPLATE;
      }

      \Config::setConfigurationValues(self::CONTEXT, [
         self::KEY_BASE_URL     => self::normalizeBaseUrl($base_url),
         self::KEY_URL_TEMPLATE => $url_template,
      ]);
   }

   public static function buildTakeControlUrl(string $remote_id): ?string {
      $remote_id = trim($remote_id);
      if ($remote_id === '') {
         return null;
      }

      if (preg_match('/^https?:\/\//i', $remote_id) === 1) {
         return $remote_id;
      }

      $base_url = self::getBaseUrl();
      $template = self::getUrlTemplate();
      if ($base_url === '' && preg_match('/^https?:\/\//i', $template) !== 1) {
         return null;
      }

      $target = str_replace(
         ['{id}', '{raw_id}'],
         [rawurlencode($remote_id), $remote_id],
         $template
      );

      if (preg_match('/^https?:\/\//i', $target) === 1) {
         return $target;
      }

      if ($base_url === '') {
         return null;
      }

      if ($target === '') {
         return $base_url;
      }

      return sprintf('%s/%s', rtrim($base_url, '/'), ltrim($target, '/'));
   }

   public static function getMenuName(): string {
      return __('TacticalRMM Remote', 'tacticalrmmremote');
   }

   public static function getMenuContent(): array {
      return [
         'title' => self::getMenuName(),
         'page'  => '/plugins/tacticalrmmremote/front/config.php',
         'icon'  => 'ti ti-device-desktop-share',
      ];
   }

   private static function normalizeBaseUrl(string $base_url): string {
      $base_url = trim($base_url);
      if ($base_url === '') {
         return '';
      }

      return rtrim($base_url, '/');
   }
}
