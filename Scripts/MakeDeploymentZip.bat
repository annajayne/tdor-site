@echo on

set DepoyZipFile=%~dp0..\Deploy\tdor_deploy.zip


if EXIST "%DepoyZipFile%" del "%DepoyZipFile%" /f


:DoBackup
echo Zipping files...
echo.


rem Zip the rest
7z a -tzip -r  "%DepoyZipFile%" "..\Projects\TDoR\src\*.*" -xr!".*/" -x@MakeDeploymentZip.exclusions.txt

if ERRORLEVEL 1 goto ZipError

goto Exit



:ZipError
  echo.
  echo Unable to perform backup - Zip error
  echo.
  pause
  goto Exit

:Exit
  echo.
  echo.
