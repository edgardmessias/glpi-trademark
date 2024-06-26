<?php

$_GET["donotcheckversion"]   = true;
$dont_check_maintenance_mode = true;

include('../../../inc/includes.php');

// Redirect if is a not cached URL
if (!isset($_GET['_'])) {
   $timestamp = PluginTrademarkToolbox::getTimestamp();

   // Disable cache and redirect to cached URL
   header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
   header("Cache-Control: post-check=0, pre-check=0", false);
   header("Pragma: no-cache");

   $file = basename(__FILE__);
   $url = "$file?_=$timestamp";
   if (isset($_GET['v'])) {
      $url .= '&v=' . $_GET['v'];
   }
   Html::redirect($url, 302);
   die;
}

$name = 'internal';
$css = "";

$picture = PluginTrademarkConfig::getConfig("{$name}_picture", '');
if ($picture) {
   $css .= ".page .glpi-logo {";
   $css .= " width: " . PluginTrademarkConfig::getConfig("{$name}_picture_width", '100px') . " !important;";
   $css .= " height: " . PluginTrademarkConfig::getConfig("{$name}_picture_height", '55px') . " !important;";
   $css .= " background-size: contain !important;";
   $css .= " background-repeat: no-repeat !important;";
   $css .= " background-position: center !important;";
   $css .= " background-image: url(\"" . PluginTrademarkToolbox::getPictureUrl($picture) . "\") !important;";
   $css .= "}";
}

$css_type = PluginTrademarkConfig::getConfig("{$name}_css_type", 'scss');
$css_custom = PluginTrademarkConfig::getConfig("{$name}_css_custom", '');

$css_custom = html_entity_decode($css_custom);

if ($css_type === 'scss' && $css_custom && PluginTrademarkScss::hasScssSuport()) {
   try {
      $variables = [];
      $variables['trademark_timestamp'] = PluginTrademarkToolbox::getTimestamp();

      $css .= PluginTrademarkScss::compileScss($css_custom, $variables);
   } catch (\Throwable $th) {
      \Glpi\Application\ErrorHandler::getInstance()->handleException($th);
   }
} else if ($css_type === 'css') {
   $css .= $css_custom;
}

header('Content-Type: text/css');

$is_cacheable = !isset($_GET['debug']) && !isset($_GET['nocache']);
if ($is_cacheable) {
   // Makes CSS cacheable by browsers and proxies
   $max_age = WEEK_TIMESTAMP;
   header_remove('Pragma');
   header('Cache-Control: public');
   header('Cache-Control: max-age=' . $max_age);
   header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $max_age));
}

echo $css;
