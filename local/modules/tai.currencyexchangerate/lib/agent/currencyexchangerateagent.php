<?
namespace Tai\CurrencyExchangeRate\Agent;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Type\DateTime;
use Tai\CurrencyExchangeRate\Api\Cbr;
use Tai\CurrencyExchangeRate\Entity\CurrencyExchangeRateTable;
use Tai\CurrencyExchangeRate\OptionManager;
use Throwable;

class CurrencyExchangeRateAgent
{
    public static function fetchExchangeRates()
    {
        try {
            $cbr = new Cbr();
            $cursDt = new DateTime();

            $optValutes = OptionManager::getInstance()->getValute();
            $cursesAll = $cbr->getCursOnDate($cursDt);

            $curses = [];

            foreach ($optValutes as $optValute) {
                if (isset($cursesAll[$optValute])) {
                    $curses[$optValute] = $cursesAll[$optValute];
                }
            }

            foreach ($curses as $curs) {
                CurrencyExchangeRateTable::add([
                    'CODE' => $curs['CODE'],
                    'EXCHANGE_RATE' => $curs['EXCHANGE_RATE'],
                    'DATE_INSERT' => $cursDt
                ]);
            }
        } catch (Throwable $e) {
            Debug::dumpToFile($e->getMessage());
            Debug::dumpToFile($e->getTraceAsString());
        }

        return __METHOD__ . '();';
    }
}
