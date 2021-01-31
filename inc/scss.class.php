<?php

class PluginTrademarkScss {

   static function getNamespace() {
      $namespace = 'ScssPhp\ScssPhp';
      $compiler = "$namespace\Compiler";

      if (!class_exists($compiler)) {
         $namespace = 'Leafo\ScssPhp';
         $compiler = "$namespace\Compiler";
      }
      if (!class_exists($compiler)) {
         return false;
      }

      return $namespace;
   }

   static function hasScssSuport() {
      $namespace = static::getNamespace();
      return !empty($namespace);
   }

   static function compileScss($content) {
      global $GLPI_CACHE;

      $namespace = static::getNamespace();

      if (!$namespace) {
         return '';
      }

      $compiler = "$namespace\Compiler";
      $formatter = "$namespace\Formatter\Crunched";

      $scss = new $compiler();
      $scss->setFormatter($formatter);
      $scss->addImportPath(GLPI_ROOT);

      $ckey = md5($content);

      if ($GLPI_CACHE->has($ckey) && !isset($_GET['reload']) && !isset($_GET['nocache'])) {
         $css = $GLPI_CACHE->get($ckey);
      } else {
         $css = $scss->compile($content);
         if (!isset($_GET['nocache'])) {
            $GLPI_CACHE->set($ckey, $css);
         }
      }

      return $css;
   }
}
