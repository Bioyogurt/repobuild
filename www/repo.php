<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$page['title'] = 'Repositories on Repobuild';
$content = "";
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	tpl_err("Error!");
}
$id = $_GET['id'];
$sth = $dbh->prepare("SELECT * FROM repos WHERE user = :userid AND id = :repoid");
$sth->bindParam(':userid', $USER['id']);
$sth->bindParam(':repoid', $id);
$sth->execute();

if($sth->rowCount() == 0) {
	tpl_err("Error!");
}

$sth = $dbh->prepare("SELECT * FROM builds WHERE repo = :repoid");
$sth->bindParam(':repoid', $id);
$sth->execute();

// List packets
if($sth->rowCount() > 0) {
    $content .= "<table class=\"table table-hover\">";
    $content .= "<thead><tr><th></th><th>Packets</th><th></th></thead><tbody>";
    while($row = $sth->fetch()) {
        $content .= "<tr>";
        $content .= '<td width="1%"><div class="btn-group">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cog"></i> <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                            <li><a tabindex="-1" href="#m'.$row['packet'].'" data-toggle="modal"><i class="icon-pencil"></i> Edit</a></li>
                            <li class="divider"></li>
                            <li><a tabindex="-1" href="takedeletebuild.php?id='.$row['id'].'"><i class="icon-remove"></i> Delete</a></li>
                        </ul></div></td>';
        $content .= "<td><b>".$_pkgs[$row['packet']]['name']."</b> <i class=\"muted\">".$row['version']."</i>";

		if($row['key'] == "")
			$content .= " <span class=\"text-error\"> (please, edit build params)</span>";

        $content .= '</td><td width="1%"><button id="b'.$row['packet'].'" type="button" class="btn" data-toggle="button"><i class="icon-angle-down"></i></button></td>';

		$sth2 = $dbh->prepare("SELECT * FROM builds_opts WHERE build = :buildid");
                $sth2->bindParam(':buildid', $row['id']);
		$sth2->execute();

		$opts = array();
		while($row2 = $sth2->fetch()) {
			if($row2['value'] <> "")
				$opts[] = $_opts[$row2['option']]['name']."=".$row2['value'];
			else
				$opts[] = $_opts[$row2['option']]['name'];

			$opt[$row2['option']] = $row2;
		}

		$content .= '<tr id="p'.$row['packet'].'" style="display: none"><td></td><td colspan=2>'.implode("<br />\n", $opts).'</td></tr>';

		$modals = '<script>$("#b'.$row['packet'].'").click(function () {$("#p'.$row['packet'].'").toggle();});</script>';
		$modals .= '<div id="m'.$row['packet'].'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
							<h3>'.$_pkgs[$row['packet']]['name'].'</h3>
						</div>
						<div class="modal-body">';

		$modals .= '<form id="f'.$row['packet'].'" method="POST" action="/takeeditbuild.php?id='.$row['id'].'&pack='.$row['packet'].'"><table class="table">';
		foreach($_opts as $o) {
			$modals .= '<tr><td><input id="o'.$o['id'].'" name="opts[]" value="'.$o['id'].'" type="checkbox"';
			if(isset($opt[$o['id']]))
				$modals .= ' checked="checked"';
			$modals .= ' /></td><td> <label for="o'.$o['id'].'"> '.$o['name'].'</label></td><td>';

			if($o['allow_custom'] == 'yes') {
				if($o['custom'] <> "") {
					$modals .= '<input name="v'.$o['id'].'" type="text" value="';
					if(isset($opt[$o['id']]) && $opt[$o['id']] <> "")
						$modals .= $opt[$o['id']]['value'];
					else
						$modals .= $o['custom'];
					$modals .= '">';
				}
			} else {
				$modals .= $o['custom'];
			}
			$modals .= "</tr>\n";
		}
		$modals .= '</table>';
		$modals .= '</div>
						<div class="modal-footer">
							<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
							<button id="s'.$row['packet'].'"class="btn btn-primary">Save</button>
						</div>
					</div></form>';
		$modals .= '<script>
						$("#s'.$row['packet'].'").click(function() {
							$("#f'.$row['packet'].'").submit();
						});
					</script>';
    }
    $content .= "</tbody></table>\n";
	$content .= $modals;
} else
    $content .= "Repo is empty<br /><br />";
///



$content .= '<a class="btn btn-primary" href="#addpack" role="button" data-toggle="modal"><i class="icon-plus"></i> Add Packet</a>';
$content .= '<div id="addpack" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">Add packet</h3>
				</div>
				<div class="modal-body">';

$sth = $dbh->prepare("SELECT id, display_name, version FROM packets WHERE id NOT IN (SELECT packet FROM builds WHERE repo = :repoid)");
$sth->bindParam(':repoid', $id);
$sth->execute();
if($sth->rowCount() > 0) {
	$content .= '<form id="addfrm" method="POST" action="addpack.php?repo='.htmlspecialchars($id).'">
					<select name="packet">';
	while($row = $sth->fetch()) {
		$content .= '<option value="'.$row['id'].'">'.htmlspecialchars($row['display_name']).' '.$row['version'].'</option>';
	}

	$content .= '</select></form>';
	$add_enabled = true;
} else {
	$content .= 'not aviable packets';
	$add_enabled = false;
}

$content .='				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					'.($add_enabled ? '<button id="addbtn" class="btn btn-primary">Add</button><script>$("#addbtn").click(function() { $("#addfrm").submit(); });</script>':'').'
				</div>
			</div>';

tpl_head($page);
echo $content;
tpl_foot();
