<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('discordlink');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div id="div_AboAlert" style="display: none;"></div>
<div class="input-group" style="display:inline-flex">
    <span class="input-group-btn">
        <a class="btn btn-sm btn-success" href="index.php?v=d&m=discordlink&p=discordlink"><i class="fas fa-check-circle"></i>{{Retour à }} Discord Link</a>
    </span>
</div>
<div class="input-group pull-right" style="display:inline-flex">
    <span class="input-group-btn">
        <a class="btn btn-sm btn-success eqLogicAction" data-action="saveemojy"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-success btn-sm cmdAction" id="bt_addemojy"><i class="fa fa-plus-circle"></i>{{Ajouter un émoji}}</a>
        <a class="btn btn-danger btn-sm cmdAction" id="bt_reset"><i class="fa fa-plus-circle"></i>{{Reset émoji}}</a>
    </span>
</div>

<div role="tabpanel" class="tab-pane active" id="commandtab">
    <table id="table_cmd" class="table table-bordered table-condensed ui-sortable">
        <thead>
        <tr class="emojy">
            <th style="">{{Clé émoji}}</th>
            <th style="">{{Code émoji}}</th>
            <th style="width: 100px;"></th>
        </tr>
        </thead>
        <tbody>

    </table>
    </div>

<?php include_file('desktop', 'discordlinkemote', 'js', 'discordlink');?>
<?php include_file('core', 'plugin.template', 'js');?>
