# Password Bypass Feature - UI and API Implementation

## Overview

This document records the implementation of the UI management interface and API endpoints for the Password Bypass feature.

## Implementation Date

2025-01-28

## Modified Files

### Backend

1. **app/Contracts/Http/Requests/RequestAttribute.php**
   - Added `GRANTS_PASSWORD_BYPASS_ATTRIBUTE` constant

2. **app/Http/Requests/UserManagement/SetUserSettingsRequest.php**
   - Added `grants_password_bypass` field and validation rules
   - Added `grantsPasswordBypass()` accessor method

3. **app/Http/Requests/UserManagement/AddUserRequest.php**
   - Added `grants_password_bypass` field and validation rules
   - Added `grantsPasswordBypass()` accessor method

4. **app/Actions/User/Save.php**
   - Added `grants_password_bypass` parameter to `do()` method signature
   - Added field assignment in user save logic

5. **app/Actions/User/Create.php**
   - Added `grants_password_bypass` parameter to `do()` method signature
   - Added field assignment in user creation logic

6. **app/Http/Controllers/Admin/UserManagementController.php**
   - Added `grants_password_bypass` field query in `list()` method
   - Pass `grants_password_bypass` parameter in `save()` and `create()` methods

7. **app/Http/Resources/Models/UserManagementResource.php**
   - Added `public bool $grants_password_bypass` property
   - Initialize field in constructor

### Frontend

8. **resources/js/services/user-management-service.ts**
   - Added `grants_password_bypass?` field to `UserManagementCreateRequest` type

9. **resources/js/components/forms/users/CreateEditUser.vue**
   - Added password bypass checkbox in template
   - Added `grants_password_bypass` ref variable
   - Included field in `createUser()` and `editUser()` methods
   - Reset field after successful create/edit
   - Update field in `watch()` handler

### Localization

10. **lang/en/users.php**
    - Added English translation: `'password_bypass' => 'User can bypass password-protected albums (except albums with special restriction).'`

11. **lang/zh_CN/users.php**
    - Added Chinese translation: `'password_bypass' => '用户可以无密码访问加密相册（特殊限制的相册除外）。'`

## API Endpoints

### 1. Get User List

**Endpoint:** `GET /api/v2/UserManagement`

**Response:**
```json
[
  {
    "id": 1,
    "username": "admin",
    "may_administrate": true,
    "may_upload": true,
    "may_edit_own_settings": true,
    "grants_password_bypass": false,
    "is_owner": true,
    "quota_kb": null,
    "description": null,
    "note": null,
    "space": 1024000
  }
]
```

### 2. Create User

**Endpoint:** `POST /api/v2/UserManagement`

**Request Body:**
```json
{
  "username": "testuser",
  "password": "password123",
  "may_upload": true,
  "may_edit_own_settings": true,
  "may_administrate": false,
  "grants_password_bypass": true,
  "has_quota": false,
  "quota_kb": 0,
  "note": "Test user with password bypass"
}
```

### 3. Edit User

**Endpoint:** `PATCH /api/v2/UserManagement`

**Request Body:**
```json
{
  "id": 2,
  "username": "testuser",
  "password": null,
  "may_upload": true,
  "may_edit_own_settings": true,
  "may_administrate": false,
  "grants_password_bypass": true,
  "has_quota": false,
  "quota_kb": 0,
  "note": "Updated note"
}
```

## UI Location

In the User Management interface:
1. Navigate to `Settings > Users`
2. Click "Create a new user" or edit an existing user
3. In the permissions section, you'll see the new "User can bypass password-protected albums" checkbox

## Feature Logic

### How Password Bypass Works

1. **User has bypass permission enabled**:
   - `users.grants_password_bypass = true`

2. **When accessing password-protected albums**:
   - Middleware `UnlockWithPassword` checks if user has bypass permission
   - If permission granted and album has no special restriction, automatically unlock album

3. **Album Special Restriction**:
   - `base_albums.requires_password_despite_bypass = true`
   - Even with bypass permission, password is still required

## Testing Recommendations

### Manual Testing Steps

1. **Create test user**:
   - Create a new user with password bypass enabled

2. **Create password-protected album**:
   - Create an album with password protection

3. **Test bypass functionality**:
   - Login as test user
   - Access password-protected album, should not require password

4. **Test special restriction**:
   - Set album's `requires_password_despite_bypass = true`
   - Should still require password even with bypass permission

### API Testing

Use the following curl commands to test API:

```bash
# Get User List
curl -X GET http://127.0.0.1:8000/api/v2/UserManagement \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create User
curl -X POST http://127.0.0.1:8000/api/v2/UserManagement \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "username": "testuser",
    "password": "password123",
    "may_upload": true,
    "may_edit_own_settings": true,
    "may_administrate": false,
    "grants_password_bypass": true
  }'

# Edit User
curl -X PATCH http://127.0.0.1:8000/api/v2/UserManagement \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "id": 2,
    "username": "testuser",
    "may_upload": true,
    "may_edit_own_settings": true,
    "may_administrate": false,
    "grants_password_bypass": false
  }'
```

## Security Considerations

1. **Permission Check**:
   - Only admin users can modify `grants_password_bypass` setting
   - Controlled by `UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE` policy

2. **Album Special Restriction**:
   - Album owners can set `requires_password_despite_bypass` to override user bypass permission
   - Provides an additional layer of control

3. **Audit Logging**:
   - Recommended to log when user permissions are modified
   - Can track change reasons through user's `note` field

## Next Steps

1. **Album Settings UI**:
   - Add UI for setting `requires_password_despite_bypass` on albums

2. **Permission Visualization**:
   - Display bypass permission icon in user list
   - Display special restriction icon in album list

3. **Test Coverage**:
   - Add unit tests and integration tests
   - Test various edge cases

---

*Last updated: 2025-01-28*
