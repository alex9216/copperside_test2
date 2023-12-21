<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<table>
    <thead>
        <tr>
            <th>Id</td>
            <th>Название группы</td>
            <th>Описание Группы</td>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td><?= $arResult['ID']; ?></td>
            <td><?= $arResult['NAME']; ?></td>
            <td><?= $arResult['DESCRIPTION']; ?></td>
        </tr>
    </tbody>
</table>
