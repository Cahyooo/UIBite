@echo off
cd /d "%~dp0"
echo Installing Tailwind CLI dependencies...
npm install
if errorlevel 1 goto failed
echo Building production CSS...
npm run build
if errorlevel 1 goto failed
echo.
echo Tailwind CSS build completed.
pause
exit /b 0
:failed
echo.
echo Build failed. Check Node.js, npm, and your internet connection.
pause
exit /b 1
