<?php

function plugin_trademark_display_login() {

   $themeInfo = null;
   if (isset($_GET['theme'])) {
      $themeInfo = PluginTrademarkTheme::getThemeInfo($_GET['theme']);
   }
   if (!$themeInfo) {
      $theme = PluginTrademarkConfig::getConfig("login_theme", '');
      $themeInfo = PluginTrademarkTheme::getThemeInfo($theme);
   }

   $loginPicture = PluginTrademarkConfig::getConfig('login_picture');

   if (!$loginPicture && $themeInfo && isset($themeInfo['login-background'])) {
      $loginPicture = $themeInfo['login-logo'] . '&theme=' . $themeInfo['id'];
   }

   if ($loginPicture && version_compare(GLPI_VERSION, '9.5.0', '<')) {
      echo Html::css("/plugins/trademark/css/login.base.css", [
         'version' => PLUGIN_TRADEMARK_VERSION,
      ]);
   }

   $timestamp = PluginTrademarkToolbox::getTimestamp();

   $cssUrl = "/plugins/trademark/front/login.css.php?_=$timestamp";

   if (isset($_GET['theme'])) {
      $cssUrl .= "&theme=" . $_GET['theme'];
   }
   if (isset($_GET['nocache'])) {
      $cssUrl .= "&nocache=" . $_GET['nocache'];
   }

   echo Html::css($cssUrl, [
      'version' => PLUGIN_TRADEMARK_VERSION,
   ]);

   ?>
   <?php if ($loginPicture) : ?>
      <?php
      $pictureUrl = PluginTrademarkToolbox::getPictureUrl($loginPicture);
      $maxWidth = PluginTrademarkConfig::getConfig('login_picture_max_width', '145px');
      $maxHeight = PluginTrademarkConfig::getConfig('login_picture_max_height', '80px');
      ?>
      <style>
         .page-anonymous .glpi-logo {
            --logo: url(<?php echo $pictureUrl ?>);
            content: url(<?php echo $pictureUrl ?>);
            width: auto;
            height: auto;
            max-width: <?php echo $maxWidth ?>px;
            max-height: <?php echo $maxHeight ?>px;
         }
      </style>
   <?php endif; ?>
   <script type="text/javascript">
      $('#login_name').attr('placeholder', <?php echo json_encode(__('Login')) ?>);
      $('input[type=password]').attr('placeholder', <?php echo json_encode(__('Password')) ?>);
      $('input[type=password]').after($('.form-label-description'));
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
         $(function() {
            $('#footer-login').hide();
         });
      <?php
      endif;
   if ($footerDisplay === 'custom') :
      $footerText = \Glpi\RichText\RichText::getEnhancedHtml($footerText);
      ?>
         $(function() {
            $('a.copyright').parent().html(<?php echo json_encode($footerText) ?>);
         });
      <?php
      endif;
   ?>
   </script>
   <?php
}

function plugin_trademark_install() {
   return true;
}

function plugin_trademark_uninstall() {
   return true;
}
