@echo on

set BackupFile=tdor_deploy.zip


if EXIST %BackupFile% del %BackupFile% /F


:DoBackup
echo Zipping files...
echo.


cd "..\..\..\Scripts"

rem Zip the rest
7z a -tzip -r  %BackupFile% "..\TDoR\*.*" -xr!".*/" -x@MakeDeploymentZip.exclusions.txt

if ERRORLEVEL 1 goto ZipError

goto Exit

:DoIt

:ZipError
  echo.
  echo Unable to perform backup - Zip error
  echo.
  pause
  goto Exit

:Exit
  echo.
  echo.
