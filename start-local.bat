@echo off
cd /d "%~dp0"
echo Starting UIBite at http://localhost:8000
echo Listening on localhost only (public/LAN access is disabled).
echo Make sure MySQL is running and database/uibite.sql has been imported.
php -S localhost:8000 -t public
pause
