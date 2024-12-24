@echo off
REM Change to the current directory (where the .bat file is located)
cd /d "%~dp0"

REM Start the PHP built-in server
php -S localhost:8000

REM The server will stop when the window is closed or Ctrl+C is pressed