<?php
// test lalala
exec("cd /home/repobuild/git; git pull");
exec("rsync -crl /home/repobuild/git/www/ /home/repobuild/www/docs");
exec("rsync -crl /home/repobuild/git/scripts/ /home/repobuild/share/scripts");
