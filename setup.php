<?php

use Glpi\Http\Firewall;

define('PLUGIN_TRADEMARK_VERSION', '3.0.0');

// Minimal GLPI version, inclusive
define("PLUGIN_TRADEMARK_MIN_GLPI_VERSION", "11.0.0");

// Maximum GLPI version, exclusive
define("PLUGIN_TRADEMARK_MAX_GLPI_VERSION", "11.1.0");


$folder = basename(dirname(__FILE__));

if ($folder !== "trademark") {
   $msg = sprintf("Please, rename the plugin folder \"%s\" to \"trademark\"", $folder);
   Session::addMessageAfterRedirect($msg, true, ERROR);
}

// Init the hooks of the plugins -Needed
function plugin_init_trademark() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   $PLUGIN_HOOKS['csrf_compliant']['trademark'] = true;

   $PLUGIN_HOOKS['config_page']['trademark'] = '../../front/config.form.php?itemtype=Config&glpi_tab=PluginTrademarkConfig$1';

   $plugin = new Plugin();

   if ($plugin->isInstalled('trademark') && $plugin->isActivated('trademark')) {

      $autoload = __DIR__ . '/vendor/autoload.php';
      if (file_exists($autoload)) {
         include_once $autoload;
      };

      Plugin::registerClass('PluginTrademarkConfig', [
         'addtabon' => ['Config']
      ]);

      $PLUGIN_HOOKS['display_login']['trademark'] = "plugin_trademark_display_login";

      // Tip Trick to add version in css output
      // GLPI 11: core uses plugin version for cache busting; use string path
      $PLUGIN_HOOKS["add_css"]['trademark'] = 'front/internal.css.php';
      $PLUGIN_HOOKS["add_javascript"]['trademark'] = 'front/internal.js.php';

      $CFG_GLPI['javascript']['config']['config'][] = 'codemirror';
      $CFG_GLPI['javascript']['config']['config'][] = 'tinymce';

      // Make the callback page public again.
      Firewall::addPluginStrategyForLegacyScripts('trademark', '#^/front/login.css.php$#', Firewall::STRATEGY_NO_CHECK);
      Firewall::addPluginStrategyForLegacyScripts('trademark', '#^/front/picture.send.php$#', Firewall::STRATEGY_NO_CHECK);
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_trademark() {
   return [
      'name'           => t_trademark('Trademark'),
      'version'        => PLUGIN_TRADEMARK_VERSION,
      'author'         => '<a href="https://nextflow.com.br/">Nextflow</a>, <a href="https://github.com/edgardmessias">Edgard</a>',
      'homepage'       => 'https://nextflow.com.br/plugin-glpi/trademark',
      'license'        => 'GPL v2+',
      'minGlpiVersion' => PLUGIN_TRADEMARK_MIN_GLPI_VERSION,
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_TRADEMARK_MIN_GLPI_VERSION,
            'max' => PLUGIN_TRADEMARK_MAX_GLPI_VERSION,
         ]
      ]
   ];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_trademark_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_TRADEMARK_MIN_GLPI_VERSION, '<')) {
      echo "This plugin requires GLPI >= " . PLUGIN_TRADEMARK_MIN_GLPI_VERSION;
      return false;
   }
   if (version_compare(GLPI_VERSION, PLUGIN_TRADEMARK_MAX_GLPI_VERSION, '>=')) {
      echo "This plugin is not yet validated for this GLPI version";
      return false;
   }
   if (version_compare(PHP_VERSION, '8.2', '<')) {
      echo "This plugin requires PHP >= 8.2";
      return false;
   }
   return true;
}

function plugin_trademark_check_config() {
   return true;
}

function t_trademark($str) {
   return __($str, 'trademark');
}
