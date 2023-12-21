<?
/** @var \CMain $APPLICATION */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/prolog.php';

use Bitrix\Main\Localization\Loc;

$modulePermissions = $APPLICATION->GetGroupRight('main');

$canEdit = $modulePermissions >= 'W';
$canView = $modulePermissions >= 'U';

if (!$canView)
{
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

require $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/prolog_admin_after.php';

$APPLICATION->includeComponent(
    'tai:admin.currencyexchangerate.grid',
    '.default',
    [
        'GRID_ID' => 'admin_currencyexchangerate_grid_v1',
        'FILTER_ID' => 'admin_currencyexchangerate_filter_v1',
        'CAN_EDIT' => $canEdit
    ]
);

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin.php';
