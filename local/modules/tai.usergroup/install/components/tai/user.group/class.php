<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use CBitrixComponent;
use CComponentEngine;

class CWebhook extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        return [
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 3600,
            'SEF_MODE' => isset($arParams['SEF_MODE']) ? $arParams['SEF_MODE'] : 'Y',
            'SEF_FOLDER' => isset($arParams['SEF_FOLDER']) ? $arParams['SEF_FOLDER'] : '',
            'SEF_URL_TEMPLATES' => isset($arParams['SEF_URL_TEMPLATES']) ? $arParams['SEF_URL_TEMPLATES'] : [],
            'SECTION_TITLE' => isset($arParams['SECTION_TITLE']) ? $arParams['SECTION_TITLE'] : ''
        ];
    }

    public function executeComponent()
    {
        if ($this->arParams['SEF_MODE'] != 'Y') {
            ShowError('SEF_MODE == N is not supported.');
        } else {
            $arDefaultUrlTemplates404 = [
                'detail'    => '#ELEMENT_ID#/',
            ];

            $arComponentVariables = [
                'ELEMENT_ID'
            ];

            $arUrlTemplates = [];
            $arVariables = [];

            $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates(
                $arDefaultUrlTemplates404,
                $this->arParams['SEF_URL_TEMPLATES']
            );
            $arVariableAliases = CComponentEngine::MakeComponentVariableAliases([], []);

            $componentPage = CComponentEngine::ParseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $arUrlTemplates,
                $arVariables
            );

            if (empty($componentPage)) {
                $componentPage = 'list';
            }

            CComponentEngine::InitComponentVariables(
                $componentPage,
                $arComponentVariables,
                $arVariableAliases,
                $arVariables
            );

            $this->arResult = [
                'FOLDER'        => $this->arParams['SEF_FOLDER'],
                'URL_TEMPLATES' => $arUrlTemplates,
                'VARIABLES'     => $arVariables,
                'ALIASES'       => $arVariableAliases,
            ];

            $this->IncludeComponentTemplate($componentPage);
        }
    }

}
