<?

namespace Tai\CurrencyExchangeRate;

use Bitrix\Main\Config\Option;

class OptionManager
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getModuleId(): string
    {
        return 'tai.currencyexchangerate';
    }

    public function getValute(): array
    {
        $tcer = unserialize(Option::get($this->getModuleId(), 'tcer'));

        return !empty($tcer['valute']) && is_array($tcer['valute']) ? $tcer['valute'] : [];
    }
}