<?php

class PluginTrademarkTheme {

   /** Theme folder for server-side files (theme.json, login.scss) */
   public static function getThemeFolder() {
      return dirname(__DIR__) . '/themes';
   }

   /** Public folder for theme assets exposed in URLs (images), per GLPI Firewall */
   public static function getThemePublicFolder() {
      return dirname(__DIR__) . '/public/themes';
   }

   /** Image types served from public/themes; others from themes/ */
   private static function getThemeImageTypes() {
      return ['login.background.jpg', 'login.logo.png', 'login.preview.jpg'];
   }

   public static function isLoginTheme($dir) {
      $path = static::getThemeFolder() . '/' . $dir . '/login.scss';
      return file_exists($path);
   }

   public static function getThemePath($dir, $type) {
      $folder = in_array($type, static::getThemeImageTypes(), true)
         ? static::getThemePublicFolder()
         : static::getThemeFolder();
      $path = $folder . '/' . $dir . '/' . $type;
      if (!file_exists($path)) {
         return false;
      }
      return $dir . '/' . $type;
   }

   public static function getThemeInfo($dir) {
      $dirPath = static::getThemeFolder() . '/' . $dir;
      $path = $dirPath . '/theme.json';
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
      $info['path'] = $dirPath;

      if (static::getThemePath($dir, 'login.background.jpg')) {
         $info['login-background'] = 'login.background.jpg';
      }
      if (static::getThemePath($dir, 'login.logo.png')) {
         $info['login-logo'] = 'login.logo.png';
      }
      if (static::getThemePath($dir, 'login.preview.jpg')) {
         $info['login-preview'] = 'login.preview.jpg';
      }
      if (static::getThemePath($dir, 'login.scss')) {
         $info['login-scss'] = 'login.scss';
      } else if (static::getThemePath($dir, 'login.css')) {
         $info['login-css'] = 'login.css';
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
