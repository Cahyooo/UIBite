@echo off
cd /d "%~dp0"
echo Starting UIBite at http://localhost:8000
echo Make sure MySQL is running and database/uibite.sql has been imported.
php -S localhost:8000 -t public
pause
