<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}
?>

<div class="adm-toolbar-panel-container">
    <div class="adm-toolbar-panel-flexible-space"><?= $arResult['FILTER_HTML'] ?></div>
    <div class="adm-toolbar-panel-align-right"><?= $arResult['BUTTONS_HTML'] ?></div>
</div>

<?
$APPLICATION->includeComponent(
    'bitrix:main.ui.grid',
    '',
    $arResult['GRID_PARAMS']
);
?>