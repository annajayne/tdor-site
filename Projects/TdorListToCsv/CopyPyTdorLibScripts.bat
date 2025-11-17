rem Copies the PyTdorLib scripts into this folder - this is needed to run TdorListToCsv from the Terminal

@echo off

pushd "%~dp0"

xcopy ..\PyTdorLib\*.py * /s /y

popd

time /t 5

