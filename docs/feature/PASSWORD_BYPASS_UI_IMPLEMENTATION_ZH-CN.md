# Password Bypass Feature - UI and API Implementation

## 概述 / Overview

本文档记录了用户密码旁路（Password Bypass）功能的 UI 管理界面和 API 端点的实现。

This document records the implementation of the UI management interface and API endpoints for the Password Bypass feature.

## 实现日期 / Implementation Date

2025-01-28

## 修改的文件 / Modified Files

### Backend / 后端

1. **app/Contracts/Http/Requests/RequestAttribute.php**
   - 添加了 `GRANTS_PASSWORD_BYPASS_ATTRIBUTE` 常量
   - Added `GRANTS_PASSWORD_BYPASS_ATTRIBUTE` constant

2. **app/Http/Requests/UserManagement/SetUserSettingsRequest.php**
   - 添加了 `grants_password_bypass` 字段和验证规则
   - 添加了 `grantsPasswordBypass()` 访问器方法
   - Added `grants_password_bypass` field and validation rules
   - Added `grantsPasswordBypass()` accessor method

3. **app/Http/Requests/UserManagement/AddUserRequest.php**
   - 添加了 `grants_password_bypass` 字段和验证规则
   - 添加了 `grantsPasswordBypass()` 访问器方法
   - Added `grants_password_bypass` field and validation rules
   - Added `grantsPasswordBypass()` accessor method

4. **app/Actions/User/Save.php**
   - 在 `do()` 方法签名中添加了 `grants_password_bypass` 参数
   - 在用户保存逻辑中添加了字段赋值
   - Added `grants_password_bypass` parameter to `do()` method signature
   - Added field assignment in user save logic

5. **app/Actions/User/Create.php**
   - 在 `do()` 方法签名中添加了 `grants_password_bypass` 参数
   - 在用户创建逻辑中添加了字段赋值
   - Added `grants_password_bypass` parameter to `do()` method signature
   - Added field assignment in user creation logic

6. **app/Http/Controllers/Admin/UserManagementController.php**
   - 在 `list()` 方法中添加了 `grants_password_bypass` 字段查询
   - 在 `save()` 和 `create()` 方法中传递 `grants_password_bypass` 参数
   - Added `grants_password_bypass` field query in `list()` method
   - Pass `grants_password_bypass` parameter in `save()` and `create()` methods

7. **app/Http/Resources/Models/UserManagementResource.php**
   - 添加了 `public bool $grants_password_bypass` 属性
   - 在构造函数中初始化该字段
   - Added `public bool $grants_password_bypass` property
   - Initialize field in constructor

### Frontend / 前端

8. **resources/js/services/user-management-service.ts**
   - 在 `UserManagementCreateRequest` 类型中添加了 `grants_password_bypass?` 字段
   - Added `grants_password_bypass?` field to `UserManagementCreateRequest` type

9. **resources/js/components/forms/users/CreateEditUser.vue**
   - 在模板中添加了密码旁路复选框
   - 添加了 `grants_password_bypass` ref 变量
   - 在 `createUser()` 和 `editUser()` 方法中包含该字段
   - 在成功创建/编辑后重置该字段
   - 在 `watch()` 处理器中更新该字段
   - Added password bypass checkbox in template
   - Added `grants_password_bypass` ref variable
   - Included field in `createUser()` and `editUser()` methods
   - Reset field after successful create/edit
   - Update field in `watch()` handler

### Localization / 本地化

10. **lang/en/users.php**
    - 添加了英文翻译：`'password_bypass' => 'User can bypass password-protected albums (except albums with special restriction).'`
    - Added English translation for password bypass feature

11. **lang/zh_CN/users.php**
    - 添加了中文翻译：`'password_bypass' => '用户可以无密码访问加密相册（特殊限制的相册除外）。'`
    - Added Chinese translation for password bypass feature

## API 端点 / API Endpoints

### 1. 获取用户列表 / Get User List

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

