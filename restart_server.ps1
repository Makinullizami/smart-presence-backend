# Script untuk Restart Laravel Server
# Jalankan dengan: .\restart_server.ps1

Write-Host "🔄 Stopping Laravel server..." -ForegroundColor Yellow

# Stop semua proses PHP
Get-Process -Name "php" -ErrorAction SilentlyContinue | Stop-Process -Force

Write-Host "✅ Server stopped" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 Starting Laravel server..." -ForegroundColor Yellow

# Pindah ke direktori backend
Set-Location "c:\Semester 7\flutter\smart-presence-backend"

# Clear cache
php artisan config:clear
php artisan cache:clear

Write-Host "✅ Cache cleared" -ForegroundColor Green
Write-Host ""

# Start server
Write-Host "🌐 Server running at http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Gray
Write-Host ""

php artisan serve
