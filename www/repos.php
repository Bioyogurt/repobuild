<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$page['title'] = 'Repositories on Repobuild';
$content = "";

$sql = "SELECT * FROM repos WHERE user = ".sqlesc($USER['id']);
$res = sql_query($sql);
if(mysql_num_rows($res) > 0) {
    $content .= "<table class=\"table table-hover\">";
    $content .= "<thead><tr><th></th><th>name</th><th>os</th><th>arch</th><th>url</th></tr></thead><tbody>";
    while($row = mysql_fetch_assoc($res)) {
		$sql = "SELECT COUNT(*) FROM builds WHERE repo = ".sqlesc($row['id']);
		$cnt = mysql_fetch_row(sql_query($sql));
        $content .= "<tr>";
        $content .= '<td width="1%"><div class="btn-group">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cog"></i> <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                            <li><a tabindex="-1" href="/takedeleterepo.php?id='.$row['id'].'"><i class="icon-remove"></i> Delete</a></li>
                        </ul></div></td>';
        $content .= "<td><b><a href=\"repo.php?id=".$row['id']."\">".$row['name']."</a></b></td>";
        $content .= "<td>".$_os[$row['os']]['display_name']."</td>";
        $content .= "<td>".$_arch[$row['arch']]['display_name']."</td>";

		if($cnt[0] == 0)
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