### 2. 创建用户 / Create User

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

### 3. 编辑用户 / Edit User

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

## UI 界面位置 / UI Location

在用户管理界面（Users Management）中：
1. 导航到 `设置 > 用户` (Settings > Users)
2. 点击"创建新用户"或编辑现有用户
3. 在权限部分可以看到新增的"用户可以无密码访问加密相册"复选框

In the User Management interface:
1. Navigate to `Settings > Users`
2. Click "Create a new user" or edit an existing user
3. In the permissions section, you'll see the new "User can bypass password-protected albums" checkbox

## 功能逻辑 / Feature Logic

### 密码旁路工作原理 / How Password Bypass Works

1. **用户启用旁路权限** / User has bypass permission enabled:
   - `users.grants_password_bypass = true`

2. **访问加密相册时** / When accessing password-protected albums:
   - 中间件 `UnlockWithPassword` 检查用户是否有旁路权限
   - Middleware `UnlockWithPassword` checks if user has bypass permission
   - 如果有权限且相册未设置特殊限制，自动解锁相册
   - If permission granted and album has no special restriction, automatically unlock album

3. **相册特殊限制** / Album Special Restriction:
   - `base_albums.requires_password_despite_bypass = true`
   - 即使用户有旁路权限，仍需要输入密码
   - Even with bypass permission, password is still required

## 测试建议 / Testing Recommendations

### 手动测试步骤 / Manual Testing Steps

1. **创建测试用户** / Create test user:
   - 创建一个新用户并启用密码旁路权限
   - Create a new user with password bypass enabled

2. **创建加密相册** / Create password-protected album:
   - 创建一个带密码的相册
   - Create an album with password protection

3. **测试旁路功能** / Test bypass functionality:
   - 使用测试用户登录
   - Login as test user
   - 访问加密相册，应该无需输入密码
   - Access password-protected album, should not require password

4. **测试特殊限制** / Test special restriction:
   - 将相册设置为 `requires_password_despite_bypass = true`
   - Set album's `requires_password_despite_bypass = true`
   - 即使有旁路权限，仍应要求输入密码
   - Should still require password even with bypass permission

### API 测试 / API Testing

使用以下 curl 命令测试 API：

```bash
# 获取用户列表
curl -X GET http://127.0.0.1:8000/api/v2/UserManagement \
  -H "Authorization: Bearer YOUR_TOKEN"

# 创建用户
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

# 编辑用户
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

## 安全考虑 / Security Considerations

1. **权限检查** / Permission Check:
   - 只有管理员用户可以修改 `grants_password_bypass` 设置
   - Only admin users can modify `grants_password_bypass` setting
   - 通过 `UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE` 策略控制
   - Controlled by `UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE` policy

2. **相册特殊限制** / Album Special Restriction:
   - 相册所有者可以设置 `requires_password_despite_bypass` 来覆盖用户的旁路权限
   - Album owners can set `requires_password_despite_bypass` to override user bypass permission
   - 提供了额外的控制层
   - Provides an additional layer of control

3. **审计日志** / Audit Logging:
   - 建议在修改用户权限时记录日志
   - Recommended to log when user permissions are modified
   - 可以通过检查用户的 `note` 字段来追踪变更原因
   - Can track change reasons through user's `note` field

## 下一步 / Next Steps

1. **相册设置 UI** / Album Settings UI:
   - 为相册添加 `requires_password_despite_bypass` 设置的 UI
   - Add UI for setting `requires_password_despite_bypass` on albums

2. **权限可视化** / Permission Visualization:
   - 在用户列表中显示旁路权限图标
   - Display bypass permission icon in user list
   - 在相册列表中显示特殊限制图标
   - Display special restriction icon in album list

3. **测试覆盖** / Test Coverage:
   - 添加单元测试和集成测试
   - Add unit tests and integration tests
   - 测试各种边界条件
   - Test various edge cases

---

*Last updated: 2025-01-28*
