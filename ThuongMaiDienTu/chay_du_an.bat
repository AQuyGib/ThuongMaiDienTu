@echo off
setlocal enabledelayedexpansion
title DIENMAYPRO Ultimate Runner v4.0 [Maintenance Pro]
mode con: cols=110 lines=40

:: ======================================================================
:: KIEM TRA CAC LENH CO BAN
:: ======================================================================
where php >nul 2>&1 || (echo [LOI] Chua cai dat PHP! & pause & exit)
where npm >nul 2>&1 || (echo [LOI] Chua cai dat Node.js! & pause & exit)
where composer >nul 2>&1 || (echo [LOI] Chua cai dat Composer! & pause & exit)

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
echo                                 SYSTEM ORCHESTRATOR v4.0 [MAINTENANCE]
echo.
echo    [1] DEV MODE        - Dung cho lap trinh (Vite Dev Server)
echo    [2] STABLE RUN      - Chay bang file Build (On dinh 100%%)
echo    [3] RESET DATABASE  - Lam moi Database va Seed du lieu
echo    [4] VIEW SITEMAP    - Xem danh sach duong link website
echo    [5] FULL AUTOMATION - [ONE CLICK] Reset DB + Build + Chay ngay
echo    [6] INITIALIZE      - Cai dat moi cac thu vien (Composer + NPM install)
echo    [7] UPGRADE ALL     - Nang cap toan bo thu vien len ban moi nhat
echo    [8] EXIT            - Thoat
echo.
echo    ----------------------------------------------------------------------------------------
set /p choice="   >> Nhap lua chon cua ban (1-8): "

if "%choice%"=="1" goto DEV_MODE
if "%choice%"=="2" goto STABLE_RUN
if "%choice%"=="3" goto RESET_DB
if "%choice%"=="4" goto VIEW_SITEMAP
if "%choice%"=="5" goto FULL_AUTO
if "%choice%"=="6" goto INITIALIZE
if "%choice%"=="7" goto UPGRADE_ALL
if "%choice%"=="8" exit
goto MENU

:INITIALIZE
cls
color 0e
echo.
echo    [ HANH DONG ] Dang tai day du thu vien cho du an...
echo    -------------------------------------------------
echo    [1/2] Dang tai Backend (Composer Install)...
call composer install --prefer-dist
echo    [2/2] Dang tai Frontend (NPM Install)...
call npm install
echo    [ OK ] Da tai xong tat ca thu vien!
pause
goto MENU

:UPGRADE_ALL
cls
color 0e
echo.
echo    [ HANH DONG ] Dang nang cap toan bo he thong...
echo    -------------------------------------------------
echo    [1/2] Dang nang cap Backend (Composer Update)...
call composer update
echo    [2/2] Dang nang cap Frontend (NPM Update)...
call npm update
echo    [ OK ] Da nang cap len cac phien ban moi nhat!
pause
goto MENU

:FULL_AUTO
cls
color 0b
echo.
echo    [1/3] DANG RESET DATABASE...
call php artisan migrate:fresh --seed
echo    [2/3] DANG BUILD ASSETS...
if exist public\hot del /f /q public\hot
call php artisan optimize:clear
call npm run build
echo    [3/3] DANG KHOI DONG SERVERS...
set MODE=STABLE
goto PROCESS

:VIEW_SITEMAP
cls
color 0f
echo.
echo    +======================================================================================+
echo    ^|                   DANH SACH DUONG LINK WEBSITE DIENMAYPRO                            ^|
echo    +======================================================================================+
echo.
echo    --- HE THONG QUAN TRI (ADMIN) ---
echo    [+] Trang chu Admin:     http://127.0.0.1:8000/admin
echo    --- TRANG CHU KHACH HANG (FRONTEND) ---
echo    [+] Trang chu:           http://127.0.0.1:8000/
echo.
echo    Nhan phim bat ky de quay lai Menu...
pause > nul
goto MENU

:DEV_MODE
cls
echo.
echo    [ HANH DONG ] Dang khoi dong che do DEV...
set MODE=DEV
goto PROCESS

:STABLE_RUN
cls
color 0e
echo.
echo    [1/4] Dang don dep tien trinh cu...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
if exist public\hot del /f /q public\hot
php artisan optimize:clear > nul
echo    [2/4] Dang build lai Assets...
call npm run build
set MODE=STABLE
goto PROCESS

:RESET_DB
cls
color 0c
echo    [!] Luu y: Toan bo du lieu cu se bi xoa sach.
set /p confirm="    >> Ban co chac chan muon tiep tuc? (y/n): "
if /i "%confirm%" neq "y" goto MENU
php artisan migrate:fresh --seed
php artisan optimize:clear
echo    [ OK ] Database da lam moi xong!
pause
goto MENU

:PROCESS
color 0a
echo.
echo    [ STEP 1 ] KIEM TRA MYSQL...
tasklist /fi "imagename eq mysqld.exe" | findstr /i "mysqld.exe" > nul
if %errorlevel% neq 0 (
    color 0c
    echo    [ LOI ] MySQL chua bat! 
    pause
    goto MENU
)

if not exist .env (
    copy .env.example .env > nul
    php artisan key:generate
)

echo    [ STEP 2 ] KHOI TAO SERVERS...
start "Laravel Server" cmd /c "php artisan serve"
if "%MODE%"=="DEV" (
    start "Vite Dev" cmd /c "npm run dev"
    timeout /t 10 > nul
) else (
    timeout /t 3 > nul
)

echo    [ STEP 3 ] HOAN THIEN...
start http://127.0.0.1:8000/
echo.
echo    *************************************************
echo    *   DIENMAYPRO DANG CHAY - NHAN PHIM DE TAT     *
echo    *************************************************
pause > nul
taskkill /f /im php.exe >nul 2>&1
exit