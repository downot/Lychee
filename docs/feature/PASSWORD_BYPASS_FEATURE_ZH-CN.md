# Password Bypass Feature Implementation

## 概述 (Overview)

该功能为用户添加了一个免密开关（`grants_password_bypass`），允许特定用户绕过所有加密相册的密码验证。同时，相册可以设置一个特殊标志（`requires_password_despite_bypass`）来强制要求密码，即使用户拥有免密权限。

This feature adds a password bypass toggle (`grants_password_bypass`) for users, allowing specific users to bypass password verification for all encrypted albums. Additionally, albums can be marked with a special flag (`requires_password_despite_bypass`) to require passwords even if the user has bypass permissions.

## 数据库变更 (Database Changes)

### 1. Users Table
**Migration**: `2025_11_28_122524_add_grants_password_bypass_to_users_table.php`

```php
$table->boolean('grants_password_bypass')->default(false);
```

为用户表添加 `grants_password_bypass` 字段，默认为 `false`。
Adds the `grants_password_bypass` field to the users table, defaulting to `false`.

### 2. Base Albums Table  
**Migration**: `2025_11_28_122659_add_password_bypass_exception_to_base_albums_table.php`

```php
$table->boolean('requires_password_despite_bypass')->default(false);
```

为相册表添加 `requires_password_despite_bypass` 字段，默认为 `false`。
Adds the `requires_password_despite_bypass` field to the base_albums table, defaulting to `false`.

## 模型变更 (Model Changes)

### User Model (`app/Models/User.php`)
- 添加 `grants_password_bypass` 到 `$casts` 数组
- 添加 `@property bool $grants_password_bypass` 到 PHPDoc

### BaseAlbumImpl Model (`app/Models/BaseAlbumImpl.php`)
- 添加 `requires_password_despite_bypass` 到 `$attributes` 数组
- 添加 `requires_password_despite_bypass` 到 `$casts` 数组  
- 添加 `@property bool $requires_password_despite_bypass` 到 PHPDoc

## 逻辑实现 (Logic Implementation)

### Unlock Action (`app/Actions/Album/Unlock.php`)

**Changes**:
1. 在 `do()` 方法中添加密码绕过检查
2. 新增 `propagateWithBypass()` 方法来解锁所有允许绕过的相册

**Logic Flow**:
```
用户访问加密相册 (User accesses encrypted album)
    ↓
检查用户是否有 grants_password_bypass 权限
(Check if user has grants_password_bypass permission)
    ↓
    是 (Yes) → 检查相册是否设置 requires_password_despite_bypass
                (Check if album has requires_password_despite_bypass)
                ↓
                否 (No) → 自动解锁相册 (Automatically unlock album)
                是 (Yes) → 需要密码 (Require password)
    否 (No) → 需要密码 (Require password)
```

### UnlockWithPassword Middleware (`app/Http/Middleware/UnlockWithPassword.php`)

**Changes**:
在处理请求前，检查当前用户是否具有 `grants_password_bypass` 权限，如果有且相册未设置 `requires_password_despite_bypass`，则自动解锁。

Before processing the request, check if the current user has `grants_password_bypass` permission. If yes and the album does not have `requires_password_despite_bypass` set, unlock automatically.

## 使用场景 (Use Cases)

### 场景 1: 管理员免密访问
**Scenario 1: Admin Password-Free Access**

管理员用户可以设置 `grants_password_bypass = true`，然后可以访问所有未标记为例外的加密相册。

Admin users can set `grants_password_bypass = true` and access all encrypted albums not marked as exceptions.

### 场景 2: 特殊相册强制密码  
**Scenario 2: Special Albums Require Password**

对于包含敏感内容的相册，设置 `requires_password_despite_bypass = true`，即使用户有免密权限，也必须输入密码。

For albums containing sensitive content, set `requires_password_despite_bypass = true`. Even users with bypass permissions must enter the password.

## 待完成功能 (Pending Features)

1. **API Endpoints**: 添加用于更新用户 `grants_password_bypass` 字段的 API 端点
2. **UI Components**: 在用户管理界面添加开关控制
3. **Album UI**: 在相册设置中添加 `requires_password_despite_bypass` 复选框
4. **Testing**: 编写单元测试和功能测试

## 安全考虑 (Security Considerations)

- `grants_password_bypass` 权限应该只授予受信任的用户（如管理员）
- `requires_password_despite_bypass` 可用于额外保护敏感相册
- 所有密码验证逻辑仍然保留，只是在特定条件下被跳过

- `grants_password_bypass` permission should only be granted to trusted users (e.g., administrators)
- `requires_password_despite_bypass` can be used to additionally protect sensitive albums
- All password verification logic is retained, only bypassed under specific conditions

## 测试检查清单 (Testing Checklist)

- [ ] 用户没有 `grants_password_bypass` 权限时，需要密码访问加密相册
- [ ] 用户有 `grants_password_bypass` 权限时，可以免密访问普通加密相册
- [ ] 相册设置 `requires_password_despite_bypass = true` 时，即使用户有免密权限也需要密码
- [ ] 密码传播逻辑正常工作（解锁一个相册时，解锁所有相同密码的相册）
- [ ] 免密传播逻辑正常工作（解锁一个相册时，解锁所有允许绕过的相册）

---

*实现日期 (Implementation Date): 2025-11-28*
*最后更新 (Last Updated): 2025-11-28*
