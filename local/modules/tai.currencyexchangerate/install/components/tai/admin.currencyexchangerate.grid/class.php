<?php
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Grid\MessageType as GridMessageType;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Grid\Panel\Snippet as PanelSnippet;
use Bitrix\Main\Grid\Types as GridTypes;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\Filter\Theme as FilterTheme;
use Bitrix\Main\UI\Extension as UIExtension;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type;
use Bitrix\UI\Buttons as UIButtons;
use Bitrix\UI\Toolbar as UIToolbar;
use Bitrix\UI\Toolbar\Facade\Toolbar as ToolbarFacade;
use CAjax;
use CBitrixComponent;
use CMain;
use CUtil;
use Exception;
use Throwable;
use Tai\CurrencyExchangeRate\Entity\CurrencyExchangeRateTable;

class TaiAdminCurrencyExchangerateGridComponent extends CBitrixComponent
{
    private const NAV_PARAM_NAME = 'page';

    public function onPrepareComponentParams($arParams)
    {
        $arParams['PAGE_SIZES'] = [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '200', 'VALUE' => '200'],
            ['NAME' => '500', 'VALUE' => '500']
        ];

        $gridOptions = new GridOptions($arParams['GRID_ID']);

		$gridSort = $gridOptions->GetSorting(['sort' => ['DATE_INSERT' => 'desc']]);
        $navParams = $gridOptions->getNavParams();

        $pageNavigation = new PageNavigation(self::NAV_PARAM_NAME);
        $pageNavigation->allowAllRecords(false)->setPageSize($navParams['nPageSize'])->initFromUri();

        $filterOptions = new FilterOptions($arParams['FILTER_ID']);

        $arParams['SORT'] = $gridSort['sort'];
        $arParams['PAGE_NAVIGATION'] = $pageNavigation;
        $arParams['CURRENT_PAGE'] = $pageNavigation->getCurrentPage();
        $arParams['FILTER'] = $filterOptions->getFilterLogic($this->getFilterFields());

        $searchString = $filterOptions->getSearchString();

        if (!empty($searchString) && empty($arParams['FILTER']['URL']))
        {
            $arParams['FILTER']['URL'] = $searchString;
        }

        $arParams['CACHE_TYPE'] = 'N';

