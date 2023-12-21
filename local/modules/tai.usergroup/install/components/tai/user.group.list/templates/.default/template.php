<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? if (!empty($arResult['ITEMS']) && is_array($arResult['ITEMS'])): ?>
    <table>
        <thead>
            <tr>
                <th>Id</td>
                <th>Название группы</td>
                <th>Описание Группы</td>
                <th>Ссылка</td>
            </tr>
        </thead>

        <tbody>
            <? foreach ($arResult['ITEMS'] as $item): ?>
                <tr>
                    <td><?= $item['ID']; ?></td>
                    <td><?= $item['NAME']; ?></td>
                    <td><?= $item['DESCRIPTION']; ?></td>
                    <td><a href="<?= $item['DETAIL_PAGE_URL']; ?>"><?= $item['DETAIL_PAGE_URL']; ?></a></td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>
