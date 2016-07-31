#!/bin/bash
pushd /var/www/html;
sudo php -r 'define("BASE", "http://localhost/"); require "/var/www/html/controllers/homeController.php"; require "/var/www/html/config/dbconfig.inc.php"; define("user_key", "");$c = new homeController; $c->timerAction();'
popd;
