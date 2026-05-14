@echo off
setlocal enabledelayedexpansion
title DIENMAYPRO Ultimate Runner v3.3
mode con: cols=100 lines=35

:MENU
cls
color 0b
echo.
echo    +======================================================================================+
echo    ^|  _____  _____ ______ _   _ __  __          __     __ _____  _____   ____         ^|
echo    ^| ^|  __ \^|_   _^|  ____^| \ ^| ^|  \/  ^|   /\   \ \   / /^|  __ \^|  __ \ / __ \        ^|
echo    ^| ^| ^|  ^| ^| ^| ^| ^| ^|__  ^|  \^| ^| \  / ^|  /  \   \ \_/ / ^| ^|__) ^| ^|__) ^| ^|  ^| ^|       ^|
echo    ^| ^| ^|  ^| ^| ^| ^| ^|  __^| ^| . ` ^| ^|\ /^| ^| / /\ \   \   /  ^|  ___/^|  _  /^| ^|  ^| ^|       ^|
echo    ^| ^| ^|__^| ^|_^| ^|_^| ^|____^| ^|\  ^| ^|  ^| ^|/ ____ \   ^| ^|   ^| ^|    ^| ^| \ \^| ^|__^| ^|       ^|
echo    ^| ^|_____/^|_____^|______^|_^| \_^|_^|  ^|_/_/    \_\  ^|_^|   ^|_^|    ^|_^|  \_\ \____/        ^|
echo    +======================================================================================+
echo                                 SYSTEM ORCHESTRATOR v3.3 [STABLE]
echo.
echo    [1] START PROJECT      - Khoi dong Laravel + Vite
echo    [2] EMERGENCY REPAIR   - Sua loi Loading (Clean, Rebuild)
echo    [3] RELOAD DATABASE    - Reset du lieu (Xoa, Seed)
echo    [4] EXIT               - Thoat
echo.
echo    ----------------------------------------------------------------------------------------
set /p choice="   >> Nhap lua chon cua ban (1-4): "

if "%choice%"=="1" goto RUN_NORMAL
if "%choice%"=="2" goto FIX_LOADING
if "%choice%"=="3" goto RESET_DB
if "%choice%"=="4" exit
goto MENU

:RUN_NORMAL
cls
echo.
echo    [ HANH DONG ] Dang bat dau quy trinh khoi chay...
echo    -------------------------------------------------
goto PROCESS

:FIX_LOADING
cls
color 0e
echo.
echo    +=================================================+
echo    ^|           DANG TIEN HANH SUA LOI GIAO DIEN      ^|
echo    +=================================================+
echo.
echo    [1/4] Dang giai phong cac cong ket noi...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1

echo    [2/4] Dang don dep file rac...
if exist public\hot del /f /q public\hot
php artisan view:clear > nul
php artisan config:clear > nul

echo    [3/4] Dang build lai Assets...
call npm run build

echo    [4/4] Hoan tat! Dang chuyen sang khoi dong...
timeout /t 2
goto PROCESS

:RESET_DB
cls
color 0c
echo.
echo    +=================================================+
echo    ^|             CANH BAO: LAM MOI DATABASE          ^|
echo    +=================================================+
echo    [!] TOAN BO DU LIEU CU SE BI XOA SACH!
echo.
set /p confirm="    >> Ban co chac chan muon tiep tuc? (y/n): "
if /i "%confirm%" neq "y" goto MENU

echo.
echo    [1/3] Dang xoa va tao lai Database...
php artisan migrate:fresh

echo    [2/3] Dang nap du lieu mau DIENMAYPRO...
php artisan db:seed

echo    [3/3] Dang don dep Cache...
php artisan cache:clear
php artisan config:clear

echo.
echo    [ Thanh Cong ] Database da san sang!
pause
goto MENU

:PROCESS
color 0a
echo.
echo    [ STEP 1 ] KIEM TRA MOI TRUONG...
tasklist /fi "imagename eq mysqld.exe" | findstr /i "mysqld.exe" > nul
if %errorlevel% neq 0 (
    color 0c
    echo    [ LOI ] MySQL chua duoc bat! 
    echo    Vui long mo XAMPP va nhan START cho MySQL truoc.
    pause
    goto MENU
)
echo    [ OK ] MySQL dang hoat dong.

if not exist .env (
    copy .env.example .env > nul
    php artisan key:generate
)

echo.
echo    [ STEP 2 ] KHOI TAO SERVERS...
start "Laravel Server" cmd /c "php artisan serve"
start "Vite Dev" cmd /c "npm run dev"

echo    [ STEP 3 ] HOAN THIEN...
echo    Dang chuan bi moi truong quan tri (10s)...
echo    ..........
timeout /t 10 > nul
start http://127.0.0.1:8000/admin

echo.
echo    *************************************************
echo    *   DIENMAYPRO DANG CHAY - NHAN PHIM DE TAT     *
echo    *************************************************
pause > nul
taskkill /f /im php.exe >nul 2>&1
exit