<?
namespace Tai\CurrencyExchangeRate\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type;

class CurrencyExchangeRateTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'tai_currency_exchange_rate';
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),
            (new StringField('CODE'))
                ->configureSize(8),
            (new FloatField('EXCHANGE_RATE')),
            (new DatetimeField('DATE_INSERT'))
                ->configureRequired()
                ->configureDefaultValue(function () {
                    return new Type\DateTime();
                })
        ];
    }
}
