@echo off

cls

cd /d %~dp0

call vendor\bin\phpunit --bootstrap "bootstrap.php" "tests/utilsTest"
call vendor\bin\phpunit --bootstrap "bootstrap.php" "tests/DisplayUtilsTest"
call vendor\bin\phpunit --bootstrap "bootstrap.php" "tests/ExporterTest"

timeout /t 60