@echo off
title Bionett - Laravel Dev Server
echo Starting Bionett...
echo.

start "Laravel - Backend" cmd /k "cd /d %~dp0 && php artisan serve --host=0.0.0.0 --port=8000"

timeout /t 2 /nobreak >nul

start "Ngrok - HTTPS Tunnel" cmd /k "ngrok http 8000"

:: Get local IP address
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr "192.168"') do (
    set IP=%%a
    goto :found
)
:found
set IP=%IP: =%

echo  From THIS computer:
echo    http://localhost:8000
echo.
echo  From phone (same WiFi):
echo    http://%IP%:8000
echo.
echo  For PUSH NOTIFICATIONS on phone:
echo    Check the ngrok window for the https:// URL
echo    Use that URL on your phone instead
echo.
echo You can close this window. Server and ngrok windows will stay open.
pause
