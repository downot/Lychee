# Password Bypass Feature - Quick Test Guide

## Quick Test Guide

### Prerequisites

1. Ensure database migrations have run:
   ```bash
   php artisan migrate
   ```

2. Ensure frontend is built:
   ```bash
   npm run build
   # Or for development
   npm run dev
   ```

3. Server is running:
   - Laravel: http://127.0.0.1:8000
   - Vite (dev): http://localhost:5173

### Test Steps

#### 1. Test API Endpoints

##### 1.1 Get User List

```bash
# After logging in as admin, get user list
curl http://127.0.0.1:8000/api/v2/UserManagement \
  -H "Accept: application/json" \
  -b "lychee_session=YOUR_SESSION_COOKIE"
```

Expected response should contain the `grants_password_bypass` field.

##### 1.2 Create Test User

In browser:
1. Visit http://127.0.0.1:8000
2. Login as admin
3. Navigate to Settings > Users
4. Click "Create a new user"
5. Fill the form:
   - Username: `testbypass`
   - Password: `test123`
   - ✓ User can upload content
   - ✓ User can edit their own profile
   - ✓ **User can bypass password-protected albums** ← New Feature
6. Click "Create"

#### 2. Test Password Bypass Functionality

##### 2.1 Create Encrypted Album

1. Login as admin
2. Create a new album
3. Set password in album settings:
   - Settings > Protection > Password
   - Enter password, e.g.: `album123`
4. Save settings

##### 2.2 Test Bypass User Access

1. Logout admin account
2. Login as `testbypass` user (password: `test123`)
3. Try to access the encrypted album
4. **Expected Result**: Should see album content directly without entering password

##### 2.3 Test Normal User Access (Comparison)

1. Create another user without bypass permission:
   - Username: `testnormal`
   - Password: `test123`
   - ✓ User can upload content
   - ✓ User can edit their own profile
   - ✗ **User can bypass password-protected albums** ← Uncheck
2. Logout, login as `testnormal`
3. Try to access the encrypted album
4. **Expected Result**: Should see password prompt, requires password to access

#### 3. Test Edit User Permission

1. Login as admin
2. Navigate to Settings > Users
3. Click "Edit" for `testnormal` user
4. Check "User can bypass password-protected albums"
5. Save
6. Logout, login as `testnormal`
7. Access encrypted album
8. **Expected Result**: Should access directly now without password

#### 4. Verify Database

```bash
# Connect to database
mysql -h 127.0.0.1 -P 33061 -u lychee -p lychee

# Check users table
SELECT id, username, grants_password_bypass FROM users;

# Should see output similar to:
# +----+-------------+-----------------------+
# | id | username    | grants_password_bypass|
# +----+-------------+-----------------------+
# |  1 | admin       |                     0 |
# |  2 | testbypass  |                     1 |
# |  3 | testnormal  |                     1 |
# +----+-------------+-----------------------+

# Check albums table (verify requires_password_despite_bypass field)
SELECT id, title, password, requires_password_despite_bypass FROM base_albums WHERE password IS NOT NULL;
```

### Known Issues

1. **Frontend Translation Missing**:
   - Some language files (German, French, etc.) haven't added `password_bypass` translation
   - These languages will show English key name
   - Solution: Add corresponding translation for each language file

2. **Album Special Restriction UI**:
   - `requires_password_despite_bypass` field UI setting interface not yet implemented
   - Currently can only be modified via database directly
   - Need to add checkbox in album protection settings

### Next Development Steps

1. **Album Settings UI**:
   ```
   Add to album protection settings:
   □ Require password even for users with bypass permission
   ```

2. **Complete Translations**:
   Add `password_bypass` translation for all supported languages

3. **Add Tests**:
   Write unit tests and functional tests

4. **User List Icons**:
   Display password bypass permission icon in user list

### Troubleshooting

#### Issue: TypeScript types not updated

```bash
# Regenerate TypeScript types
php artisan typescript:transform
```

#### Issue: Frontend not showing new field

```bash
# Clear cache and rebuild
npm run build
# Or
npm run dev
```

#### Issue: API returns 500 error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

#### Issue: Database field does not exist

```bash
# Run migrations
php artisan migrate

# If already run, force refresh
php artisan migrate:refresh --path=database/migrations/2025_11_28_122524_add_grants_password_bypass_to_users_table.php
php artisan migrate:refresh --path=database/migrations/2025_11_28_122659_add_password_bypass_exception_to_base_albums_table.php
```

---

*Last updated: 2025-01-28*
