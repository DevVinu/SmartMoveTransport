@echo off
echo ===================================================================
echo   SmartMove Transport Solutions - MongoDB Initializer
echo ===================================================================
echo Connecting to mongodb://localhost:27017/smartmove_db ...
echo.

where mongosh >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] 'mongosh' command was not found in your system PATH.
    echo Please ensure MongoDB Shell is installed or run via mongosh full path.
    pause
    exit /b 1
)

mongosh "mongodb://localhost:27017/smartmove_db" "%~dp0mongodb_setup.js"

echo.
echo ===================================================================
echo   MongoDB Initialization Completed!
echo ===================================================================
pause
