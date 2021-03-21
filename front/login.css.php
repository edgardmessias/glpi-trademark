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
   if (isset($_GET['theme'])) {
      $url .= '&theme=' . $_GET['theme'];
   }
   Html::redirect($url, 302);
   die;
}

$css = PluginTrademarkScss::getLoginCSS(isset($_GET['theme']) ? $_GET['theme'] : null);

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
