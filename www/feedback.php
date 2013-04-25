<?php

include 'inc/init.php';
dbc();
auth(true);

$page['title'] = 'Repobuild';
tpl_head($page);
if(isset($_POST['text']) && isset($_POST['subj']) && strlen($_POST['text']) >= 4) {
        require_once 'Mail.php';
        $to = 'avs@repobuild.com';
        $bcc = 'fds@repobuild.com';

        $from = 'no-reply@repobuild.com';
        $subject = $_POST['subj'];
        $body = $USER['username'].' ('.$USER['email'].') пишет:'."\n\n".htmlspecialchars($_POST['text'])."\n";

        $host = 'smtp.yandex.ru';
        $username = 'no-reply@repobuild.com';
        $password = 'q1w2e3r4t5y6';

        $headers = array ('From' => $from, 'To' => $to, 'Subject' => $subject);
        $smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => true, 'username' => $username, 'password' => $password ));
        $mail = $smtp->send($to.', '.$bcc, $headers, $body);
//        $mail = $smtp->send($to, $headers, $body);
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
