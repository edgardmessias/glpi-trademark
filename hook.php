<?php

function plugin_trademark_display_login() {

   global $GLPI_CACHE;

   $timestamp = $GLPI_CACHE->get('trademark_timestamp');
   if (!$timestamp) {
      $timestamp = time();
      $GLPI_CACHE->set('trademark_timestamp', $timestamp);
   }

   echo Html::css("/plugins/trademark/front/login.css.php?_=$timestamp", [
      'version' => PLUGIN_TRADEMARK_VERSION,
   ]);

   ?>
   <script type="text/javascript">
      var $box = $('#firstboxlogin');
      var $wrapper = $('<div />', {
         class: 'login_wrapper'
      }).append($box.contents());
      $wrapper.prependTo($box);
      $('#display-login').appendTo($box);
   <?php

   $loginPicture = PluginTrademarkConfig::getConfig('login_picture');
   if ($loginPicture) :
      $pictureUrl = PluginTrademarkToolbox::getPictureUrl($loginPicture);
      $css = [
         'max-width' => PluginTrademarkConfig::getConfig('login_picture_max_width', '100px'),
         'max-height' => PluginTrademarkConfig::getConfig('login_picture_max_height', '100px'),
      ];
      ?>
         var $logo_login = $('#logo_login');
         var $img = $logo_login.find('img');
         if ($img.length) {
            $img.css(<?php echo json_encode($css) ?>);
            $img.attr('src', <?php echo json_encode($pictureUrl) ?>);
         } else {
            console.log('ok');
            $logo_login.css({
               'background-image': 'url(<?php echo json_encode($pictureUrl) ?>)'
            });
         }
      <?php
      endif;
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
