<?php

class PluginTrademarkTheme {

   public static function getThemeFolder() {
      return dirname(__DIR__) . '/themes';
   }

   public static function isLoginTheme($dir) {
      $path = static::getThemeFolder() . '/' . $dir . '/login.scss';
      return file_exists($path);
   }

   public static function getThemePath($dir, $type) {
      $path = static::getThemeFolder() . '/' . $dir . '/' . $type;
      if (!file_exists($path)) {
         return false;
      }
      return $dir . '/' . $type;
   }

   public static function getThemeInfo($dir) {
      $path = static::getThemeFolder() . '/' . $dir . '/theme.json';
      if (!file_exists($path)) {
         return false;
      }

      $json = file_get_contents($path);

      if (!$json) {
         return false;
      }

      $info = @json_decode($json, true);

      if (!$info) {
         return false;
      }
      $info['id'] = $dir;

      if (static::getThemePath($dir, 'login.preview.jpg')) {
         $info['login-preview'] = 'login.preview.jpg';
      }
      if (static::getThemePath($dir, 'login.background.jpg')) {
         $info['login-background'] = 'login.background.jpg';
      }

      return $info;
   }

   public static function getLoginThemes() {
      $themes = [];
      $dirs = scandir(static::getThemeFolder());

      foreach ($dirs as $dir) {
         if (!static::isLoginTheme($dir)) {
            continue;
         }
         $info = static::getThemeInfo($dir);
         if (!$info) {
            continue;
         }
         $themes[$dir] = $info;
      }

      return $themes;
   }
}
