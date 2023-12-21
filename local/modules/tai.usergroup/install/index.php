<?
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Throwable;

class tai_usergroup extends CModule
{
    public $MODULE_ID = 'tai.usergroup';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $PARTNER_NAME;

    public $MODULE_COMPONENTS_DIR;
    public $MODULE_COMPONENTS_INSTALL_DIR;
    public $MODULE_COMPONENTS_NAMES;

    public function __construct()
    {
        $arModuleVersion = array();

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));

        include($path . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = 'Tai: Модуль списка групп пользователей';
        $this->MODULE_DESCRIPTION = 'Модуль устанавливающий компоненты вывода списка групп пользователей и информацию о конкретной группе';
        $this->PARTNER_NAME = 'Татаринцев Алексей Иванович';

        $this->MODULE_COMPONENTS_DIR = __DIR__ . '/components';
        $this->MODULE_COMPONENTS_INSTALL_DIR = Application::getDocumentRoot() . '/local/components';
        $this->MODULE_COMPONENTS_NAMES = [
            'user.group',
            'user.group.detail',
            'user.group.list'
        ];
    }

	function InstallFiles()
	{
		return CopyDirFiles(
            $this->MODULE_COMPONENTS_DIR,
            $this->MODULE_COMPONENTS_INSTALL_DIR,
            true,
            true,
            false
        );
	}

	function UnInstallFiles()
	{
        global $errors;

        try {
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

        if ($this->InstallFiles()) {
            RegisterModule($this->MODULE_ID);
        }

        $APPLICATION->IncludeAdminFile(
            'Установка модуля ' . $this->MODULE_ID,
            __DIR__ . '/step1.php'
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION, $errors;

        $errors = [];

        $this->UnInstallFiles();

        UnRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            'Установка модуля ' . $this->MODULE_ID,
            __DIR__ . '/unstep1.php'
        );
    }
}
