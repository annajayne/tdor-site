@echo off

cd /d %~dp0

rem call vendor\bin\phpunit --bootstrap "vendor/autoload.php" "tests/EmailTest"
call vendor\bin\phpunit --bootstrap "bootstrap.php" "tests/utilsTest"

timeout /t 60