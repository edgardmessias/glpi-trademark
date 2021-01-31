<?php

class PluginTrademarkConfig extends CommonDBTM {

   private static $_cache = null;
   private static $_i = 1;

   static function getConfig($name, $defaultValue = null) {

      if (self::$_cache === null) {
         $config = new self();
         $config->getEmpty();
         $config->fields = array_merge($config->fields, Config::getConfigurationValues('trademark'));

         self::$_cache = $config->fields;
      }

      if (isset(self::$_cache[$name]) && self::$_cache[$name] !== '') {
         return self::$_cache[$name];
      }
      return $defaultValue;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Config':
            return [1 => t_trademark('Trademark')];
         default:
            return '';
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Config':
            $config = new self();
            $config->showFormDisplay();
            break;
      }
      return true;
   }

   protected static function checkPicture($name, $input, $old, $width = 0, $height = 0, $max_size = 500) {

      $blank = "_blank_$name";
      $new = "_new_$name";

      if (isset($input[$blank]) && $input[$blank]) {
         unset($input[$blank]);
         if (isset($old[$name]) && $old[$name]) {
            PluginTrademarkToolbox::deletePicture($old[$name]);
         }
         $input[$name] = '';
      } else if (isset($input[$new]) && !empty($input[$new])) {
         $picName = array_shift($input[$new]);
         $picPath = GLPI_TMP_DIR . '/' . $picName;
         $picResizedPath = GLPI_TMP_DIR . '/resized_' . $picName;

         if ($width || $height) {
            if (PluginTrademarkToolbox::resizePicture($picPath, $picResizedPath, $width, $height, 0, 0, 0, 0, $max_size)) {
               $picPath = $picResizedPath;
            }
         }

         if ($dest = PluginTrademarkToolbox::savePicture($picPath)) {
            $input[$name] = $dest;
         } else {
            Session::addMessageAfterRedirect(__('Unable to save picture file.'), true, ERROR);
         }

         if (isset($old['$name']) && $old['$name']) {
            PluginTrademarkToolbox::deletePicture($old['$name']);
         }
      }

      unset($input["_$name"]);
      unset($input["_prefix_$name"]);
      unset($input["_prefix_new_$name"]);
      unset($input["_tag_$name"]);
      unset($input["_tag_new_$name"]);
      unset($input["new_$name"]);
      unset($input[$blank]);
      unset($input[$new]);

      return $input;
   }

   protected static function checkCSS($name, $label, $input, $old) {
      $fullName = "{$name}_css_custom";
      $type = "{$name}_css_type";

      if (!isset($input[$type])) {
         $input[$type] = 'css';
      }

      if (isset($input[$fullName])) {
         $input[$fullName] = html_entity_decode($input[$fullName]);
         $input[$fullName] = preg_replace('/\\\\r\\\\n/', "\n", $input[$fullName]);
         $input[$fullName] = preg_replace('/\\\\n/', "\n", $input[$fullName]);

         if ($input[$type] === 'scss' && PluginTrademarkScss::hasScssSuport()) {
            try {
               PluginTrademarkScss::compileScss($input[$fullName]);
            } catch (\Throwable $th) {
               $message = sprintf(t_trademark('Unable to compile the SCSS (%1$s). Message: '), $label);
               Session::addMessageAfterRedirect($message . $th->getMessage(), true, ERROR);
            }
         }
      }

      return $input;
   }

   static function configUpdate($input) {
      $old = Config::getConfigurationValues('trademark');

      unset($input['_no_history']);

      $input = self::checkPicture('favicon_picture', $input, $old, 192, 192, 192);
      $input = self::checkPicture('login_picture', $input, $old, 145, 80, 300);
      $input = self::checkPicture('internal_picture', $input, $old, 100, 55, 300);
      $input = self::checkPicture('login_background_picture', $input, $old);

      $input = self::checkCSS('login', t_trademark('Login Page'), $input, $old);
      $input = self::checkCSS('internal', t_trademark('Internal Page'), $input, $old);

      $input['timestamp'] = time();

      PluginTrademarkToolbox::setTimestamp($input['timestamp']);

      return $input;
   }

   function getEmpty() {

      $defaultCss = 'css';

      if (PluginTrademarkScss::hasScssSuport()) {
         $defaultCss = 'scss';
      }

      $this->fields = [
         'favicon_picture' => '',
         'page_title' => '',
         'login_picture' => '',
         'login_picture_max_width' => '240px',
         'login_picture_max_height' => '130px',
         'login_css_custom' => '',
         'login_css_type' => $defaultCss,
         'internal_picture' => '',
         'internal_picture_width' => '100px',
         'internal_picture_height' => '55px',
         'internal_css_custom' => '',
         'internal_css_type' => $defaultCss,
      ];
   }

   protected function buildPictureLine($name, $recommendedSize = null) {
      if (!empty($this->fields[$name])) {
         echo '<td>';
         echo Html::image(PluginTrademarkToolbox::getPictureUrl($this->fields[$name]), [
            'style' => '
               max-width: 100px;
               max-height: 100px;
               background-image: linear-gradient(45deg, #b0b0b0 25%, transparent 25%), linear-gradient(-45deg, #b0b0b0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #b0b0b0 75%), linear-gradient(-45deg, transparent 75%, #b0b0b0 75%);
               background-size: 10px 10px;
               background-position: 0 0, 0 5px, 5px -5px, -5px 0px;',
            'class' => 'picture_square'
         ]);
         echo "&nbsp;";
         echo Html::getCheckbox([
            'title' => t_trademark('Reset'),
            'name'  => "_blank_$name"
         ]);
         echo "&nbsp;" . t_trademark('Reset');
         echo '</td>';
         echo '<td colspan="2">';
      } else {
         echo '<td colspan="3">';
      }
      Html::file([
         'name'       => "new_$name",
         'onlyimages' => true,
      ]);
      if ($recommendedSize) {
         echo '<small>';
         echo sprintf(t_trademark('Recommended size: %1$s'), $recommendedSize);
         echo '</small>';
      }
      echo '</td>';
   }

   protected function buildCssLine($name, $label) {
      $fullName = "{$name}_css_custom";
      $type = "{$name}_css_type";

      echo "<tr><th colspan='4'>" . sprintf(t_trademark('Custom CSS for %1$s'), $label) . "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo t_trademark('CSS Type') . ':';
      echo "</td>";
      echo "<td colspan='3'>";

      $css_type = [
         'off' => __('Disabled'),
         'css' => 'CSS',
      ];

      if (PluginTrademarkScss::hasScssSuport()) {
         $css_type['scss'] = 'SCSS';
      }
      Dropdown::showFromArray($type, $css_type, ['value' => $this->fields[$type]]);
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4' style='max-width: 1000px'>";
      $rand = mt_rand();

      echo sprintf(
         '<textarea %1$s>',
         Html::parseAttributes([
            'id' => $fullName . '_' . $rand,
            'name' => $fullName,
         ])
      );
      echo $this->fields[$fullName];
      echo '</textarea>';

      $editor_options = [
         'mode'               => 'text/x-scss',
         'lineNumbers'        => true,

         // Autocomplete with CTRL+SPACE
         'extraKeys'          => [
            'Ctrl-Space' => 'autocomplete',
         ],

         // Code folding configuration
         'foldGutter' => true,
         'gutters'    => [
            'CodeMirror-linenumbers',
            'CodeMirror-foldgutter'
         ],
      ];

      echo Html::scriptBlock('
         $(function() {
            var textarea = document.getElementById("' . $fullName . '_' . $rand . '");
            var editorLogin = CodeMirror.fromTextArea(textarea, ' . json_encode($editor_options) . ');

            // Fix bad display of gutter (see https://github.com/codemirror/CodeMirror/issues/3098 )
            setTimeout(function () {editorLogin.refresh();}, ' . (500 + self::$_i++ * 100) . ');
         });
      ');
      echo "</td>";
      echo "</tr>\n";
   }

   /**
    * Print the config form for display
    *
    * @return Nothing (display)
    * */
   function showFormDisplay() {
      if (!Config::canView()) {
         return false;
      }

      $this->getEmpty();

      $this->fields = array_merge($this->fields, Config::getConfigurationValues('trademark'));

      // Codemirror lib
      echo Html::css('public/lib/codemirror.css');
      echo Html::script("public/lib/codemirror.js");

      $canedit = Session::haveRight(Config::$rightname, UPDATE);
      if ($canedit) {
         echo "<form name='form' action=\"" . Toolbox::getItemTypeFormURL('Config') . "\" method='post'>";
      }
      echo Html::hidden('config_context', ['value' => 'trademark']);
      echo Html::hidden('config_class', ['value' => __CLASS__]);

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

      // General
      echo "<tr><th colspan='4'>" . t_trademark('Favicon and Title') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Picture') . "</td>";
      $this->buildPictureLine('favicon_picture', '192px x 192px');
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Title') . "</td>";
      echo "<td colspan='3'>";
      echo sprintf(
         '<input type="text" %1$s />',
         Html::parseAttributes([
            'name' => 'page_title',
            'value' => $this->fields['page_title'],
         ])
      );
      echo "</td>";
      echo "</tr>\n";

      // Login
      echo "<tr><th colspan='4'>" . t_trademark('Login Page') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Picture') . "</td>";
      $this->buildPictureLine('login_picture', '145px x 80px');
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Width') . "</td>";
      echo "<td>";
      echo sprintf(
         '<input type="text" %1$s />',
         Html::parseAttributes([
            'name' => 'login_picture_max_width',
            'value' => $this->fields['login_picture_max_width'],
         ])
      );
      echo "</td>";
      echo "<td>" . __('Height')  . "</td>";
      echo "<td>";
      echo sprintf(
         '<input type="text" %1$s />',
         Html::parseAttributes([
            'name' => 'login_picture_max_height',
            'value' => $this->fields['login_picture_max_height'],
         ])
      );
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . t_trademark('Background picture') . "</td>";
      $this->buildPictureLine('login_background_picture', '1920px x 1080px');
      echo "</tr>\n";

      // Internal Page
      echo "<tr><th colspan='4'>" . t_trademark('Internal Page') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Picture') . "</td>";
      $this->buildPictureLine('internal_picture', '100px x 55px');
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Width') . "</td>";
      echo "<td>";
      echo sprintf(
         '<input type="text" %1$s />',
         Html::parseAttributes([
            'name' => 'internal_picture_width',
            'value' => $this->fields['internal_picture_width'],
         ])
      );
      echo "</td>";
      echo "<td>" . __('Height')  . "</td>";
      echo "<td>";
      echo sprintf(
         '<input type="text" %1$s />',
         Html::parseAttributes([
            'name' => 'internal_picture_height',
            'value' => $this->fields['internal_picture_height'],
         ])
      );
      echo "</td>";
      echo "</tr>\n";

      // Custom CSS LOGIN
      $this->buildCssLine('login', t_trademark('Login Page'));
      $this->buildCssLine('internal', t_trademark('Internal Page'));

      if ($canedit) {
         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' class='center'>";
         echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Save') . "\">";
         echo "</td></tr>";
      }

      echo "</table></div>";
      Html::closeForm();
   }
}
