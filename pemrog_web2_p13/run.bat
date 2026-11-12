@echo off
title PHP Web Server - Pertemuan 13
echo Menjalankan PHP Server di http://localhost:8001 ...
echo [JANGAN TUTUP JENDELA INI SELAMA WEBSITE DIAKSES]
echo.

if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8001
) else if exist "D:\xampp\php\php.exe" (
    "D:\xampp\php\php.exe" -S localhost:8001
) else (
    php -S localhost:8001
)

pause