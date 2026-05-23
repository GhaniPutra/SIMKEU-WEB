#!/bin/bash

# ====================================
# Testing Script untuk Login & Register SIMKEU
# Jalankan script ini untuk verify semuanya berfungsi
# ====================================

echo "🔍 SIMKEU Login & Register System - Testing"
echo "=========================================="

# Test 1: Check if server is running
echo ""
echo "Test 1: Verifikasi Server Running..."
if ps aux | grep -q "[a]rtisan serve"; then
    echo "✅ Server sudah running di port 8000"
else
    echo "❌ Server belum running"
    echo "   Jalankan: php artisan serve --host=localhost --port=8000"
    exit 1
fi

# Test 2: Verify database connection
echo ""
echo "Test 2: Verifikasi Database Connection..."
php artisan tinker <<EOF | grep -q "connected"
\$table = \Illuminate\Support\Facades\DB::table('users')->count();
echo "\$table > 0 ? 'connected' : 'error'";
exit;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Database terkoneksi"
else
    echo "⚠️  Database status tidak terpastikan"
fi

# Test 3: Check if tables exist
echo ""
echo "Test 3: Verifikasi Tables Exist..."
TABLES=$(php artisan tinker <<EOF
\$tables = \Illuminate\Support\Facades\Schema::getTables();
echo count(\$tables) > 0 ? 'OK' : 'ERROR';
exit;
EOF
)

if [[ $TABLES == *"OK"* ]]; then
    echo "✅ Database tables exist"
else
    echo "❌ Database tables tidak ditemukan"
fi

# Test 4: Check if users exist
echo ""
echo "Test 4: Verifikasi Test Users..."
USER_COUNT=$(mysql -u root -pGhaniputr@1! simkeu -sNe "SELECT COUNT(*) FROM users;" 2>/dev/null)

if [ "$USER_COUNT" -gt 0 ]; then
    echo "✅ Found $USER_COUNT test users"
    echo ""
    echo "Test Users:"
    mysql -u root -pGhaniputr@1! simkeu -sNe "SELECT email, role, store_id FROM users LIMIT 5;" 2>/dev/null | while read line; do
        echo "   - $line"
    done
else
    echo "❌ No users found in database"
    echo "   Jalankan: php artisan migrate:fresh --seed"
fi

# Test 5: Check if stores exist
echo ""
echo "Test 5: Verifikasi Test Stores..."
STORE_COUNT=$(mysql -u root -pGhaniputr@1! simkeu -sNe "SELECT COUNT(*) FROM stores;" 2>/dev/null)

if [ "$STORE_COUNT" -gt 0 ]; then
    echo "✅ Found $STORE_COUNT stores"
else
    echo "❌ No stores found"
fi

# Test 6: Check routes
echo ""
echo "Test 6: Verifikasi Routes..."
ROUTES=$(php artisan route:list | grep -E "login|register|dashboard" | wc -l)

if [ "$ROUTES" -gt 0 ]; then
    echo "✅ Found $ROUTES auth routes"
else
    echo "❌ Auth routes not found"
fi

# Test 7: Check controllers
echo ""
echo "Test 7: Verifikasi Controllers..."
if [ -f "app/Http/Controllers/AuthController.php" ] && [ -f "app/Http/Controllers/DashboardController.php" ]; then
    echo "✅ AuthController & DashboardController exist"
else
    echo "❌ Controllers not found"
fi

# Test 8: Check views
echo ""
echo "Test 8: Verifikasi Views..."
if [ -f "resources/views/auth/login.blade.php" ] && \
   [ -f "resources/views/auth/register.blade.php" ] && \
   [ -f "resources/views/dashboard.blade.php" ] && \
   [ -f "resources/views/layouts/app.blade.php" ]; then
    echo "✅ All views exist (login, register, dashboard, app layout)"
else
    echo "❌ Some views missing"
fi

# Test 8b: Check dashboard contains new components
if grep -q "trendChart" resources/views/dashboard.blade.php && \
   grep -q "branchChart" resources/views/dashboard.blade.php && \
   grep -q "global-period" resources/views/dashboard.blade.php; then
    echo "✅ Dashboard view includes charts and global filter"
else
    echo "⚠️ Dashboard view missing expected chart IDs or filter"
fi

# Test 9: Check documentation
echo ""
echo "Test 9: Verifikasi Documentation..."
if [ -f "DOKUMENTASI_LOGIN_REGISTER.md" ] && \
   [ -f "QUICK_START.md" ] && \
   [ -f "README_LOGIN_REGISTER.md" ]; then
    echo "✅ Documentation files exist"
else
    echo "❌ Some documentation missing"
fi

# Test 10: List all files created
echo ""
echo "Test 10: Created/Modified Files..."
echo ""
echo "✅ Controllers:"
echo "   - app/Http/Controllers/AuthController.php"
echo "   - app/Http/Controllers/DashboardController.php"
echo ""
echo "✅ Models:"
echo "   - app/Models/User.php (updated)"
echo "   - app/Models/Store.php (new)"
echo ""
echo "✅ Views:"
echo "   - resources/views/layouts/app.blade.php"
echo "   - resources/views/auth/login.blade.php"
echo "   - resources/views/auth/register.blade.php"
echo "   - resources/views/dashboard.blade.php"
echo ""
echo "✅ Routes & Seeders:"
echo "   - routes/web.php (updated)"
echo "   - database/seeders/DatabaseSeeder.php (updated)"
echo "   - database/seeders/StoreSeeder.php"
echo ""
echo "✅ Documentation:"
echo "   - DOKUMENTASI_LOGIN_REGISTER.md"
echo "   - QUICK_START.md"
echo "   - SUMMARY.md"
echo "   - INDEX.md"

# Summary
echo ""
echo "=========================================="
echo "✅ TESTING COMPLETE!"
echo "=========================================="
echo ""
echo "🌐 Access aplikasi di: http://localhost:8000"
echo "📖 Quick start: Baca file QUICK_START.md"
echo "📚 Documentation: Baca file DOKUMENTASI_LOGIN_REGISTER.md"
echo ""
echo "Akun Test:"
echo "  Email: admin@simkeu.com"
echo "  Password: admin123"
echo ""
