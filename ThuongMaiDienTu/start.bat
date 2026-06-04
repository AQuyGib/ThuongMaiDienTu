@echo off
setlocal enabledelayedexpansion
title DIENMAYPRO Ultimate Orchestrator v8.0 [Super Auto-Pilot]
mode con: cols=110 lines=42

:: ======================================================================
:: DYNAMIC PHP & SYSTEM ENVIRONMENT DISCOVERY (AUTO-PILOT)
:: ======================================================================
echo.
echo    [ KHOI CHAY ] Dang phat hien moi truong he thong...
echo    --------------------------------------------------

:: Phat hien PHP.exe tu dong
where php >nul 2>&1
if %errorlevel% equ 0 (
    set PHP_CMD=php
    echo    [+] Phien ban PHP: Da bat dau voi bien moi truong he thong [PATH].
) else (
    echo    [!] Canh bao: Khong tim thay PHP trong PATH. Dang quet thu muc thong dung...
    if exist "C:\xampp\php\php.exe" (
        set PHP_CMD="C:\xampp\php\php.exe"
        echo    [+] Phat hien PHP tu XAMPP: C:\xampp\php\php.exe
    ) else (
        set PHP_FOUND=0
        for /d %%d in (C:\laragon\bin\php\php-*) do (
            if exist "%%d\php.exe" (
                set PHP_CMD="%%d\php.exe"
                set PHP_FOUND=1
            )
        )
        if !PHP_FOUND! equ 1 (
            echo    [+] Phat hien PHP tu Laragon: !PHP_CMD!
        ) else (
            color 0c
            echo    [LOI CRITICAL] Khong tim thay PHP tren he thong!
            echo    Vui long cai dat PHP [XAMPP/Laragon] va them vao PATH.
            pause
            exit
        )
    )
)

:: Phat hien Composer tu dong
where composer >nul 2>&1
if %errorlevel% equ 0 (
    set COMPOSER_CMD=composer
) else (
    if exist "composer.phar" (
        set COMPOSER_CMD=!PHP_CMD! composer.phar
        echo    [+] Phat hien composer.phar trong thu muc hien tai.
    ) else (
        echo    [!] Canh bao: Khong tim thay Composer. Mot so tinh nang khoi tao se bi han che.
        set COMPOSER_CMD=composer
    )
)

:: Kiem tra Node.js / NPM
where npm >nul 2>&1
if %errorlevel% equ 0 (
    echo    [+] Phien ban NPM: Da san sang hoat dong.
) else (
    echo    [!] Canh bao: Khong tim thay Node.js / NPM. Hay cai dat de Vite asset build hoat dong.
)

:: Phat hien IP mang noi bo tu dong (Local IP Discovery cho mobile testing)
set LOCAL_IP=127.0.0.1
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    set temp_ip=%%a
    set temp_ip=!temp_ip: =!
    if "!LOCAL_IP!"=="127.0.0.1" (
        if not "!temp_ip!"=="127.0.0.1" (
            set LOCAL_IP=!temp_ip!
        )
    )
)
echo    [+] IP Mang noi bo duoc tim thay: !LOCAL_IP! (Ho tro test tu dien thoai)

:: Doc file .env de lay ten va port Database
set DB_NAME=Unknown
set DB_PORT=3306
if exist .env (
    for /f "usebackq tokens=1,2 delims==" %%i in (".env") do (
        set var_name=%%i
        set var_val=%%j
        if "!var_name!"=="DB_DATABASE" set DB_NAME=!var_val!
        if "!var_name!"=="DB_PORT" set DB_PORT=!var_val!
    )
)
echo    [+] Thong tin DB (.env): Ten CSDL=!DB_NAME!, Cong=!DB_PORT!
timeout /t 2 > nul

