@echo off
setlocal enabledelayedexpansion
title DIENMAYPRO Ultimate Runner v3.8 [Ultimate Automation]
mode con: cols=110 lines=38

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
echo                                 SYSTEM ORCHESTRATOR v3.8 [STABLE]
echo.
echo    [1] DEV MODE        - Dung cho lap trinh (Vite Dev Server)
echo    [2] STABLE RUN      - Chay bang file Build (On dinh 100%%)
echo    [3] RESET DATABASE  - Lam moi Database va Seed du lieu
echo    [4] VIEW SITEMAP    - Xem danh sach duong link website
echo    [5] FULL AUTOMATION - [ONE CLICK] Reset DB + Build + Chay ngay
echo    [6] EXIT            - Thoat
echo.
echo    ----------------------------------------------------------------------------------------
set /p choice="   >> Nhap lua chon cua ban (1-6): "

if "%choice%"=="1" goto DEV_MODE
if "%choice%"=="2" goto STABLE_RUN
if "%choice%"=="3" goto RESET_DB
if "%choice%"=="4" goto VIEW_SITEMAP
if "%choice%"=="5" goto FULL_AUTO
if "%choice%"=="6" exit
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
echo    [+] Quan ly tai khoan:   http://127.0.0.1:8000/admin/users
echo    --- TRANG CHU KHACH HANG (FRONTEND) ---
echo    [+] Trang chu:           http://127.0.0.1:8000/
echo    ----------------------------------------------------------------------------------------
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

echo    [ STEP 2 ] KHOI TAO SERVERS...
start "Laravel Server" cmd /c "php artisan serve"
if "%MODE%"=="DEV" (
    start "Vite Dev" cmd /c "npm run dev"
    timeout /t 10 > nul
) else (
    timeout /t 3 > nul
)

echo    [ STEP 3 ] HOAN THIEN...
start http://127.0.0.1:8000/admin
echo.
echo    *************************************************
echo    *   DIENMAYPRO DANG CHAY - NHAN PHIM DE TAT     *
echo    *************************************************
pause > nul
taskkill /f /im php.exe >nul 2>&1
exit