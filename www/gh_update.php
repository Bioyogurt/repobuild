<?php
// test
exec("cd /home/repobuild/git; git pull");
exec("rsync -curl /home/repobuild/git/www/ /home/repobuild/www/docs");
exec("rsync -curl /home/repobuild/git/scripts/ /home/repobuild/share/scripts");
