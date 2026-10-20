@echo off
title Auto Push GitHub - RTQ Utsmaniyyah
color 0a

echo ========================================================
echo   AUTO PUSH GITHUB REPOSITORY - RTQ UTSMANIYYAH
echo   Target: https://github.com/goldhunter240819-create/utsmaniyyah.git
echo ========================================================
echo.

:: Masuk ke direktori file bat berada
cd /d "%~dp0"

echo [1/4] Memeriksa status Git lokal...
git status
echo.

echo [2/4] Menambahkan seluruh perubahan (git add)...
git add .
echo.

echo [3/4] Melakukan commit perubahan...
set /p commit_msg="Masukkan pesan commit (tekan Enter untuk default): "
if "%commit_msg%"=="" set commit_msg=update: sinkronisasi aplikasi munaqosyah utsmaniyyah
git commit -m "%commit_msg%"
echo.

echo [4/4] Memastikan branch main dan remote origin...
git branch -M main
git remote remove origin 2>nul
git remote add origin https://github.com/goldhunter240819-create/utsmaniyyah.git

echo.
echo Melakukan push ke GitHub...
git push -u origin main

echo.
if %errorlevel% equ 0 (
    echo ========================================================
    echo   [SUKSES] Berhasil di-push ke GitHub!
    echo   Link: https://github.com/goldhunter240819-create/utsmaniyyah
    echo ========================================================
) else (
    echo ========================================================
    echo   [PERHATIAN] Push selesai atau butuh otentikasi Git.
    echo ========================================================
)

echo.
pause
