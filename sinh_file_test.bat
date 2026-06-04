@echo off
echo ======================================================================
echo           DANG TAO THU MUC VA CAC FILE KIEM THU (TEST RESOURCES)
echo ======================================================================
echo.

set "PHP_BIN=php"

:: 1. Kiem tra trong PATH
where php >nul 2>nul
if %errorlevel% equ 0 (
    echo [OK] Tim thay PHP trong bien moi truong PATH.
    goto run
)

:: 2. Kiem tra XAMPP o C
if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
    echo [OK] Tim thay PHP tai: C:\xampp\php\php.exe
    goto run
)

:: 3. Kiem tra XAMPP o D
if exist "D:\xampp\php\php.exe" (
    set "PHP_BIN=D:\xampp\php\php.exe"
    echo [OK] Tim thay PHP tai: D:\xampp\php\php.exe
    goto run
)

:: 4. Kiem tra Laragon o C
if exist "C:\laragon\bin\php" (
    for /d %%d in (C:\laragon\bin\php\php-*) do (
        if exist "%%d\php.exe" (
            set "PHP_BIN=%%d\php.exe"
            echo [OK] Tim thay PHP Laragon tai: %%d\php.exe
            goto run
        )
    )
)

:: 5. Kiem tra Laragon o D
if exist "D:\laragon\bin\php" (
    for /d %%d in (D:\laragon\bin\php\php-*) do (
        if exist "%%d\php.exe" (
            set "PHP_BIN=%%d\php.exe"
            echo [OK] Tim thay PHP Laragon tai: %%d\php.exe
            goto run
        )
    )
)

echo ======================================================================
echo [LOI] Khong tim thay PHP tren may cua ban!
echo.
echo Hay tu chay lenh trong terminal cua ban:
echo   cd ThuongMaiDienTu
echo   php generate_test_resources.php
echo ======================================================================
pause
exit

:run
echo.
echo Dang chay script tao file...
echo.

cd ThuongMaiDienTu
"%PHP_BIN%" generate_test_resources.php
cd ..

echo.
echo ======================================================================
echo   DA TAO THANH CONG CAC FILE KIEM THU TRONG THU MUC:
echo   g:\ThuongMaiDienTu\tai_lieu_kiem_thu
echo ======================================================================
echo.
pause
