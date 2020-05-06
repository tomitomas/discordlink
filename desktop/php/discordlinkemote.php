<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('discordlink');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
  <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
  <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter un emojy}}</span>
    </div>
    <div class="cursor eqLogicAction logoSecondary" style="position: absolute; left: 0px; top: 0px;">
        <a href="index.php?v=d&m=discordlink&p=discordlink"><img style="margin-top:-32px;" src="plugins/discordlink/plugin_info/discordlink_icon.png" width="75" height="75">
        <br>
        <span>DiscordLink</span></a>
    </div>
</div>

<div class="input-group pull-right" style="display:inline-flex">
    <span class="input-group-btn">
        <a class="btn btn-sm btn-success eqLogicAction" data-action="saveemojy"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-success btn-sm cmdAction" id="bt_addemojy"><i class="fa fa-plus-circle"></i> Ajouter un Emojy</a>
    </span>
</div>

<div role="tabpanel" class="tab-pane active" id="commandtab">
    <table id="table_cmd" class="table table-bordered table-condensed ui-sortable">
        <thead>
        <tr class="emojy">
            <th style="">Clée emojy</th>
            <th style="">Code Emojy</th>
            <th style="width: 100px;"></th>
        </tr>
        </thead>
        <tbody>

    </table>
    </div>

<?php include_file('desktop', 'discordlinkemote', 'js', 'discordlink');?>
<?php include_file('core', 'plugin.template', 'js');?>
