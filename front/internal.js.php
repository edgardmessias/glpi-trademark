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

header('Content-Type: application/javascript');

$is_cacheable = !isset($_GET['debug']) && !isset($_GET['nocache']);
if ($is_cacheable) {
   // Makes CSS cacheable by browsers and proxies
   $max_age = WEEK_TIMESTAMP;
   header_remove('Pragma');
   header('Cache-Control: public');
   header('Cache-Control: max-age=' . $max_age);
   header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $max_age));
}

if (false) {
   ?><script>
   <?php
}
?>
      <?php echo "$(function () {"; ?>
      $('link[href*="trademark/front/internal.css.php"]').appendTo($('head'))
      <?php
      $favicon = PluginTrademarkConfig::getConfig('favicon_picture');
      if ($favicon) :
         $faviconUrl = PluginTrademarkToolbox::getPictureUrl($favicon);
         ?>
         var $icon = $('link[rel*=icon]');
         $icon.attr('type', null);
         $icon.attr('href', <?php echo json_encode($faviconUrl) ?>);
         <?php
      endif;
      $pageTitle = PluginTrademarkConfig::getConfig('page_title');
      if ($pageTitle) :
         ?>
         var $title = $('title');
         var newTitle = $title.text().replace('GLPI', <?php echo json_encode($pageTitle) ?>);
         $title.text(newTitle);
         <?php
      endif;

      $footerDisplay = PluginTrademarkConfig::getConfig('page_footer_display', 'original');
      $footerText = PluginTrademarkConfig::getConfig('page_footer_text', '');
      if ($footerDisplay === 'hide') :
         ?>
         $('a[id^=show_about_modal_]').hide();
         <?php
      endif;
      if ($footerDisplay === 'custom') :
         $footerText = \Glpi\RichText\RichText::getEnhancedHtml($footerText);
         ?>
         $('div[id^=about_modal_] .copyright').parent().parent().html(<?php echo json_encode($footerText) ?>);
         <?php
      endif;

      echo "});";
      if (false) {
         ?>
   </script>
         <?php
      }
