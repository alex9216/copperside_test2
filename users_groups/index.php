<?
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle('Тестовое задание Copperside');
?>

<?
$APPLICATION->IncludeComponent(
	'tai:user.group',
	'.default',
	[
		'CACHE_TYPE' => 'A',
		'CACHE_TIME' => '36000000',
		'SEF_MODE' => 'Y',
		'SEF_FOLDER' => '/users_groups/',
		'SEF_URL_TEMPLATES' => [
			'detail' => '#ELEMENT_ID#/'
		],
		'SECTION_TITLE' => 'Список групп пользователей'
	]
);
?>

<?
require($_SERVER['DOCUMENT_ROOT'] .'/bitrix/footer.php');
?>
