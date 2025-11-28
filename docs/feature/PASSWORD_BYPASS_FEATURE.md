# Password Bypass Feature Implementation

## Overview

This feature adds a password bypass toggle (`grants_password_bypass`) for users, allowing specific users to bypass password verification for all encrypted albums. Additionally, albums can be marked with a special flag (`requires_password_despite_bypass`) to require passwords even if the user has bypass permissions.

## Database Changes

### 1. Users Table
**Migration**: `2025_11_28_122524_add_grants_password_bypass_to_users_table.php`

```php
$table->boolean('grants_password_bypass')->default(false);
```

Adds the `grants_password_bypass` field to the users table, defaulting to `false`.

### 2. Base Albums Table
**Migration**: `2025_11_28_122659_add_password_bypass_exception_to_base_albums_table.php`

```php
$table->boolean('requires_password_despite_bypass')->default(false);
```

Adds the `requires_password_despite_bypass` field to the base_albums table, defaulting to `false`.

## Model Changes

### User Model (`app/Models/User.php`)
- Added `grants_password_bypass` to `$casts` array
- Added `@property bool $grants_password_bypass` to PHPDoc

### BaseAlbumImpl Model (`app/Models/BaseAlbumImpl.php`)
- Added `requires_password_despite_bypass` to `$attributes` array
- Added `requires_password_despite_bypass` to `$casts` array
- Added `@property bool $requires_password_despite_bypass` to PHPDoc

## Logic Implementation

### Unlock Action (`app/Actions/Album/Unlock.php`)

**Changes**:
1. Added password bypass check in `do()` method
2. Added `propagateWithBypass()` method to unlock all bypass-allowed albums

**Logic Flow**:
```
User accesses encrypted album
    ↓
Check if user has grants_password_bypass permission
    ↓
    Yes → Check if album has requires_password_despite_bypass
                ↓
                No → Automatically unlock album
                Yes → Require password
    No → Require password
```

### UnlockWithPassword Middleware (`app/Http/Middleware/UnlockWithPassword.php`)

**Changes**:
Before processing the request, check if the current user has `grants_password_bypass` permission. If yes and the album does not have `requires_password_despite_bypass` set, unlock automatically.

## Use Cases

### Scenario 1: Admin Password-Free Access

Admin users can set `grants_password_bypass = true` and access all encrypted albums not marked as exceptions.

### Scenario 2: Special Albums Require Password

For albums containing sensitive content, set `requires_password_despite_bypass = true`. Even users with bypass permissions must enter the password.

## Pending Features

1. **API Endpoints**: Add API endpoints for updating the user `grants_password_bypass` field
2. **UI Components**: Add toggle control in user management interface
3. **Album UI**: Add `requires_password_despite_bypass` checkbox in album settings
4. **Testing**: Write unit and functional tests

## Security Considerations

- `grants_password_bypass` permission should only be granted to trusted users (e.g., administrators)
- `requires_password_despite_bypass` can be used to additionally protect sensitive albums
- All password verification logic is retained, only bypassed under specific conditions

## Testing Checklist

- [ ] Users without `grants_password_bypass` permission require password to access encrypted albums
- [ ] Users with `grants_password_bypass` permission can access standard encrypted albums without password
- [ ] Albums with `requires_password_despite_bypass = true` require password even for users with bypass permission
- [ ] Password propagation logic works correctly (unlocking one album unlocks all with same password)
- [ ] Bypass propagation logic works correctly (unlocking one album unlocks all bypass-allowed albums)

---

*Implementation Date: 2025-11-28*
*Last Updated: 2025-11-28*
