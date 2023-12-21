<?

namespace Tai\CurrencyExchangeRate\Api;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Type\DateTime;
use SimpleXMLElement;
use SoapClient;
use Throwable;

class Cbr {
    private const DAILY_INFO_URL = 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';

    public function getCursOnDate(DateTime $date): array
    {
        $result = [];

        try {
            $client = new SoapClient(self::DAILY_INFO_URL);

            $response = $client->GetCursOnDate(['On_date' => $date->format('Y-m-d')]);

            if (
                is_object($response)
                && property_exists($response, 'GetCursOnDateResult')
                && is_object($response->GetCursOnDateResult)
                && property_exists($response->GetCursOnDateResult, 'any')
            ) {
                $xmlData = simplexml_load_string($response->GetCursOnDateResult->any);

                if (
                    $xmlData instanceof SimpleXMLElement
                    && property_exists($xmlData, 'ValuteData')
                    && is_object($xmlData->ValuteData)
                    && property_exists($xmlData->ValuteData, 'ValuteCursOnDate')
                ) {
                    /** @var SimpleXMLElement $xmlCurs */
                    foreach ($xmlData->ValuteData->ValuteCursOnDate as $xmlCurs) {
                        if (
                            $xmlCurs instanceof SimpleXMLElement
                            && property_exists($xmlCurs, 'Vcurs')
                            && property_exists($xmlCurs, 'VchCode')
                        ) {
                            $code = trim(strval($xmlCurs->VchCode));

                            $result[$code] = [
                                'CODE' => $code,
                                'EXCHANGE_RATE' => floatval($xmlCurs->Vcurs)
                            ];
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            Debug::dumpToFile($e->getMessage());
            Debug::dumpToFile($e->getTraceAsString());
        }

        return $result;
    }

    public function getEnumValutes(): array
    {
        $result = [];

        $cacheEnabled = Option::get('main', 'component_cache_on', 'Y') === 'Y' && defined('BX_COMP_MANAGED_CACHE');
        $cacheDir = 'api/cbr';
        $cache = Cache::createInstance();

        try {
            if ($cacheEnabled && $cache->initCache(3600, __METHOD__, $cacheDir)) {
                $result = $cache->getVars();
            } elseif (!$cacheEnabled || $cache->startDataCache()) {
                $client = new SoapClient(self::DAILY_INFO_URL);

                $response = $client->EnumValutes(['Seld' => false]);

                if (
                    is_object($response)
                    && property_exists($response, 'EnumValutesResult')
                    && is_object($response->EnumValutesResult)
                    && property_exists($response->EnumValutesResult, 'any')
                ) {
                    $xmlData = simplexml_load_string($response->EnumValutesResult->any);

                    if (
                        $xmlData instanceof SimpleXMLElement
                        && property_exists($xmlData, 'ValuteData')
                        && is_object($xmlData->ValuteData)
                        && property_exists($xmlData->ValuteData, 'EnumValutes')
                    ) {
                        /** @var SimpleXMLElement $xmlValute */
                        foreach ($xmlData->ValuteData->EnumValutes as $xmlValute) {
                            if (
                                $xmlValute instanceof SimpleXMLElement
                                && property_exists($xmlValute, 'VcharCode')
                                && property_exists($xmlValute, 'Vname')
                            ) {
                                $code = trim(strval($xmlValute->VcharCode));

                                $result[$code] = [
                                    'CODE' => $code,
                                    'NAME' => trim(strval($xmlValute->Vname))
                                ];
                            }
                        }
                    }
                }

                if ($cacheEnabled) {
                    $cache->endDataCache($result);
                }
            }
        } catch (Throwable $e) {
            if ($cacheEnabled) {
                $cache->abortDataCache();
            }

            Debug::dumpToFile($e->getMessage());
            Debug::dumpToFile($e->getTraceAsString());
        }

        return $result;
    }
}
