<?
/** @global CMain $APPLICATION */
/** @global string $RestoreDefaults */
/** @global string $Update */

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use CAdminMessage;
use Tai\CurrencyExchangeRate\Api\Cbr;

$module_id = 'tai.currencyexchangerate';

$aTabs = array(
	array('DIV' => 'edit_settings', 'TAB' => 'Настройки', 'TITLE' => 'Настройки параметров модуля'),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && strlen($Update) > 0 && check_bitrix_sessid()) {
    Option::set($module_id, 'tcer', '');

    if (!empty($_POST['tcer']) && is_array($_POST['tcer'])) {
        $tcer = $_POST['tcer'];

        if (!empty($tcer['valutes']) && in_array('', $tcer['valute'])) {
            $tcer['valute'] = [];
        }

        Option::set($module_id, 'tcer', serialize($tcer));
    }

    $tcer = unserialize(Option::get($module_id, 'tcer'));

    LocalRedirect($APPLICATION->GetCurPage().'?mid='.$module_id.'&lang='.LANGUAGE_ID.'&'.$tabControl->ActiveTabParam());
}

$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=$module_id?>&lang=<?=LANGUAGE_ID?>" name="opt_form">
<?=bitrix_sessid_post();
$tabControl->BeginNextTab();
?>
    <tr class="heading">
        <td colspan="2">Курсы валют</td>
    </tr>

	<tr>
		<td colspan="2">
                <?
                $tcer = unserialize(Option::get($module_id, 'tcer', ''));

                if (Loader::includeModule($module_id)) {
                    $cbr = new Cbr();
                    $cbrValutes = $cbr->getEnumValutes();
                }
                ?>

				<table cellspacing="5" cellpadding="0" border="0" width="100%" align="center">
					<tr>
						<td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><label for="order-status">Валюты ЦБ РФ:</label></td>

						<td width="60%" class="adm-detail-content-cell-r">
                            <? if (!empty($cbrValutes)): ?>
                                <select id="valute" name="tcer[valute][]" size="10" multiple>
                                    <option value=""<? if (empty($tcer['valute'])): ?> selected<? endif; ?>>Все</option>
                                    <? foreach ($cbrValutes as $cbrValute): ?>
                                        <option
                                            value="<?= $cbrValute['CODE'] ?>"
                                            <? if (!empty($tcer['valute']) && in_array($cbrValute['CODE'], $tcer['valute'])): ?> selected<? endif; ?>
                                        >[<?= $cbrValute['CODE'] ?>]: <?= $cbrValute['NAME'] ?></option>
                                    <? endforeach; ?>
                                </select>
                            <? else: ?>
                                <? CAdminMessage::ShowMessage(GetMessage('TCER_COULD_NOT_GET_VALUTES')) ?>
                            <? endif; ?>
                        </td>
					</tr>
                </table>
        </td>
    </tr>

<? $tabControl->Buttons(); ?>

<input type="submit" name="Update" value="Сохранить" class="adm-btn-save">
<input type="hidden" name="Update" value="Y">

<? $tabControl->End(); ?>

</form>
