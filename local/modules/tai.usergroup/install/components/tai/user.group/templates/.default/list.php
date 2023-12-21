<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$APPLICATION->IncludeComponent('tai:user.group.list', '.default', [
    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
    'CACHE_TIME' => $arParams['CACHE_TIME'],
    'DETAIL_PAGE_URL_TEMPLATE' => $arParams['SEF_FOLDER'] . $arParams['SEF_URL_TEMPLATES']['detail'],
    'SECTION_TITLE' => $arParams['SECTION_TITLE']
], $component);
