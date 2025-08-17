@echo off
echo ================================
echo  🔧 DEBUG MESSAGING SYSTEM
echo ================================
echo.

echo 1. Clearing Laravel logs...
if exist storage\logs\laravel.log (
    del storage\logs\laravel.log
    echo ✅ Laravel log cleared
) else (
    echo ℹ️ No Laravel log to clear
)

echo.
echo 2. Starting Laravel server...
start "Laravel Server" cmd /k "php artisan serve"
timeout /t 3

echo.
echo 3. Starting Vue.js dev server...
start "Vue.js Dev" cmd /k "npm run dev"
timeout /t 5

echo.
echo 4. Opening browser tabs...
start "" "http://localhost:8000"
start "" "http://localhost:5175"

echo.
echo ================================
echo  🚀 SERVERS STARTED
echo ================================
echo Laravel API: http://localhost:8000
echo Vue.js App:  http://localhost:5175
echo.
echo Instructions:
echo 1. Connectez-vous avec: njandjeu@gmail.com / password
echo 2. Allez sur un produit (ex: ID 5)
echo 3. Cliquez sur "Message"
echo 4. Regardez la console F12 pour les logs frontend
echo 5. Regardez les logs Laravel dans storage/logs/laravel.log
echo.
echo Appuyez sur une touche pour voir les logs Laravel en temps réel...
pause

echo.
echo ================================
echo  📊 LOGS LARAVEL EN TEMPS RÉEL
echo ================================
if exist storage\logs\laravel.log (
    powershell -Command "Get-Content storage\logs\laravel.log -Wait"
) else (
    echo ⏳ En attente des logs Laravel...
    timeout /t 2
    goto :logloop
)

:logloop
if exist storage\logs\laravel.log (
    powershell -Command "Get-Content storage\logs\laravel.log -Wait"
) else (
    timeout /t 1
    goto :logloop
)