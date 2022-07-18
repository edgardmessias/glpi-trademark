<?php

class PluginTrademarkScss {

   static function hasScssSuport() {
      return true;
   }

   static function compileScss($content, $variables = []) {
      global $GLPI_CACHE;

      $scss = new \ScssPhp\ScssPhp\Compiler();
      $scss->setOutputStyle("compressed");
      $scss->setSourceMap(\ScssPhp\ScssPhp\Compiler::SOURCE_MAP_NONE);
      $scss->addVariables($variables);
      $scss->addImportPath(GLPI_ROOT);

      $scss->addImportPath(
         function ($path) {
            $file_chunks = [];
            if (!preg_match('/^~@?(?<directory>.*)\/(?<file>[^\/]+)(?:(\.scss)?)/', $path, $file_chunks)) {
               return null;
            }

            $possible_filenames = [
               sprintf('%s/css/lib/%s/%s.scss', GLPI_ROOT, $file_chunks['directory'], $file_chunks['file']),
               sprintf('%s/css/lib/%s/_%s.scss', GLPI_ROOT, $file_chunks['directory'], $file_chunks['file']),
            ];
            foreach ($possible_filenames as $filename) {
               if (file_exists($filename)) {
                  return $filename;
               }
            }

            return null;
         }
      );

      $content = str_replace('\"', '"', $content);

      $ckey = md5($content . json_encode($variables));

      if ($GLPI_CACHE->has($ckey) && !isset($_GET['reload']) && !isset($_GET['nocache'])) {
         $css = $GLPI_CACHE->get($ckey);
      } else {
         $css = $scss->compileString($content, GLPI_ROOT . '/trademark.scss')->getCss();
         if (!isset($_GET['nocache'])) {
            $GLPI_CACHE->set($ckey, $css);
         }
      }

      return $css;
   }

   static function getLoginCSS($theme = null, $variables = []) {
      if (!$theme) {
         $theme = PluginTrademarkConfig::getConfig("login_theme", '');
      }
      $themeInfo = null;
      if ($theme) {
         $themeInfo = PluginTrademarkTheme::getThemeInfo($theme);
      }

      $picture = PluginTrademarkConfig::getConfig("login_background_picture", '');

      if (!$picture && $themeInfo && $themeInfo['login-background']) {
         $picture = $themeInfo['login-background'] . '&theme=' . $themeInfo['id'];
      }

      $css = '';
      if ($picture) {
         $css .= "#firstboxlogin, #text-login, #logo_login {";
         $css .= " background-color: transparent;";
         $css .= "}";
         $css .= "html {";
         $css .= " height: 100%;";
         $css .= "}";
         $css .= "body {";
         $css .= " background-size: cover;";
         $css .= " background-repeat: no-repeat;";
         $css .= " background-position: center;";
         $css .= " background-image: url(\"" . PluginTrademarkToolbox::getPictureUrl($picture) . "\");";
         $css .= "}";
      }

      $css_type = PluginTrademarkConfig::getConfig("login_css_type", 'scss');
      $css_custom = PluginTrademarkConfig::getConfig("login_css_custom", '');

      $css_custom = html_entity_decode($css_custom);

      if ($css_type === 'scss' && PluginTrademarkScss::hasScssSuport()) {
         $variables = [];
         if ($themeInfo && isset($themeInfo['variables'])) {
            foreach ($themeInfo['variables'] as $k => $v) {
               $themeId = $themeInfo['id'];
               $fieldName = "login_theme-$themeId-$k";
               $fieldValue = PluginTrademarkConfig::getConfig($fieldName, $v['default']);
               $variables[$k] = $fieldValue;
            }
         }

         if ($themeInfo && $themeInfo['login-scss']) {
            $scssPath = str_replace('\\', '/', $themeInfo['path'] . '/' . $themeInfo['login-scss']);
            $css_custom = "@import '" . $scssPath . "';\n" . $css_custom;
         }

         try {
            $css .= PluginTrademarkScss::compileScss($css_custom, $variables);
         } catch (\Throwable $th) {
            \Glpi\Application\ErrorHandler::getInstance()->handleException($th);
         }
      } else {
         if ($themeInfo && $themeInfo['login-css']) {
            $css .= file_get_contents($themeInfo['path'] . '/' . $themeInfo['login-css']) . "\n";
         }

         if ($css_type === 'css') {
            $css .= $css_custom;
         }
      }

      return $css;
   }
}
