# Asset Loading Fix Guide for Shared Hosting

## Problem Description

**Symptoms:**
- Assets loading with 404 errors in browser console
- CSS not loading (broken styling)  
- JavaScript not working
- Console errors: `GET https://domain.com/build/assets/app-compiled.js net::ERR_ABORTED 404 (Not Found)`
- MIME type errors: `Refused to apply style from 'https://domain.com/build/assets/app-compiled.css' because its MIME type ('text/html') is not a supported stylesheet MIME type`

**Root Causes:**
1. Shared hosting routes ALL requests through Laravel (even static files)
2. Build assets contain ES6 imports instead of properly bundled code
3. Web server doesn't serve static files directly
4. Manifest.json points to incorrect asset paths

## Solution Steps

### Step 1: Add Laravel Routes for Asset Serving

Add these routes to `routes/web.php`:

```php
// Asset serving routes for restricted hosting
Route::get('/app-compiled.css', function () {
    return response()->file(public_path('app-compiled.css'), ['Content-Type' => 'text/css']);
});

Route::get('/app-compiled.js', function () {
    return response()->file(public_path('app-compiled.js'), ['Content-Type' => 'application/javascript']);
});

// Additional routes for build path (if needed)
Route::get('/build/app-compiled.css', function () {
    return response()->file(public_path('app-compiled.css'), ['Content-Type' => 'text/css']);
});

Route::get('/build/app-compiled.js', function () {
    return response()->file(public_path('app-compiled.js'), ['Content-Type' => 'application/javascript']);
});
```

**Terminal commands:**
```bash
echo "" >> routes/web.php
echo "// Asset serving routes for restricted hosting" >> routes/web.php
echo "Route::get('/app-compiled.css', function () {" >> routes/web.php
echo "    return response()->file(public_path('app-compiled.css'), ['Content-Type' => 'text/css']);" >> routes/web.php
echo "});" >> routes/web.php
echo "" >> routes/web.php
echo "Route::get('/app-compiled.js', function () {" >> routes/web.php
echo "    return response()->file(public_path('app-compiled.js'), ['Content-Type' => 'application/javascript']);" >> routes/web.php
echo "});" >> routes/web.php
```

### Step 2: Use Properly Built Assets

**Problem:** Source files contain ES6 imports that browsers can't resolve:
```javascript
import { Swiper } from 'swiper';  // ❌ This causes errors
```

**Solution:** Use properly compiled/bundled assets:

```bash
# Copy properly built assets from deploy_package (if available)
cp deploy_package/build/assets/*.js public/app-compiled.js
cp deploy_package/build/assets/*.css public/app-compiled.css

# OR build them locally and upload
npm run build
cp public/build/assets/*.js public/app-compiled.js  
cp public/build/assets/*.css public/app-compiled.css
```

### Step 3: Update Manifest.json

Update `public/build/manifest.json` to point to correct asset paths:

```json
{
  "resources/css/app.css": {
    "file": "app-compiled.css",
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/js/app.js": {
    "file": "app-compiled.js", 
    "isEntry": true,
    "src": "resources/js/app.js"
  }
}
```

**Terminal command:**
```bash
echo '{
  "resources/css/app.css": {
    "file": "app-compiled.css",
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/js/app.js": {
    "file": "app-compiled.js",
    "isEntry": true,
    "src": "resources/js/app.js"
  }
}' > public/build/manifest.json
```

### Step 4: Clear Laravel Caches

```bash
php artisan route:clear
php artisan route:cache  
php artisan view:clear
php artisan config:clear
```

### Step 5: Test the Fix

**Test asset routes:**
```bash
curl -I https://your-domain.com/app-compiled.css
curl -I https://your-domain.com/app-compiled.js
```

**Expected response:**
- ✅ `HTTP/2 200` (not 404)
- ✅ `Content-Type: text/css` for CSS
- ✅ `Content-Type: application/javascript` for JS

**Test in browser:**
1. Hard refresh (Ctrl+F5 or Cmd+Shift+R)
2. Check browser console - no more 404 errors
3. CSS styling should work
4. JavaScript should load without import errors

### Step 6: Verify Route Registration

```bash
php artisan route:list | grep "app-compiled"
```

Should show:
```
GET|HEAD  app-compiled.css  
GET|HEAD  app-compiled.js
```

## File Checklist

Files to commit to git:
- ✅ `routes/web.php` (with asset serving routes)
- ✅ `public/build/manifest.json` (updated paths)  
- ✅ `public/app-compiled.css` (properly built CSS)
- ✅ `public/app-compiled.js` (properly built JS)

## Common Issues & Solutions

### Issue 1: Routes still return 404
**Solution:** Clear route cache and ensure files exist:
```bash
php artisan route:clear
php artisan route:cache
ls -la public/app-compiled.*
```

### Issue 2: JavaScript import errors persist
**Problem:** Using source files instead of built assets
**Solution:** Ensure you're using properly bundled assets without ES6 imports

### Issue 3: CSS MIME type errors
**Problem:** Route not serving correct Content-Type header
**Solution:** Verify route includes `['Content-Type' => 'text/css']` parameter

### Issue 4: Assets load but website still broken
**Problem:** Assets may be empty or corrupted
**Solution:** Check file sizes and content:
```bash
ls -la public/app-compiled.*
head -10 public/app-compiled.js  # Should show bundled code, not imports
```

## Why This Solution Works

**Traditional Setup:**
- Web server serves static files directly from `/public/build/assets/`
- Works on standard hosting with proper document root

**Restricted Hosting Setup:**
- ALL requests route through Laravel (even static files)
- Static file serving is disabled or misconfigured
- Laravel routes serve assets with proper MIME types
- Bypasses server restrictions

## Prevention

To avoid this issue in future deployments:

1. **Always test asset loading** after deployment
2. **Use properly built assets** (not source files)
3. **Test on production-like environment** before going live
4. **Keep this guide handy** for quick reference

---

**Last Updated:** August 15, 2025  
**Tested On:** Shared hosting with restricted static file serving  
**Laravel Version:** 11.x