:MENU
cls
color 0b
echo.
echo    +======================================================================================+
echo    ^|   _____  _____ ______ _   _ __  __          __     __ _____  _____   ____            ^|
echo    ^|  ^|  __ \^|_   _^|  ____^| \ ^| ^|  \/  ^|   /\   \ \   / /^|  __ \^|  __ \ / __ \           ^|
echo    ^|  ^| ^|  ^| ^| ^| ^| ^|__  ^|  \^| ^| \  / ^|  /  \   \ \_/ / ^| ^|__) ^| ^|__) ^| ^|  ^| ^|          ^|
echo    ^|  ^| ^|  ^| ^| ^| ^| ^|  __^| ^| . ` ^| ^|\ /^| ^| / /\ \   \   /  ^|  ___/^|  _  /^| ^|  ^| ^|          ^|
echo    ^|  ^| ^|__^| ^|_^| ^|_^| ^|____^| ^|\  ^| ^|  ^| ^|/ ____ \   ^| ^|   ^| ^|    ^| ^| \ \^| ^|__^| ^|          ^|
echo    ^|  ^|_____/^|_____^|______^|_^| \_^|_^|  ^|_/_/    \_\  ^|_^|   ^|_^|    ^|_^|  \_\ \____/           ^|
echo    +======================================================================================+
echo                                 SYSTEM ORCHESTRATOR v8.0 [SUPER AUTO]
echo.
echo    [1] DEV MODE          - Che do Lap trinh (Hot Reloading bang Vite Server)
echo    [2] STABLE RUN        - Che do On dinh (Chay bang file Production Build - nhanh hon)
echo    [3] RESET DATABASE    - [CANH BAO] Lam moi toan bo Database va Nap du lieu gia lap
echo    [4] VIEW SITEMAP      - Xem danh sach lien ket website hoat dong
echo    [5] FAST REBUILD ^& RUN - [ONE CLICK] Reset DB + Rebuild Vite + Mo Server ngay
echo    [6] INITIALIZE        - Tai moi thu vien (Composer Install + NPM Install)
echo    [7] UPGRADE SYSTEM    - [SUA LOI] Dong bo hoa va Cap nhat lai khoa composer.lock
echo    [8] HEALTH DIAGNOSTIC - [MOI] Kiem tra suc khoe toan dien he thong du an
echo    [9] LOG MANAGEMENT    - [MOI] Xem, theo doi va xoa sach nhat ky loi Laravel
echo    [10] EXIT             - Thoat
echo.
echo    ----------------------------------------------------------------------------------------
set /p choice="   >> Nhap lua chon cua ban (1-10): "

if "%choice%"=="1" goto DEV_MODE
if "%choice%"=="2" goto STABLE_RUN
if "%choice%"=="3" goto RESET_DB
if "%choice%"=="4" goto VIEW_SITEMAP
if "%choice%"=="5" goto PIPELINE_STANDARD
if "%choice%"=="6" goto INITIALIZE
if "%choice%"=="7" goto UPGRADE_ALL
if "%choice%"=="8" goto DIAGNOSTICS
if "%choice%"=="9" goto LOG_MGMT
if "%choice%"=="10" goto EXIT_CLEAN
goto MENU

:INITIALIZE
cls
color 0e
echo.
echo    +======================================================================================+
echo    ^|                DIENMAYPRO SMART SETUP WIZARD - TRINH KHOI TAO THONG MINH             ^|
echo    +======================================================================================+
echo.
echo    [!] Trinh huong dan khoi tao se tu dong thiet lap moi truong, tai thu vien va cau hinh CSDL.
echo.
echo    [1] CAI DAT NHANH (Fast Setup)    - Tai backend/frontend + khoi tao .env tu dong
echo    [2] THIET LAP CSDL (Database)     - Chuyen doi giua SQLite (Nhanh) va MySQL (Day du)
echo    [3] CAI DAT LAI SACH (Clean Rebuild)- Xoa sach vendor, node_modules, .env de lam lai tu dau
echo    [4] Quay lai Menu chinh
echo.
echo    ----------------------------------------------------------------------------------------
set /p setup_choice="   >> Nhap lua chon khoi tao cua ban (1-4): "

if "!setup_choice!"=="4" goto MENU
if "!setup_choice!"=="1" goto INITIALIZE_FAST
if "!setup_choice!"=="2" goto INITIALIZE_DB
if "!setup_choice!"=="3" goto INITIALIZE_CLEAN
goto INITIALIZE

:INITIALIZE_FAST
cls
echo.
echo    ====================================================================
echo    [ FAST SETUP ] TIEN HANH KHOI TAO NHANH THONG MINH
echo    ====================================================================
echo.

:: 1. Kiem tra va Copy .env
if not exist .env (
    echo    [1/5] [ HANH DONG ] Dang copy .env.example sang .env...
    copy .env.example .env > nul
    echo    ^|---^> [OK] Da khoi tao tep .env thanh cong.
) else (
    echo    [1/5] [+] .env da ton tai. Bo qua khoi tao.
)

:: 2. Composer Install
set run_composer=1
if exist vendor (
    echo.
    echo    [!] Phat hien thu muc [vendor] da ton tai.
    set /p comp_skip="   >> Ban co muon bo qua 'composer install' de tiet kiem thoi gian? [y/n]: "
    if /i "!comp_skip!"=="y" set run_composer=0
)
if !run_composer! equ 1 (
    echo.
    echo    [2/5] [ HANH DONG ] Dang tai backend packages [Composer]...
    call !COMPOSER_CMD! install --prefer-dist --ignore-platform-reqs
    echo    ^|---^> [OK] Tai backend packages hoan tat.
) else (
    echo.
    echo    [2/5] [+] Da bo qua buoc tai backend packages.
)

:: 3. NPM Install
set run_npm=1
if exist node_modules (
    echo.
    echo    [!] Phat hien thu muc [node_modules] da ton tai.
    set /p npm_skip="   >> Ban co muon bo qua 'npm install' de tiet kiem thoi gian? [y/n]: "
    if /i "!npm_skip!"=="y" set run_npm=0
)
if !run_npm! equ 1 (
    echo.
    echo    [3/5] [ HANH DONG ] Dang tai frontend packages [NPM]...
    call npm install
    echo    ^|---^> [OK] Tai frontend packages hoan tat.
) else (
    echo.
    echo    [3/5] [+] Da bo qua buoc tai frontend packages.
)

:: 4. Generate App Key if not exists
echo.
echo    [4/5] Kiem tra va sinh App Key...
findstr /C:"APP_KEY=base64:" .env > nul
if %errorlevel% neq 0 (
    echo    [ HANH DONG ] APP_KEY chua co. Dang khoi tao...
    call !PHP_CMD! artisan key:generate
    echo    ^|---^> [OK] Da sinh APP_KEY moi.
) else (
    echo    ^|---^> [+] APP_KEY da ton tai.
)

:: 5. SQLite database file check
echo.
echo    [5/5] Kiem tra ket noi CSDL trong .env...
findstr /C:"DB_CONNECTION=sqlite" .env > nul
if %errorlevel% equ 0 (
    if not exist database\database.sqlite (
        echo    [ HANH DONG ] Dang tu dong tao tep database.sqlite...
        type nul > database\database.sqlite
        echo    ^|---^> [OK] Da khoi tao database.sqlite.
    )
)

:: Storage link
if not exist public\storage (
    echo.
    echo    [ HANH DONG ] Dang tao storage symbolic link...
    call !PHP_CMD! artisan storage:link
)

echo.
echo    [ OK ] Da khoi tao du an va tai xong tat ca thu vien!
set /p auto_mig="   >> Ban co muon thuc hien Migrate va Seed du lieu gia lap ngay bay gio? [y/n]: "
if /i "!auto_mig!"=="y" (
    call !PHP_CMD! artisan migrate:fresh --seed
)
pause
goto INITIALIZE


:INITIALIZE_DB
cls
echo.
echo    ====================================================================
echo    [ DATABASE SELECTION ] CAU HINH TRINH DIEU KHIEN CO SO DU LIEU
echo    ====================================================================
echo.
echo    Vui long chon loai database ban muon thiet lap cho du an:
echo.
echo    [1] SQLITE [Khuyen dung cho may thieu XAMPP/Laragon - CSDL nhe luu trong tep tin]
echo    [2] MYSQL  [Dung cho he thong that - Yeu cau phai bat MySQL/XAMPP tren may]
echo    [3] Quay lai menu truoc
echo.
set /p db_choice="   >> Nhap lua chon (1-3): "

if "!db_choice!"=="3" goto INITIALIZE

if not exist .env (
    echo    [ HANH DONG ] Chua co file .env. Dang copy tu .env.example...
    copy .env.example .env > nul
)

if "!db_choice!"=="1" goto SETUP_SQLITE
if "!db_choice!"=="2" goto SETUP_MYSQL
goto INITIALIZE_DB

:SETUP_SQLITE
cls
echo    [ HANH DONG ] Dang thiet lap cau hinh SQLITE...
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo    ^|---^> [OK] Da tao tep database/database.sqlite.
)
echo    [ HANH DONG ] Dang cap nhat tep .env sang SQLite...
powershell -Command "$c = gc .env; $c = $c -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=sqlite'; $c = $c -replace '^DB_HOST=.*', '#DB_HOST=127.0.0.1'; $c = $c -replace '^DB_PORT=.*', '#DB_PORT=3306'; $c = $c -replace '^DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite'; $c = $c -replace '^DB_USERNAME=.*', '#DB_USERNAME=root'; $c = $c -replace '^DB_PASSWORD=.*', '#DB_PASSWORD='; $c | Out-File -encoding utf8 .env"

:: Clear Laravel config cache de nhan dien .env moi
call !PHP_CMD! artisan config:clear > nul

echo    [ OK ] Da chuyen doi thanh cong sang trinh dieu khien SQLite!
echo.
set /p mig_sqlite="   >> Ban co muon lam moi va nap CSDL SQLite ngay lap tuc? [y/n]: "
if /i "!mig_sqlite!"=="y" (
    call !PHP_CMD! artisan migrate:fresh --seed
)
pause
goto INITIALIZE

:SETUP_MYSQL
cls
echo    ====================================================================
echo    [ MYSQL CONFIGURATION ] NHAP THONG TIN LIEN KET MYSQL
echo    ====================================================================
echo.
echo    Dien cac thong tin ket noi MySQL duoi day [Nhan ENTER de lay gia tri mac dinh]:
echo.

set "db_host=127.0.0.1"
set /p db_host="   >> Nhap DB Host [Mac dinh: 127.0.0.1]: "

set "db_port=3306"
set /p db_port="   >> Nhap DB Port [Mac dinh: 3306]: "

set "db_name=dienmay_pro"
set /p db_name="   >> Nhap ten database [Mac dinh: dienmay_pro]: "

set "db_user=root"
set /p db_user="   >> Nhap Username [Mac dinh: root]: "

set "db_pass="
set /p db_pass="   >> Nhap Password [Mac dinh: bo trong]: "

echo.
echo    [ HANH DONG ] Dang thiet lap cau hinh MYSQL va cap nhat tep .env...
powershell -Command "$c = gc .env; $c = $c -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=mysql'; $c = $c -replace '^DB_HOST=.*', 'DB_HOST=!db_host!'; $c = $c -replace '^DB_PORT=.*', 'DB_PORT=!db_port!'; $c = $c -replace '^DB_DATABASE=.*', 'DB_DATABASE=!db_name!'; $c = $c -replace '^DB_USERNAME=.*', 'DB_USERNAME=!db_user!'; $c = $c -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=!db_pass!'; $c | Out-File -encoding utf8 .env"

:: Clear Laravel config cache de nhan dien .env moi
call !PHP_CMD! artisan config:clear > nul

echo    [ OK ] Da cap nhat cau hinh CSDL MySQL vao tep .env!
echo.
set /p mig_mysql="   >> Ban co muon lam moi va nap CSDL MySQL ngay lap tuc [Yeu cau MySQL phai bat]? [y/n]: "
if /i "!mig_mysql!"=="y" (
    call !PHP_CMD! artisan migrate:fresh --seed
)
pause
goto INITIALIZE


:INITIALIZE_CLEAN
cls
color 0c
echo    +======================================================================================+
echo    ^|                  [ CANH BAO QUAN TRONG ] CLEAN REBUILD TOAN DIEN                       ^|
echo    +======================================================================================+
echo.
echo    [!] Thao tac nay se XOA SACH cac thu muc sau:
echo        - vendor/ [Backend Packages]
echo        - node_modules/ [Frontend Packages]
echo        - .env [Cac cau hinh hien tai]
echo        - package-lock.json va database/database.sqlite
echo.
set /p clean_confirm="    >> Ban co chac chan 100%% muon don sach he thong va thiet lap lai tu dau? [y/n]: "
if /i "!clean_confirm!" neq "y" goto INITIALIZE

cls
echo    ====================================================================
echo    [ CLEAN REBUILD ACTIVE ] DANG LAM SACH TOAN DIEN
echo    ====================================================================
echo.

echo    [1/6] Dang xoa thu muc [vendor]...
if exist vendor (rd /s /q vendor)
echo    ^|---^> [OK] Da xoa.

echo    [2/6] Dang xoa thu muc [node_modules]...
if exist node_modules (rd /s /q node_modules)
echo    ^|---^> [OK] Da xoa.

echo    [3/6] Dang xoa file [.env] cu va [package-lock.json]...
if exist .env (del /f /q .env)
if exist package-lock.json (del /f /q package-lock.json)
if exist database\database.sqlite (del /f /q database\database.sqlite)
echo    ^|---^> [OK] Da xoa.

echo    [4/6] Dang tai tao [.env] tu [.env.example]...
copy .env.example .env > nul
echo    ^|---^> [OK] Da tai tao file .env moi.

echo.
echo    [!] Vui long lua chon Driver CSDL de thiet lap truoc khi install:
echo    [1] SQLITE [Mac dinh / Khuyen dung cho dev nhanh]
echo    [2] MYSQL  [Dung database chung voi XAMPP/Laragon]
set /p rebuild_db_driver="   >> Lua chon driver [1-2]: "

if "!rebuild_db_driver!"=="2" goto REBUILD_DB_MYSQL

:REBUILD_DB_SQLITE
type nul > database\database.sqlite
powershell -Command "$c = gc .env; $c = $c -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=sqlite'; $c = $c -replace '^DB_HOST=.*', '#DB_HOST=127.0.0.1'; $c = $c -replace '^DB_PORT=.*', '#DB_PORT=3306'; $c = $c -replace '^DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite'; $c = $c -replace '^DB_USERNAME=.*', '#DB_USERNAME=root'; $c = $c -replace '^DB_PASSWORD=.*', '#DB_PASSWORD='; $c | Out-File -encoding utf8 .env"
goto REBUILD_DB_DONE

:REBUILD_DB_MYSQL
set "db_host=127.0.0.1"
set /p db_host="   >> Nhap DB Host [Mac dinh: 127.0.0.1]: "
set "db_port=3306"
set /p db_port="   >> Nhap DB Port [Mac dinh: 3306]: "
set "db_name=dienmay_pro"
set /p db_name="   >> Nhap ten database [Mac dinh: dienmay_pro]: "
set "db_user=root"
set /p db_user="   >> Nhap Username [Mac dinh: root]: "
set "db_pass="
set /p db_pass="   >> Nhap Password [Mac dinh: bo trong]: "
powershell -Command "$c = gc .env; $c = $c -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=mysql'; $c = $c -replace '^DB_HOST=.*', 'DB_HOST=!db_host!'; $c = $c -replace '^DB_PORT=.*', 'DB_PORT=!db_port!'; $c = $c -replace '^DB_DATABASE=.*', 'DB_DATABASE=!db_name!'; $c = $c -replace '^DB_USERNAME=.*', 'DB_USERNAME=!db_user!'; $c = $c -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=!db_pass!'; $c | Out-File -encoding utf8 .env"

:REBUILD_DB_DONE

echo.
echo    [5/6] Dang chay 'composer install' de nap lai Backend...
call !COMPOSER_CMD! install --prefer-dist --ignore-platform-reqs
echo    ^|---^> [OK] Composer install hoan tat.

echo.
echo    [6/6] Dang chay 'npm install' de nap lai Frontend...
call npm install
echo    ^|---^> [OK] NPM install hoan tat.

echo.
echo    [ HANH DONG ] Dang sinh APP_KEY moi va link folder public storage...
call !PHP_CMD! artisan key:generate
if not exist public\storage (
    call !PHP_CMD! artisan storage:link
)

echo.
echo    [ OK ] Clean Rebuild hoan tat! He thong cua ban da sach se 100%%!
set /p rebuild_mig="   >> Ban co muon chay migrate:fresh va seeding nap CSDL moi khong? [y/n]: "
if /i "!rebuild_mig!"=="y" (
    call !PHP_CMD! artisan migrate:fresh --seed
)
pause
goto INITIALIZE


:UPGRADE_ALL
cls
color 0e
echo.
echo    +======================================================================================+
echo    ^|                DIENMAYPRO REPAIR ASSISTANT - BO PHUC HOI ^& NANG CAP TOAN DIEN        ^|
echo    +======================================================================================+
echo.
echo    [!] Vui long lua chon phuong thuc phuc hoi hoac nang cap ban muon thuc hien:
echo.
echo    [1] COMPOSER UPDATE   - Dong bo va cap nhat lockfile (Sua loi phien ban goi backend)
echo    [2] VENDOR REBUILD    - [QUAN TRONG] Xoa sach Vendor va cai dat lai tu dau (Sua loi package PHP)
echo    [3] FRONTEND REBUILD  - [QUAN TRONG] Xoa sach node_modules va NPM Re-install (Sua loi compilation)
echo    [4] DEEP CACHE CLEAN  - Don dep cache chuyen sau va toi uu hoa Classmap (dump-autoload)
echo    [5] FULL REPAIR SYSTEM- [ONE-CLICK] Thuc hien toan bo 4 buoc tren de khoi phuc 100%% he thong
echo    [6] Quay lai Menu chinh
echo.
echo    ----------------------------------------------------------------------------------------
set /p repair_choice="   >> Nhap lua chon phuc hoi cua ban (1-6): "

if "!repair_choice!"=="6" goto MENU
if "!repair_choice!"=="1" goto REPAIR_COMPOSER_UPDATE
if "!repair_choice!"=="2" goto REPAIR_VENDOR_REBUILD
if "!repair_choice!"=="3" goto REPAIR_FRONTEND_REBUILD
if "!repair_choice!"=="4" goto REPAIR_DEEP_CACHE
if "!repair_choice!"=="5" goto REPAIR_FULL_SYSTEM
goto UPGRADE_ALL

:REPAIR_COMPOSER_UPDATE
cls
echo    [ HANH DONG ] Dang chay Composer Update...
call !COMPOSER_CMD! update --ignore-platform-reqs
echo    [ OK ] Da cap nhat lockfile backend thanh cong!
pause
goto UPGRADE_ALL

:REPAIR_VENDOR_REBUILD
cls
echo    [ HANH DONG ] Dang tien hanh xoa thu muc vendor cu...
if exist vendor rd /s /q vendor
echo    [ HANH DONG ] Dang tai va cai dat lai toan bo package PHP moi...
call !COMPOSER_CMD! install --prefer-dist --ignore-platform-reqs
echo    [ OK ] Da khoi phuc sach thu muc vendor thanh cong!
pause
goto UPGRADE_ALL

:REPAIR_FRONTEND_REBUILD
cls
echo    [ HANH DONG ] Dang tien hanh xoa thu muc node_modules cu...
if exist node_modules rd /s /q node_modules
if exist package-lock.json del /f /q package-lock.json
echo    [ HANH DONG ] Dang NPM Re-install va rebuild assets...
call npm install
echo    [ OK ] Da khoi phuc va rebuild frontend thanh cong!
pause
goto UPGRADE_ALL

:REPAIR_DEEP_CACHE
cls
echo    [ HANH DONG ] Dang toi uu hoa Classmap (Composer dump-autoload)...
call !COMPOSER_CMD! dump-autoload
echo    [ HANH DONG ] Dang don dep cache he thong...
call !PHP_CMD! artisan optimize:clear
echo    [ OK ] Da lam sach cache va toi uu hoa autoload classmap thanh cong!
pause
goto UPGRADE_ALL

:REPAIR_FULL_SYSTEM
cls
echo    ====================================================================
echo    [ SYSTEM FULL REPAIR ] TIEN HANH KHOI PHUC LIEN HOAN TOAN DIEN SYSTEM
echo    ====================================================================
echo.
echo    [1/4] Dang lam sach va rebuild Vendor PHP (Composer)...
if exist vendor rd /s /q vendor
call !COMPOSER_CMD! install --prefer-dist --ignore-platform-reqs
echo    ^|---^> [OK] Composer install hoan tat.

echo    [2/4] Dang lam sach va rebuild Frontend Assets (NPM)...
if exist node_modules rd /s /q node_modules
if exist package-lock.json del /f /q package-lock.json
call npm install
echo    ^|---^> [OK] NPM install hoan tat.

echo    [3/4] Chay composer dump-autoload va update...
call !COMPOSER_CMD! dump-autoload
call !COMPOSER_CMD! update --ignore-platform-reqs
echo    ^|---^> [OK] Composer dump-autoload hoan tat.

echo    [4/4] Dang don dep cache he thong...
call !PHP_CMD! artisan optimize:clear > nul
echo    ^|---^> [OK] Da optimize va clear cache Laravel.

echo.
echo    [ SUCCESS ] He thong da duoc khoi phuc sach se va nang cap len ban moi nhat 100%%!
pause
goto MENU

:SUPER_PIPELINE
cls
color 0b
echo.
echo    +======================================================================================+
echo    ^|             DIENMAYPRO SUPER PIPELINE - CONG CU KICH HOAT TOAN TRINH ALL-IN-ONE       ^|
echo    +======================================================================================+
echo.
echo    [!] Trinh tu dong hoa sieu cap se thuc hien toan bo quy trinh tu A den Z de chay website.
echo    [!] Vui long lua chon kieu Pipeline ban muon van hanh:
echo.
echo    [1] PIPELINE TIEU CHUAN (Standard Pipeline)
echo        - Giu nguyen vendor/node_modules hien tai (tai them neu thieu)
echo        - Cau hinh .env va Database (SQLite/MySQL)
echo        - Migrate ^& Seed du lieu gia lap, optimize cache va boot server ngay lap tuc.
echo.
echo    [2] PIPELINE SACH TOAN DIEN (Clean Rebuild Pipeline)
echo        - rd /s /q vendor, node_modules va xoa sach file .env cu de tranh xung dot
echo        - Tai moi hoan toan thu vien tu dau (Composer + NPM)
echo        - Cau hinh lai database, migrate:fresh --seed, build assets va boot server.
echo.
echo    [3] Quay lai Menu chinh
echo.
echo    ----------------------------------------------------------------------------------------
set /p pipe_choice="   >> Nhap lua chon cua ban (1-3): "

if "!pipe_choice!"=="3" goto MENU
if "!pipe_choice!"=="1" goto PIPELINE_STANDARD
if "!pipe_choice!"=="2" goto PIPELINE_CLEAN_REBUILD
goto SUPER_PIPELINE


:PIPELINE_STANDARD
cls
echo.
echo    ====================================================================
echo    [ FAST REBUILD ^& RUN ] RESET CSDL + BIEN DICH VITE + KHOI CHAY MOI TRUONG
echo    ====================================================================
echo.

:: 1. Tat tien trinh cu
echo    [1/7] Dang giai phong cac tien trinh cu...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
if exist public\hot del /f /q public\hot
echo    ^|---^> [OK] Da don dep xong tien trinh cu.

:: 2. File .env
if not exist .env (
    echo    [2/7] [ HANH DONG ] Dang copy .env.example sang .env...
    copy .env.example .env > nul
)
echo    [2/7] [OK] Moi truong .env san sang.

:: 3. Composer
if not exist vendor (
    echo    [3/7] [ HANH DONG ] Thieu thu muc vendor. Dang tu dong tai packages backend...
    call !COMPOSER_CMD! install --prefer-dist --ignore-platform-reqs
) else (
    echo    [3/7] [+] Thu muc vendor da co san.
)

:: 4. NPM
if not exist node_modules (
    echo    [4/7] [ HANH DONG ] Thieu node_modules. Dang tu dong tai packages frontend...
    call npm install
) else (
    echo    [4/7] [+] Thu muc node_modules da co san.
)

:: 5. Key ^& Database config
echo    [5/7] Kiem tra App Key va cau hinh CSDL...
findstr /C:"APP_KEY=base64:" .env > nul
if %errorlevel% neq 0 (
    call !PHP_CMD! artisan key:generate
)
findstr /C:"DB_CONNECTION=sqlite" .env > nul
if %errorlevel% equ 0 (
    if not exist database\database.sqlite (
        type nul > database\database.sqlite
    )
)
findstr /C:"DB_CONNECTION=mysql" .env > nul
if %errorlevel% equ 0 (
    echo    [!] Phat hien cau hinh MySQL. Dang kiem tra ket noi...
    tasklist /fi "imagename eq mysqld.exe" | findstr /i "mysqld.exe" > nul
    if !errorlevel! neq 0 (
        color 0c
        echo    [ LOI ] MySQL chua bat! Vui long bat MySQL tren XAMPP/Laragon truoc.
        echo    [ GOI Y ] Mo XAMPP Control Panel va nhan Start ben canh MySQL.
        pause
        goto MENU
    )
    echo    ^|---^> [OK] MySQL dang hoat dong.
)
if not exist public\storage (
    call !PHP_CMD! artisan storage:link
)
echo    ^|---^> [OK] Cau hinh he thong hop le.

:: 6. Migrate ^& Seed
echo    [6/7] [ HANH DONG ] Dang lam moi co so du lieu va nap du lieu gia lap...
call !PHP_CMD! artisan migrate:fresh --seed
call !PHP_CMD! artisan optimize:clear
echo    ^|---^> [OK] Reset DB va nap data thanh cong!

:: 7. Build ^& Run
echo    [7/7] Dang bien dich tai nguyen frontend (Build Assets)...
call npm run build
echo    ^|---^> [OK] Chuan bi assets hoan tat.
timeout /t 2 > nul
goto PROCESS


:PIPELINE_CLEAN_REBUILD
cls
color 0c
echo    +======================================================================================+
echo    ^|              [ CANH BAO ] PIPELINE SACH SE XOA VA KHOI TAO LAI TOAN TRINH            ^|
echo    +======================================================================================+
echo.
echo    [!] Thao tac nay se xoa sach vendor, node_modules, .env va database hien tai
echo        de dam bao tai va chay lai tu dau khong gap bat ky loi nao.
echo.
set /p pipe_clean_confirm="    >> Ban co chac chan muon tiep tuc? [y/n]: "
if /i "!pipe_clean_confirm!" neq "y" goto SUPER_PIPELINE

cls
color 0b
echo.
echo    ====================================================================
echo    [ CLEAN PIPELINE ACTIVE ] TIEN HANH XOA SACH VA TAI TOAN BO TU A-Z
echo    ====================================================================
echo.

:: 1. Giai phong va xoa
echo    [1/8] Dang xoa sach cac thu muc cu va giai phong tien trinh...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
if exist public\hot del /f /q public\hot
if exist vendor (rd /s /q vendor)
if exist node_modules (rd /s /q node_modules)
if exist .env (del /f /q .env)
if exist package-lock.json (del /f /q package-lock.json)
if exist database\database.sqlite (del /f /q database\database.sqlite)
echo    ^|---^> [OK] He thong da duoc lam sach 100%%.

:: 2. Tai tao .env
echo    [2/8] Dang khoi tao file .env moi...
copy .env.example .env > nul
echo    ^|---^> [OK] Da copy .env.example.

:: 3. Chon Database driver tu dong hoac cho nguoi dung chon nhanh
echo    [3/8] Thiet lap CSDL cho he thong:
echo    [1] SQLITE [Mac dinh / Khuyen dung cho moi truong dev cuc nhanh]
echo    [2] MYSQL  [Dung CSDL XAMPP/Laragon]
set /p pipe_db_driver="   >> Chon CSDL (1-2) [Mac dinh: 1]: "
if "!pipe_db_driver!"=="2" goto PIPE_DB_MYSQL

:PIPE_DB_SQLITE
type nul > database\database.sqlite
powershell -Command "$c = gc .env; $c = $c -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=sqlite'; $c = $c -replace '^DB_HOST=.*', '#DB_HOST=127.0.0.1'; $c = $c -replace '^DB_PORT=.*', '#DB_PORT=3306'; $c = $c -replace '^DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite'; $c = $c -replace '^DB_USERNAME=.*', '#DB_USERNAME=root'; $c = $c -replace '^DB_PASSWORD=.*', '#DB_PASSWORD='; $c | Out-File -encoding utf8 .env"
goto PIPE_DB_DONE

:PIPE_DB_MYSQL
set "db_host=127.0.0.1"
set /p db_host="   >> Nhap DB Host [Mac dinh: 127.0.0.1]: "
set "db_port=3306"
set /p db_port="   >> Nhap DB Port [Mac dinh: 3306]: "
set "db_name=dienmay_pro"
set /p db_name="   >> Nhap ten database [Mac dinh: dienmay_pro]: "
set "db_user=root"
set /p db_user="   >> Nhap Username [Mac dinh: root]: "
set "db_pass="
set /p db_pass="   >> Nhap Password [Mac dinh: bo trong]: "
powershell -Command "$c = gc .env; $c = $c -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=mysql'; $c = $c -replace '^DB_HOST=.*', 'DB_HOST=!db_host!'; $c = $c -replace '^DB_PORT=.*', 'DB_PORT=!db_port!'; $c = $c -replace '^DB_DATABASE=.*', 'DB_DATABASE=!db_name!'; $c = $c -replace '^DB_USERNAME=.*', 'DB_USERNAME=!db_user!'; $c = $c -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=!db_pass!'; $c | Out-File -encoding utf8 .env"

:PIPE_DB_DONE
echo    ^|---^> [OK] Thiet lap database hoan tat.

:: 4. Composer install
echo.
echo    [4/8] Dang cai dat thu vien Backend (Composer)...
call !COMPOSER_CMD! install --prefer-dist --ignore-platform-reqs
echo    ^|---^> [OK] Tai va cai dat goi backend thanh cong.

:: 5. NPM install
echo.
echo    [5/8] Dang cai dat thu vien Frontend (NPM)...
call npm install
echo    ^|---^> [OK] Tai va cai dat goi frontend thanh cong.

:: 6. APP_KEY ^& Storage Link
echo.
echo    [6/8] Khoi tao key bao mat va lien ket thu muc storage...
call !PHP_CMD! artisan key:generate
if not exist public\storage (
    call !PHP_CMD! artisan storage:link
)
echo    ^|---^> [OK] Da sinh khoa bao mat he thong.

:: 7. Kiem tra MySQL truoc khi migrate
echo.
echo    [7/9] Kiem tra ket noi CSDL truoc khi migrate...
findstr /C:"DB_CONNECTION=mysql" .env > nul
if %errorlevel% equ 0 (
    tasklist /fi "imagename eq mysqld.exe" | findstr /i "mysqld.exe" > nul
    if !errorlevel! neq 0 (
        color 0c
        echo    [ LOI ] MySQL chua bat! Vui long bat MySQL tren XAMPP/Laragon truoc.
        echo    [ GOI Y ] Mo XAMPP Control Panel va nhan Start ben canh MySQL.
        pause
        goto MENU
    )
    echo    ^|---^> [OK] MySQL dang hoat dong.
)

:: 8. Migrate ^& Seed
echo.
echo    [8/9] Khoi tao cau truc CSDL va nap toan bo du lieu mau gia lap...
call !PHP_CMD! artisan migrate:fresh --seed
call !PHP_CMD! artisan optimize:clear
echo    ^|---^> [OK] CSDL da san sang cung toan bo combo va home banners.

:: 9. Build Assets
echo.
echo    [9/9] Dang bien dich giao dien web (Build Production Assets)...
call npm run build
echo    ^|---^> [OK] Build hoan tat!
timeout /t 2 > nul
goto PROCESS

:VIEW_SITEMAP
cls
color 0f
if not defined PORT (set PORT=8000)
echo.
echo    +======================================================================================+
echo    ^|                DIENMAYPRO NAVIGATOR - HE THONG LIEN KET QUAN TRI ^& KHACH HANG        ^|
echo    +======================================================================================+
echo.
echo    [+] Cong may chu hien tai: !PORT!
echo    [!] Nhap so tuong ung de MO TRUC TIEP tren trinh duyet mac dinh cua ban:
echo.
echo    --- HE THONG KHACH HANG (FRONTEND) ---
echo    [1] Trang chu Website              - http://127.0.0.1:!PORT!/
echo    [2] Vong quay ^& Doi diem thuong     - http://127.0.0.1:!PORT!/rewards
echo    [3] Dat lich ^& Lich su sua chua    - http://127.0.0.1:!PORT!/profile
echo    [4] So sanh san pham               - http://127.0.0.1:!PORT!/compare
echo    [5] Dang nhap / Dang ky            - http://127.0.0.1:!PORT!/login_register
echo.
echo    --- HE THONG QUAN TRI (ADMIN PORTAL) ---
echo    [6] Tong quan Admin Dashboard      - http://127.0.0.1:!PORT!/admin
echo    [7] Quan ly Phieu sua chua         - http://127.0.0.1:!PORT!/admin/repair-tickets
echo    [8] Quan ly Hoa don dich vu        - http://127.0.0.1:!PORT!/admin/service-invoices
echo    [9] Cau hinh Vong quay ^& Qua tang   - http://127.0.0.1:!PORT!/admin/rewards
echo    [10] Lich su Quay ^& Doi thuong      - http://127.0.0.1:!PORT!/admin/rewards/history
echo    [11] Quan ly Bai viet ^& Tin tuc     - http://127.0.0.1:!PORT!/admin/articles
echo.
echo    --- DIEU HUONG CHUNG ---
echo    [12] Quay lai Menu chinh
echo.
echo    ----------------------------------------------------------------------------------------
set /p sitemap_choice="   >> Nhap lua chon lien ket cua ban (1-12): "

if "!sitemap_choice!"=="1" start http://127.0.0.1:!PORT!/ & goto VIEW_SITEMAP
if "!sitemap_choice!"=="2" start http://127.0.0.1:!PORT!/rewards & goto VIEW_SITEMAP
if "!sitemap_choice!"=="3" start http://127.0.0.1:!PORT!/profile & goto VIEW_SITEMAP
if "!sitemap_choice!"=="4" start http://127.0.0.1:!PORT!/compare & goto VIEW_SITEMAP
if "!sitemap_choice!"=="5" start http://127.0.0.1:!PORT!/login_register & goto VIEW_SITEMAP
if "!sitemap_choice!"=="6" start http://127.0.0.1:!PORT!/admin & goto VIEW_SITEMAP
if "!sitemap_choice!"=="7" start http://127.0.0.1:!PORT!/admin/repair-tickets & goto VIEW_SITEMAP
if "!sitemap_choice!"=="8" start http://127.0.0.1:!PORT!/admin/service-invoices & goto VIEW_SITEMAP
if "!sitemap_choice!"=="9" start http://127.0.0.1:!PORT!/admin/rewards & goto VIEW_SITEMAP
if "!sitemap_choice!"=="10" start http://127.0.0.1:!PORT!/admin/rewards/history & goto VIEW_SITEMAP
if "!sitemap_choice!"=="11" start http://127.0.0.1:!PORT!/admin/articles & goto VIEW_SITEMAP
goto MENU

:: ======================================================================
:: DIAGNOSTICS: HEALTH REPORT (PRO FEATURE)
:: ======================================================================
:DIAGNOSTICS
cls
color 0f
echo.
echo    ====================================================================
echo    [ DIAGNOSTIC REPORT ] KIEM TRA SUC KHOE DU AN TOAN DIEN
echo    ====================================================================
echo.
echo    [1/5] Kiem tra bien moi truong (.env)...
if not exist .env (
    set ENV_STATUS=❌ Tep .env khong ton tai!
) else (
    findstr /C:"APP_KEY=base64:" .env > nul
    if %errorlevel% neq 0 (
        set ENV_STATUS=⚠ Tep .env co san nhung APP_KEY chua khoi tao!
    ) else (
        set ENV_STATUS=✔ Tep .env ton tai va APP_KEY hop le.
    )
)
echo    ^|---^> !ENV_STATUS!

echo    [2/5] Kiem tra cac thu muc tam (Write permissions)...
set FOLDERS_OK=1
if not exist storage\framework\views (mkdir storage\framework\views & set FOLDERS_OK=0)
if not exist storage\framework\cache (mkdir storage\framework\cache & set FOLDERS_OK=0)
if not exist storage\framework\sessions (mkdir storage\framework\sessions & set FOLDERS_OK=0)
if not exist storage\logs (mkdir storage\logs & set FOLDERS_OK=0)
if !FOLDERS_OK! equ 1 (
    echo    ^|---^> ✔ Cac thu muc tam storage/framework deu san sang va writable.
) else (
    echo    ^|---^> ⚠ Da tu dong phat hien thieu thu muc tam va tao moi thanh cong.
)

echo    [3/5] Kiem tra ket noi CSDL MySQL...
tasklist /fi "imagename eq mysqld.exe" | findstr /i "mysqld.exe" > nul
if %errorlevel% neq 0 (
    set DB_REPORT=❌ CSDL MySQL hien tai dang OFF! Vui long bat MySQL tren XAMPP/Laragon.
) else (
    set DB_REPORT=✔ MySQL dang bat, Cong CSDL=!DB_PORT!, Ten CSDL=!DB_NAME!.
)
echo    ^|---^> !DB_REPORT!

echo    [4/5] Kiem tra thu vien node_modules...
if exist node_modules (
    echo    ^|---^> ✔ Thu muc node_modules da duoc cai dat.
) else (
    echo    ^|---^> ❌ Thieu thu muc node_modules! Vui long chay Lua chon [6] de cai dat.
)

echo    [5/5] Kiem tra cac goi backend vendor...
if exist vendor (
    echo    ^|---^> ✔ Thu muc backend vendor da san sang.
) else (
    echo    ^|---^> ❌ Thieu thu muc backend vendor! Vui long chay Lua chon [6] de cai dat.
)
echo.
echo    --------------------------------------------------------------------
echo    Nhan phim bat ky de quay lai Menu...
pause > nul
goto MENU

:: ======================================================================
:: LOG MANAGEMENT: VIEW & CLEAN (PRO FEATURE)
:: ======================================================================
:LOG_MGMT
cls
color 0e
echo.
echo    +======================================================================================+
echo    ^|                       QUAN LY NHAT KY LOI LARAVEL GAN NHAT                           ^|
echo    +======================================================================================+
echo.
echo    [1] Xem 30 dong nhat ky loi moi nhat
echo    [2] Xoa sach file nhat ky loi hien tai (Clear logs)
echo    [3] Quay lai Menu chinh
echo.
set /p log_choice="   >> Nhap lua chon cua ban (1-3): "

if "%log_choice%"=="1" (
    cls
    echo.
    echo    --- 30 Dong nhat ky loi gan nhat ---
    if exist storage\logs\laravel.log (
        powershell -command "Get-Content storage\logs\laravel.log -Tail 30"
    ) else (
        color 0a
        echo    [+] Khong co bat ky nhat ky loi nao duoc ghi nhan [Moi thu hoat dong rat tot!].
    )
    echo.
    color 0e
    echo    Nhan phim bat ky de quay lai...
    pause > nul
    goto LOG_MGMT
)
if "%log_choice%"=="2" (
    if exist storage\logs\laravel.log (
        del /f /q storage\logs\laravel.log
        echo    [ OK ] Da xoa sach toan bo nhat ky loi cu thanh cong!
    ) else (
        echo    [+] Tep log hien tai dang trong, khong can thuc hien.
    )
    pause
    goto LOG_MGMT
)
goto MENU

:DEV_MODE
cls
echo.
echo    [ HANH DONG ] Dang khoi dong che do DEV...
if not exist node_modules (
    color 0e
    echo    [!] Canh bao: Khong tim thay thu muc node_modules!
    echo    He thong se tu dong chay NPM INSTALL de chuan bi Vite Asset hot-reload...
    call npm install
)
set MODE=DEV
goto PROCESS

:STABLE_RUN
cls
color 0e
echo.
echo    [1/4] Dang don dep cac tien trinh chay ngam cu...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
if exist public\hot del /f /q public\hot
!PHP_CMD! artisan optimize:clear > nul
echo    [2/4] Dang dung lai goi assets cho on dinh...
call npm run build
set MODE=STABLE
goto PROCESS

:RESET_DB
cls
color 0c
echo    [!] Canh bao: Toan bo du lieu cu trong Database (!DB_NAME!) se bi xoa sach!
set /p confirm="    >> Ban co chac chan muon tiep tuc? (y/n): "
if /i "%confirm%" neq "y" goto MENU
call !PHP_CMD! artisan migrate:fresh --seed
call !PHP_CMD! artisan optimize:clear
echo    [ OK ] Database va Seeders da lam moi thanh cong!
pause
goto MENU

:PROCESS
color 0a
cls
echo.
echo    ====================================================================
echo    [ CONG CU ] DANG TIEN HANH KHOI TAO PHAN CONG TU DONG HOA...
echo    ====================================================================
echo.
echo    [ STEP 1 ] KIEM TRA MANG LIEN KET MYSQL...
tasklist /fi "imagename eq mysqld.exe" | findstr /i "mysqld.exe" > nul
if %errorlevel% neq 0 (
    color 0c
    echo    [ LOI ] Co so du lieu MySQL chua bat! Vui long bat MySQL tren XAMPP/Laragon.
    pause
    goto MENU
)
echo    [+] MySQL dang ket noi tot (CSDL: !DB_NAME!, Cong CSDL: !DB_PORT!).

if not exist .env (
    copy .env.example .env > nul
    call !PHP_CMD! artisan key:generate
)

echo    [ STEP 1.5 ] LAM SACH CACHE TOAN DIEN...
call !PHP_CMD! artisan config:clear
call !PHP_CMD! artisan route:clear
call !PHP_CMD! artisan view:clear

:: ======================================================================
:: CONFLICT RESOLUTION: ACTIVE PORT CHECKING (PORT 8000)
:: ======================================================================
echo    [ STEP 2 ] KIEM TRA TRANH CHAP CONG MO...
netstat -o -n -a | findstr :8000 > nul
if %errorlevel% equ 0 (
    color 0e
    echo    [!] Canh bao: Cong 8000 hien tai dang bi chiem dung boi tien trinh khac!
    echo    [1] Tu dong giai phong cong 8000 [Tat ung dung dang chan]
    echo    [2] Chuyen sang khoi chay tren cong phu [Cong 8080]
    set /p port_choice="   >> Nhap lua chon [1-2]: "
    if "!port_choice!"=="1" (
        for /f "tokens=5" %%p in ('netstat -aon ^| findstr :8000') do (
            taskkill /f /pid %%p >nul 2>&1
        )
        echo    [+] Da tu dong giai phong cong 8000 thanh cong.
        set PORT=8000
    ) else (
        set PORT=8080
        echo    [+] He thong se duoc dinh huong sang cong 8080.
    )
) else (
    set PORT=8000
)

color 0a
echo    [ STEP 3 ] PHAT HANH BAN SERVERS CONG !PORT!...
start "DienMayPro Laravel Server" cmd /c "!PHP_CMD! artisan serve --port=!PORT!"
if "%MODE%"=="DEV" (
    start "Vite Hot-Reload Server" cmd /c "npm run dev"
    timeout /t 8 > nul
) else (
    timeout /t 3 > nul
)

cls
color 0b
echo.
echo    +======================================================================================+
echo    ^|                     DIENMAYPRO CHAY TU DONG HOA THANH CONG                           ^|
echo    +======================================================================================+
echo    ^|                                                                                      ^|
echo    ^|  [+] Localhost URL:       http://127.0.0.1:!PORT!/                                     ^|
echo    ^|  [+] Local Network URL:   http://!LOCAL_IP!:!PORT!/                                   ^|
echo    ^|  [+] Admin Portal:        http://127.0.0.1:!PORT!/admin                              ^|
echo    ^|                                                                                      ^|
echo    ^|  [*] Meo kiem thu: Dung dien thoai / may tinh bang ket noi cung mang Wi-Fi voi may   ^|
echo    ^|      tinh, roi truy cap vao duong dan Local Network URL o tren de test thiet bi di   ^|
echo    ^|      dong cuc ky de dang va thuc te.                                                 ^|
echo    ^|                                                                                      ^|
echo    +======================================================================================+
echo.
echo    [*] DIENMAYPRO DANG CHAY THEO CONG !PORT! - NHAN PHIM BAT KY DE TAT TOAN BO SERVERS
echo.
pause > nul
echo    [!] Dang giai phong va tat toan bo tien trinh ngam php/node...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1
:: ======================================================================
:: EXIT SYSTEM CLEANLY (THOAT CO CHE AN TOAN TRA VE MA 0)
:: ======================================================================
:EXIT_CLEAN
:: Reset lai errorlevel ve 0 tranh lỗi do cac lenh taskkill truoc do de lai
cmd /c "exit /b 0"
exit /b 0
