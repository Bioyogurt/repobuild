<?php
exec("cd /home/repobuild/share; git pull;", $out);
print_r($out);
