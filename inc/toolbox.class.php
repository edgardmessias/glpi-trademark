<?php

class PluginTrademarkToolbox {

   static public function startsWith($haystack, $needle) {
      $length = strlen($needle);
      return (substr($haystack, 0, $length) === $needle);
   }

   static function getPictureUrl($path) {
      global $CFG_GLPI;

      $path = Html::cleanInputText($path); // prevent xss

      if (empty($path)) {
         return null;
      }

      return Html::getPrefixedUrl('/plugins/trademark/front/picture.send.php?path=' . $path);
   }

   static public function savePicture($src, $uniq_prefix = null) {
      $basePath = GLPI_PLUGIN_DOC_DIR . "/trademark";

      if (function_exists('Document::isPicture') && !Document::isPicture($src)) {
         return false;
      }

      $filename     = uniqid($uniq_prefix);
      $ext          = pathinfo($src, PATHINFO_EXTENSION);
      $subdirectory = substr($filename, -2); // subdirectory based on last 2 hex digit

      $i = 0;
      do {
         // Iterate on possible suffix while dest exists.
         // This case will almost never exists as dest is based on an unique id.
         $dest = $basePath
         . '/' . $subdirectory
         . '/' . $filename . ($i > 0 ? '_' . $i : '') . '.' . $ext;
         $i++;
      } while (file_exists($dest));

      if (!is_dir($basePath . '/' . $subdirectory) && !mkdir($basePath . '/' . $subdirectory, 0777, true)) {
         return false;
      }

      if (!rename($src, $dest)) {
         return false;
      }

      return substr($dest, strlen($basePath . '/')); // Return dest relative to GLPI_PICTURE_DIR
   }

   public static function deletePicture($path) {
      $basePath = GLPI_PLUGIN_DOC_DIR . "/trademark";
      $fullpath = $basePath . '/' . $path;

      if (!file_exists($fullpath)) {
         return false;
      }

      $fullpath = realpath($fullpath);
      if (!static::startsWith($fullpath, realpath($basePath))) {
         return false;
      }

      return @unlink($fullpath);
   }
}
