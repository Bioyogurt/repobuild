<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$page['title'] = 'Repositories on Repobuild';
$content = "";

$sth = $dbh->prepare("SELECT * FROM repos WHERE user = :userid");
$sth->bindParam(':userid', $USER['id']);
$sth->execute();
if($sth->rowCount() > 0) {
    $content .= "<table class=\"table table-hover\">";
    $content .= "<thead><tr><th></th><th>name</th><th>os</th><th>arch</th><th>url</th></tr></thead><tbody>";
    while($row = $sth->fetch()) {
		$sth2 = $dbh->prepare("SELECT COUNT(*) FROM builds WHERE repo = :repoid");
                $sth2->bindParam(':repoid', $row['id']);
                $sth2->execute();
                $cnt = $sth2->fetchColumn();
        $content .= "<tr>";
        $content .= '<td width="1%"><div class="btn-group">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cog"></i> <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                            <li><a tabindex="-1" href="/takedeleterepo.php?id='.$row['id'].'"><i class="icon-remove"></i> Delete</a></li>
                        </ul></div></td>';
        $content .= "<td><b><a href=\"repo.php?id=".$row['id']."\">".$row['name']."</a></b></td>";
        $content .= "<td>".$_os[$row['os']]['display_name']."</td>";
        $content .= "<td>".$_arch[$row['arch']]['display_name']."</td>";

		if($cnt == 0)
			$content .= "<td><span class=\"text-error\">(please, add packets to repo)</span></td>";
        elseif(is_dir("../../share/repos/".$row['hash']))
            $content .= "<td><a href='http://repo.repobuild.com/".$row['hash']."' target='_blank'>http://repo.repobuild.com/".$row['hash']."</a></td>";
        else
            $content .= "<td><p class=\"muted\"><i class=\"icon-refresh icon-spin\"></i> creating..</p></td>";
    }
    $content .= "</tr></tbody></table>\n";
} else
    $content .= "Repos not exists<br /><br />";

$content .= '<a class="btn btn-primary" href="/create.php"><i class="icon-plus"></i> Create Repo</a>';

tpl_head($page);
echo $content;
tpl_foot();
