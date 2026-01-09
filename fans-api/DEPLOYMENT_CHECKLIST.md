# Deployment Checklist for Terms and Conditions API

## Pre-Deployment Checklist

### 1. Database Migration
- [ ] Run migration: `php artisan migrate`
- [ ] Verify table exists: `php artisan tinker` → `Schema::hasTable('terms_and_conditions')`
- [ ] Check migration status: `php artisan migrate:status`

### 2. Database Seeding
- [ ] Run seeder: `php artisan db:seed --class=TermsAndConditionSeeder`
- [ ] Verify data exists: `php artisan tinker` → `App\Models\TermsAndCondition::count()`

### 3. Route Verification
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Verify routes: `php artisan route:list | grep terms`

### 4. Controller Verification
- [ ] Check controller exists: `ls app/Http/Controllers/TermsAndConditionController.php`
- [ ] Verify model exists: `ls app/Models/TermsAndCondition.php`

## Post-Deployment Testing

### 1. API Endpoint Tests
```bash
# Test CORS
curl -X GET https://api.fans4more.com/api/cors-test

# Test Terms API
curl -X GET https://api.fans4more.com/api/terms-test

# Test Terms Latest
curl -X GET https://api.fans4more.com/api/terms/latest
```

### 2. Expected Responses

#### CORS Test (Should return 200)
```json
{
  "message": "CORS is working!"
}
```

#### Terms Test (Should return 200)
```json
{
  "message": "Terms API is accessible",
  "timestamp": "2024-08-02T...",
  "status": "working"
}
```

#### Terms Latest (Should return 200 with data or 503 if setup in progress)
```json
{
  "success": true,
  "data": { ... }
}
```

## Common Issues and Solutions

### Issue 1: 404 Error on /api/terms/latest
**Cause**: Route not registered or CORS middleware issue
**Solution**: 
1. Check if route is inside CORS middleware group
2. Clear route cache: `php artisan route:clear`
3. Verify route registration: `php artisan route:list | grep terms`

### Issue 2: 503 Error on /api/terms/latest
**Cause**: Database table doesn't exist or migration not run
**Solution**:
1. Run migration: `php artisan migrate`
2. Run seeder: `php artisan db:seed --class=TermsAndConditionSeeder`
3. Check database connection

### Issue 3: 404 Error on /api/terms-test
**Cause**: Deployment not completed
**Solution**: Wait for deployment to complete (usually 2-5 minutes)

### Issue 4: CORS Errors
**Cause**: Route outside CORS middleware
**Solution**: Ensure routes are inside `Route::middleware('cors')->group()`

## Deployment Timeline

1. **Code Push**: 0 minutes
2. **Deployment Start**: 1-2 minutes
3. **Route Registration**: 2-3 minutes
4. **Database Migration**: 3-4 minutes
5. **API Available**: 4-5 minutes

## Monitoring Commands

```bash
# Check deployment status
curl -X GET https://api.fans4more.com/api/cors-test

# Check terms API status
curl -X GET https://api.fans4more.com/api/terms-test

# Check terms data
curl -X GET https://api.fans4more.com/api/terms/latest
```

## Emergency Fallback

If deployment takes too long, the frontend has retry logic with exponential backoff that will automatically retry until the API is available. 