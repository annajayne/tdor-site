@echo off

set BackupFile=TDoR_src_from_%COMPUTERNAME%.7z


if EXIST %BackupFile% del %BackupFile% /F


:DoBackup
echo Zipping files...
echo.


set PwdParam=
if not "%RB_SOURCEZIP_PWD%"== "" (
  set PwdParam=-p%RB_SOURCEZIP_PWD%
)


cd "..\..\..\Scripts"

rem Zip the rest
7z a %PwdParam% -r  -mhe -mtc %BackupFile% "..\*.*" -xr!".*/" -x@MakeZip.exclusions.txt

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
