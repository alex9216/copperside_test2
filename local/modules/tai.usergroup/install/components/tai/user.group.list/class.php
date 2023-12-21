<?
use Bitrix\Main\GroupTable;
use Bitrix\Main\UserGroupTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

use CBitrixComponent;
use Throwable;

class UserGroupListComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $result = array(
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 3600,
            'DETAIL_PAGE_URL_TEMPLATE' => isset($arParams['DETAIL_PAGE_URL_TEMPLATE'])
                ? $arParams['DETAIL_PAGE_URL_TEMPLATE']
                : ''
        );

        return $result;
    }

    public function executeComponent()
    {
        try {
            if ($this->startResultCache()) {
                $this->arResult['ITEMS'] = [];

                $qRes = GroupTable::query()
                    ->setSelect(['ID', 'NAME', 'DESCRIPTION'])
                    ->where('ACTIVE', true)
                    ->exec();

                while ($userGroup = $qRes->fetch()) {
                    $item = $userGroup;

                    $item['DETAIL_PAGE_URL'] = str_replace(
                        '#ELEMENT_ID#',
                        $item['ID'],
                        $this->arParams['DETAIL_PAGE_URL_TEMPLATE']
                    );

                    $this->arResult['ITEMS'][] = $item;
                }

                $this->includeComponentTemplate();
            }
        } catch (Throwable $e) {
            $this->abortResultCache();

            ShowError($e->getMessage());
        }
    }
}
