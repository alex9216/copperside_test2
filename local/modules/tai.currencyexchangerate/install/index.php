<?

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use CAgent;
use Throwable;

class tai_currencyexchangerate extends CModule
{
    public $MODULE_ID = 'tai.currencyexchangerate';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $PARTNER_NAME;

    public $MODULE_COMPONENTS_DIR;
    public $MODULE_COMPONENTS_INSTALL_DIR;
    public $MODULE_COMPONENTS_NAMES;

    public const CURRENCY_EXCHANGE_RATE_AGENT = '\Tai\CurrencyExchangeRate\Agent\CurrencyExchangeRateAgent::fetchExchangeRates();';

    public function __construct()
    {
        $arModuleVersion = [];

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));

        include($path . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = 'Tai: Курсы валют';
        $this->MODULE_DESCRIPTION = 'Модуль который периодически получает курсы валют и дает возможность ими управлять';
        $this->PARTNER_NAME = 'Татаринцев Алексей Иванович';

        $this->MODULE_COMPONENTS_DIR = __DIR__ . '/components';
        $this->MODULE_COMPONENTS_INSTALL_DIR = Application::getDocumentRoot() . '/local/components';
        $this->MODULE_COMPONENTS_NAMES = [
            'admin.currencyexchangerate.grid'
        ];
    }

    function InstallDB()
    {
        global $DB, $errors;

        $sqlErrors = $DB->RunSQLBatch(__DIR__ . '/db/' . strtolower($DB->type) . '/install.sql');

        if (!empty($sqlErrors) && is_array($sqlErrors)) {
            $errors = array_merge($errors, $sqlErrors);
        }

        return empty($sqlErrors);
    }

    public function UnInstallDB()
    {
        global $DB, $errors;

        $sqlErrors = $DB->RunSQLBatch(__DIR__ . '/db/' . strtolower($DB->type) . '/uninstall.sql');

        if (!empty($sqlErrors) && is_array($sqlErrors)) {
            $errors = array_merge($errors, $sqlErrors);
        }
    }

	function InstallFiles()
	{
		return CopyDirFiles(
            $this->MODULE_COMPONENTS_DIR,
            $this->MODULE_COMPONENTS_INSTALL_DIR,
            true,
            true,
            false
        )
		&& CopyDirFiles(
            __DIR__ . '/admin',
            Application::getDocumentRoot() . '/bitrix/admin',
            true,
            true,
            false
        );
	}

	function UnInstallFiles()
	{
        global $errors;

        try {
            DeleteDirFiles(
                __DIR__ . '/admin',
                Application::getDocumentRoot() . '/bitrix/admin'
            );

            foreach ($this->MODULE_COMPONENTS_NAMES as $componentName) {
                Directory::deleteDirectory($this->MODULE_COMPONENTS_INSTALL_DIR . '/tai/' . $componentName);
            }
        } catch (Throwable $e) {
            $errors[] = $e->getTraceAsString();
        }
	}

    function DoInstall()
    {
        global $APPLICATION, $errors;

        $errors = [];

        if ($this->InstallDB() && $this->InstallFiles()) {
            RegisterModule($this->MODULE_ID);
        }

		CAgent::AddAgent(
            self::CURRENCY_EXCHANGE_RATE_AGENT,
            $this->MODULE_ID,
            'Y',
            86400
        );

        $APPLICATION->IncludeAdminFile(
            'Установка модуля ' . $this->MODULE_ID,
            __DIR__ . '/step1.php'
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION, $errors;

        $errors = [];

        CAgent::RemoveAgent(self::CURRENCY_EXCHANGE_RATE_AGENT, $this->MODULE_ID);

        $this->UnInstallFiles();
        $this->UnInstallDB();

        UnRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            'Установка модуля ' . $this->MODULE_ID,
            __DIR__ . '/unstep1.php'
        );
    }
}
