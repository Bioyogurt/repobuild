<?php
// test
exec("cd /home/repobuild/git; git pull");
exec("/usr/bin/rsync -crl /home/repobuild/git/www/ /home/repobuild/www/docs");
exec("/usr/bin/rsync -crl /home/repobuild/git/scripts/ /home/repobuild/share/scripts");
