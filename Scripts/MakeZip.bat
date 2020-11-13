@echo off

set ScriptsFolder=%~dp0

set ExclusionsFileName=MakeZip.exclusions.txt


rem First determine the root directory name. We'll use this in the zipfile name
cd /d %~dp0..

for %%a in ("%cd%\.") do set "RootFolder=%%~nxa"

set BackupFile=%RootFolder%_src_from_%COMPUTERNAME%.7z


rem cd back to the Scripts folder
cd /d %ScriptsFolder%


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
7z a %PwdParam% -r  -mhe -mtc "%BackupFile%" "..\*.*" -xr!".*/" -x@%ExclusionsFileName%

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
