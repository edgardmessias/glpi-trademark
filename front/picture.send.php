<?php
include('../../../inc/includes.php');

$path = false;

if (isset($_GET['path'])) {
   $path = $_GET['path'];
} else {
   Html::displayErrorAndDie(__('Invalid filename'), true);
}

$path = GLPI_PLUGIN_DOC_DIR . "/trademark/" . $path;

if (!file_exists($path)) {
   Html::displayErrorAndDie(__('File not found'), true); // Not found
}
Toolbox::sendFile($path, "picture.png", null, true);
