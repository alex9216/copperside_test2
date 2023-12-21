<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

use Bitrix\Main\GroupTable;
use CBitrixComponent;
use CHTTP;
use Exception;
use Throwable;

class UserGroupDetailComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $result = array(
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 3600,
            'GROUP_ID' => isset($arParams['GROUP_ID']) ? $arParams['GROUP_ID'] : null,
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
                $this->arResult['ID'] = $this->arParams['GROUP_ID'];
                $this->arResult['NAME'] = '';
                $this->arResult['DESCRIPTION'] = '';

                $this->arResult['ITEMS'] = [];

                if (!empty($this->arParams['GROUP_ID'])) {
                    $group = GroupTable::query()
                        ->setSelect(['ID', 'NAME', 'DESCRIPTION'])
                        ->where('ACTIVE', true)
                        ->where('ID', $this->arParams['GROUP_ID'])
                        ->fetch();

                    if (!empty($group)) {
                        $this->arResult['ID'] = $group['ID'];
                        $this->arResult['NAME'] = $group['NAME'];
                        $this->arResult['DESCRIPTION'] = $group['DESCRIPTION'];
                    } else {
                        CHTTP::SetStatus("404 Not Found");
                        throw new Exception(GetMessage('GROUP_NOT_FOUND'));
                    }
                }

                $this->includeComponentTemplate();
            }
        } catch (Throwable $e) {
            $this->abortResultCache();

            ShowError($e->getMessage());
        }
    }
}