        return $arParams;
    }

    public function executeComponent()
    {
        $this->arResult['ERRORS'] = [];

        if (!Loader::includeModule('ui')) {
            throw new Exception('Could not include module ui');
        }

        if (!Loader::includeModule('tai.currencyexchangerate')) {
            throw new Exception('Could not include module tai.currencyexchangerate');
        }

        $this->arResult = $this->proccessAction();

        UIExtension::load(['ui', 'ui.buttons', 'adminmetatagsgrid']);

        $this->arResult['GRID_PARAMS'] = [];
        $this->arResult['FILTER_PARAMS'] = [];

        $rows = $this->getRows();
        $totalRowsCount = $this->getTotal($this->arParams);
        $this->arParams['PAGE_NAVIGATION']->setRecordCount($totalRowsCount);

		$this->arResult['GRID_PARAMS']['ALLOW_COLUMNS_SORT'] = true;
		$this->arResult['GRID_PARAMS']['ALLOW_COLUMNS_RESIZE'] = true;
        $this->arResult['GRID_PARAMS']['ALLOW_CONTEXT_MENU'] = false;
		$this->arResult['GRID_PARAMS']['ALLOW_HORIZONTAL_SCROLL'] = true;
		$this->arResult['GRID_PARAMS']['AJAX_MODE'] = 'Y';
		$this->arResult['GRID_PARAMS']['AJAX_OPTION_JUMP'] = 'N';
		$this->arResult['GRID_PARAMS']['AJAX_OPTION_STYLE'] = 'N';
		$this->arResult['GRID_PARAMS']['AJAX_OPTION_HISTORY'] = 'N';
		$this->arResult['GRID_PARAMS']['AJAX_ID'] = CAjax::GetComponentID('bitrix:main.ui.grid', '', '');
        $this->arResult['GRID_PARAMS']['COLUMNS'] = $this->getColumns();
		$this->arResult['GRID_PARAMS']['CURRENT_PAGE'] = $this->arParams['CURRENT_PAGE'];
        $this->arResult['GRID_PARAMS']['EDITABLE'] = true;
        $this->arResult['GRID_PARAMS']['GRID_ID'] = $this->arParams['GRID_ID'];
		$this->arResult['GRID_PARAMS']['HANDLE_RESPONSE_ERRORS'] = true;
		$this->arResult['GRID_PARAMS']['NAV_PARAM_NAME'] = self::NAV_PARAM_NAME;
		$this->arResult['GRID_PARAMS']['NAV_OBJECT'] = $this->arParams['PAGE_NAVIGATION'];
        $this->arResult['GRID_PARAMS']['PAGE_SIZES'] = $this->arParams['PAGE_SIZES'];
		$this->arResult['GRID_PARAMS']['SHOW_ROW_ACTIONS_MENU'] = false;
		$this->arResult['GRID_PARAMS']['SHOW_GRID_SETTINGS_MENU'] = true;
		$this->arResult['GRID_PARAMS']['SHOW_NAVIGATION_PANEL'] = true;
		$this->arResult['GRID_PARAMS']['SHOW_PAGESIZE'] = true;
		$this->arResult['GRID_PARAMS']['SHOW_PAGINATION'] = $totalRowsCount > 0;
		$this->arResult['GRID_PARAMS']['SHOW_ROW_CHECKBOXES'] = true;
		$this->arResult['GRID_PARAMS']['SHOW_SELECTED_COUNTER'] = true;
		$this->arResult['GRID_PARAMS']['SHOW_TOTAL_COUNTER'] = true;
        $this->arResult['GRID_PARAMS']['ROWS'] = $rows;
        $this->arResult['GRID_PARAMS']['TOTAL_ROWS_COUNT'] = $totalRowsCount;

        if (!empty($this->arParams['CAN_EDIT']))
        {
            $panelSnippet = new PanelSnippet();

            $editButton = $panelSnippet->getEditButton();
            $removeButton = $panelSnippet->getRemoveButton();

            $this->arResult['GRID_PARAMS']['ACTION_PANEL'] = ['GROUPS' => [['ITEMS' => [
                $editButton,
                $removeButton
            ]]]];
        }

        $this->arResult['FILTER_PARAMS'] = [
            'GRID_ID' => $this->arParams['GRID_ID'],
            'FILTER_ID' => $this->arParams['FILTER_ID'],
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => FilterTheme::LIGHT
        ];

		ToolbarFacade::addFilter($this->arResult['FILTER_PARAMS']);

        $buttonConfig = [
            'click' => new UIButtons\JsCode(
                'BX.Main.gridManager.getInstanceById(\''
                . $this->arParams['GRID_ID']
                . '\').reloadTable(\'POST\', '
                . CUtil::PhpToJSObject(['action' => 'addItem'])
                . ')'
            ),
			'text' => 'Добавить элемент',
			'color' => UIButtons\Color::PRIMARY
        ];

        ToolbarFacade::addButton(
            UIButtons\Button::create($buttonConfig),
            UIToolbar\ButtonLocation::AFTER_FILTER
        );

        $this->arResult['FILTER_HTML'] = ToolbarFacade::getFilter();
        $this->arResult['BUTTONS_HTML'] = '';

        /** @var UIButtons\Button $button */
        foreach (ToolbarFacade::getButtons() as $button)
        {
            $this->arResult['BUTTONS_HTML'] .= $button->render();
        }

        $this->includeComponentTemplate();

        return $this->arResult;
    }

    private function getColumns(): array
    {
        return [
			[
				'id' => 'ID',
				'name' => 'ID',
				'sort' => 'ID',
				'default' => true
			],
			[
				'id' => 'DATE_INSERT',
				'name' => 'Дата добавления',
				'sort' => 'DATE_INSERT',
				'default' => true,
                'editable' => true,
                'type' => 'date'
			],
			[
				'id' => 'CODE',
				'name' => 'Код валюты',
				'sort' => 'CODE',
				'default' => true,
                'editable' => true,
                'type' => GridTypes::GRID_TEXT
			],
			[
				'id' => 'EXCHANGE_RATE',
				'name' => 'Курс',
				'sort' => 'EXCHANGE_RATE',
				'default' => true,
                'editable' => true,
                'type' => GridTypes::GRID_TEXT
			]
        ];
    }

    private function getFilterFields(): array
    {
        return $this->getColumns();
    }

    private function prepareRowsQuery()
    {
        return CurrencyExchangeRateTable::query()
            ->setOrder($this->arParams['SORT'])
            ->setFilter($this->arParams['FILTER'])
            ->setOffset($this->arParams['PAGE_NAVIGATION']->getOffset())
            ->setLimit($this->arParams['PAGE_NAVIGATION']->getLimit())
            ->addSelect('*');
    }

    private function getRows(): array
    {
        $rows = [];

        $iterator = $this->prepareRowsQuery()->exec();

        while ($item = $iterator->fetch())
        {
            $rows[] = [
                'id' => $item['ID'],
                'data' => [
                    'ID' => $item['ID'],
                    'DATE_INSERT' => $item['DATE_INSERT']->toString(),
                    'CODE' => $item['CODE'],
                    'EXCHANGE_RATE' => $item['EXCHANGE_RATE']
                ]
            ];
        }

        return $rows;
    }

    private function getTotal(array $params): int
    {
        return intval($this->prepareRowsQuery()->queryCountTotal());
    }

    private function proccessAction(): array
    {
        global $APPLICATION;

		$action = $this->request->get('action');
        $groupAction = $this->request->get('action_button_' . $this->arParams['GRID_ID']);

        try
        {
            if (!empty($action))
            {
                if ($action === 'addItem')
                {
                    $addResult = CurrencyExchangeRateTable::add([]);

                    if (!$addResult->isSuccess())
                    {
                        $this->arResult['ERRORS'] = array_merge($this->arResult['ERRORS'], $addResult->getErrorMessages());
                    }
                }
            }

            if (!empty($groupAction))
            {
                $ids = $this->request->get('ID');
                $fields = $this->request->get('FIELDS');

                if (!empty($fields) && is_array($fields))
                {
                    $ids = array_keys($fields);
                }

                if (!empty($ids) && is_array($ids))
                {
                    $cersCollection = CurrencyExchangeRateTable::query()
                        ->whereIn('ID', $ids)
                        ->exec()
                        ->fetchCollection();

                    /** @var Tai\CurrencyExchangeRate\Entity\EO_CurrencyExchangeRate $cer */
                    foreach ($cersCollection as $cer)
                    {
                        $id = $cer->getId();

                        if ($groupAction === 'edit' && $this->arParams['CAN_EDIT'])
                        {
                            if (!empty($fields[$id]) && is_array($fields[$id]))
                            {
                                foreach ($fields[$id] as $fieldName => $fieldValue)
                                {
                                    $cer->set($fieldName, $fieldValue);
                                }
                            }
                        }
                        elseif ($groupAction === 'delete')
                        {
                            $cer->delete();
                        }
                    }

                    $cersCollection->save();
                }
            }
        }
        catch (Throwable $e)
        {
            $this->arResult['ERRORS'][] = $e->getMessage();
        }

        if (!empty($this->arResult['ERRORS']) && is_array($this->arResult['ERRORS']))
        {
            $messages = [];

            foreach ($this->arResult['ERRORS'] as $error)
            {
                $messages[] = [
                    'TYPE' => GridMessageType::ERROR,
                    'TITLE' => 'Ошибка!',
                    'TEXT' => $error
                ];
            }

            $APPLICATION->RestartBuffer();
            CMain::FinalActions(Json::encode(['messages' => $messages]));
        }

        return $this->arResult;
    }
}
