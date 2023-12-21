<?

use Bitrix\Main\Loader;

global $DB;
$strDBType = strtolower($DB->type);

Loader::registerAutoLoadClasses(
    'tai.currencyexchangerate',
    [
        //CExample::class => 'classes/' . $strDBType . '/example.php',
        //Tai\CurrencyExchangeRate\Agent\CurrencyExchangeRateAgent::class => 'lib/agent/currencyexchangerateagent.php'
    ]
);

unset($strDBType);
