<?php

include 'inc/init.php';
dbc();
auth(true);

$page['title'] = 'Repobuild';
tpl_head($page);
if(isset($_POST['text']) && isset($_POST['subj']) && strlen($_POST['text']) >= 4) {
        $to = array(
			'avs@repobuild.com',
			'fds@repobuild.com'
		    );
        $subject = $_POST['subj'];
        $body = $USER['username'].' ( '.$USER['email'].' ) пишет:'."\n\n".htmlspecialchars($_POST['text'])."\n";

	send_mail($to, $subject, $body);

	echo "<meta http-equiv=refresh content='5; URL=/'>";
	echo "Thank you for yuor feedback!";
} else {
    echo '<form action="feedback.php" method="post">
	    <p>Please send your feedback on russian or english language.</p>
	    <input name="subj" type="text" placeholder="Subject"><br />
	    <textarea name="text" style="width: 480px; height: 150px;"></textarea><br />
	    <button type="submit" class="btn">send</button>
	</form>';
}
tpl_foot();
