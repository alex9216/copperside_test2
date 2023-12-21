<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = [
	'GROUPS' => [],
	'PARAMETERS' => [
		'SECTION_TITLE' => [
			'PARENT' => 'BASE',
			'NAME' => 'Заголовок страницы со списком групп',
			'TYPE' => 'TEXT'
		],
		'CACHE_TIME' => ['DEFAULT' => 36000000],
	]
];