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
        <span>{{Ajouter}}</span>
    </div>
    <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
    </div>
    <div class="cursor eqLogicAction logoSecondary" style="position: absolute; left: 0px; top: 0px;">
        <a href="index.php?v=d&m=discordlink&p=discordlinkemote"><img style="margin-top:-32px;" src="plugins/discordlink/plugin_info/discordlink_icon.png" width="75" height="75">
        <br>
        <span>Emojis Settings</span></a>
    </div>
</div>
<legend><i class="fas fa-table"></i> {{Mes Channels}}</legend>
<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
<div class="eqLogicThumbnailContainer">
    <?php
        foreach ($eqLogics as $eqLogic) {
            $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
            echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
            echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
            echo '<br>';
            echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
            echo '</div>';
        }
    ?>
</div>
</div>

<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom du channel}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de channels}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php
                            foreach (jeeObject::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                            }
                        ?>
                   </select>
               </div>
           </div>
	   <div class="form-group">
                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                <div class="col-sm-9">
                 <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
               </div>
           </div>
	<div class="form-group">
		<label class="col-sm-3 control-label"></label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="interactionjeedom"/>{{Interactions Avec Jeedom}}</label>
        </div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Channels : }}</label>
        <div class="col-sm-3">
            <select class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="channelid">       
                <?php
                    $channels = config::byKey('channels', 'discordlink', 'null');
                    if ($channels != "null") {
                        foreach($channels as $channel) {
                            echo '<option value="'.$channel['id'].'">('.$channel['guildName'].') '.$channel['name'].'</option>';
                        }
                    } else {
                        $channels = discordlink::getchannel();
                        if (count($channel) == 0) {
                            echo '<option value="null">Pas de channel disponible</option>';
                        } else {
                            foreach($channels as $channel) {
                                echo '<option value="'.$channel['id'].'">('.$channel['guildName'].') '.$channel['name'].'</option>';
                            }
                        }
                    }
                ?>
            </select>
        </div>
    </div>
    </br>
    </br>
	<div class="form-group">
		<label class="col-sm-3 control-label"></label>
		<div class="col-sm-9">
            <label class="checkbox-inline"><input id="deamoncheck" type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="deamoncheck"/>{{Vérification Démon}}</label>
            <label class="checkbox-inline"><input id="depcheck" type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="depcheck"/>{{Vérification Dépendances}}</label>
            <label class="checkbox-inline"><input id="connectcheck" type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="connectcheck"/>{{Annonce des connection}}</label>
            <?php
                if (discordlink::testplugin('openzwave')) {
                    echo'<label class="checkbox-inline"><input id="zwavecheck" type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="zwavecheck"/>{{Vérification Z-wave}}</label>';
                } else {
                    echo'<div style="visibility: hidden; display: none;"><label class="checkbox-inline"><input id="zwavecheck" type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="zwavecheck"/>{{Vérification Z-wave}}</label></div>';
                    
                }
            ?>
        </div>
    </div>
    <div class="form-group deamon">
        <label class="col-sm-3 control-label">{{Auto-actualisation deamon (cron)}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefreshDeamon" placeholder="{{Auto-actualisation Deamon (cron)}}"/>
        </div>
        <div class="col-sm-1">
            <i class="fas fa-question-circle cursor floatright" id="bt_cronGeneratordeamon"></i>
        </div>
    </div>
    <div class="form-group dependance">
        <label class="col-sm-3 control-label">{{Auto-actualisation dépendances (cron)}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefreshDependances" placeholder="{{Auto-actualisation dépendances (cron)}}"/>
        </div>
        <div class="col-sm-1">
            <i class="fas fa-question-circle cursor floatright" id="bt_cronGeneratorDependance"></i>
        </div>
    </div>
    <div class="form-group zwave">
        <label class="col-sm-3 control-label">{{Auto-actualisation Z-Wave (cron)}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefreshZWave" placeholder="{{Auto-actualisation Z-Wave (cron)}}"/>
        </div>
        <div class="col-sm-1">
            <i class="fas fa-question-circle cursor floatright" id="bt_cronGeneratorzwave"></i>
        </div>
    </div>
    </br>
    </br>
    <div class="form-group zwave">
        <label class="col-sm-3 control-label">{{Node Id Zwave a exclure : }}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="zwaveIdExclude" placeholder="{{exemple : 21 17 58}}"/>
        </div>
    </div>
    <div class="form-group zwave">
        <label class="col-sm-3 control-label">{{Temps maximal entre la dernière reponse (En seconde)(Z-Wave) : }}</label>
        <div class="col-sm-3">
            <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="TempMax" placeholder="{{default : 43200}}"/>
        </div>
    </div>
</fieldset>
</form>
</div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
<!--<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>-->
<div role="tabpanel" class="tab-pane active" id="commandtab">
    <table id="table_cmd" class="table table-bordered table-condensed ui-sortable">
        <thead>
        <tr>
            <th style="width: 40px;">#</th>
            <th style="width: 200px;">{{Nom}}</th>
            <th style="width: 150px;">{{Type}}</th>
            <th style="width: 300px;">{{Commande & Variable}}</th>
            <th style="width: 40px;">{{Min}}</th>
            <th style="width: 40px;">{{Max}}</th>
            <th style="width: 150px;">{{Paramètres}}</th>
            <th style="width: 100px;"></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    </div>
</div>

<?php include_file('desktop', 'discordlink', 'js', 'discordlink');?>
<?php include_file('core', 'plugin.template', 'js');?>
<script type="text/javascript">
setTimeout(() => {setupcase();}, 500);

</script>