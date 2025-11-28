# Password Bypass Feature - Quick Test Guide

## 快速测试指南 / Quick Test Guide

### 前提条件 / Prerequisites

1. 确保数据库迁移已运行：
   ```bash
   php artisan migrate
   ```

2. 确保前端已编译：
   ```bash
   npm run build
   # 或 for development
   npm run dev
   ```

3. 服务器正在运行：
   - Laravel: http://127.0.0.1:8000
   - Vite (dev): http://localhost:5173

### 测试步骤 / Test Steps

#### 1. 测试 API 端点 / Test API Endpoints

##### 1.1 获取用户列表

```bash
# 使用管理员账号登录后，获取用户列表
curl http://127.0.0.1:8000/api/v2/UserManagement \
  -H "Accept: application/json" \
  -b "lychee_session=YOUR_SESSION_COOKIE"
```

预期响应应包含 `grants_password_bypass` 字段。

##### 1.2 创建测试用户

在浏览器中：
1. 访问 http://127.0.0.1:8000
2. 使用管理员账号登录
3. 导航到 设置 > 用户 (Settings > Users)
4. 点击"创建新用户"按钮
5. 填写表单：
   - Username: `testbypass`
   - Password: `test123`
   - ✓ 用户可以上传内容
   - ✓ 用户可以修改其个人资料
   - ✓ **用户可以无密码访问加密相册** ← 新功能
6. 点击"创建"

#### 2. 测试密码旁路功能 / Test Password Bypass Functionality

##### 2.1 创建加密相册

1. 以管理员身份登录
2. 创建一个新相册
3. 在相册设置中设置密码：
   - Settings > Protection > Password
   - 输入密码，例如：`album123`
4. 保存设置

##### 2.2 测试旁路用户访问

1. 登出管理员账号
2. 使用 `testbypass` 用户登录 (password: `test123`)
3. 尝试访问加密相册
4. **预期结果**：应该可以直接看到相册内容，无需输入密码

##### 2.3 测试普通用户访问（对比）

1. 创建另一个没有旁路权限的用户：
   - Username: `testnormal`
   - Password: `test123`
   - ✓ 用户可以上传内容
   - ✓ 用户可以修改其个人资料
   - ✗ **用户可以无密码访问加密相册** ← 不勾选
2. 登出，使用 `testnormal` 登录
3. 尝试访问加密相册
4. **预期结果**：应该看到密码输入框，需要输入密码才能访问

#### 3. 测试编辑用户权限 / Test Edit User Permission

1. 以管理员身份登录
2. 导航到 设置 > 用户
3. 点击 `testnormal` 用户的"编辑"按钮
4. 勾选"用户可以无密码访问加密相册"
5. 保存
6. 登出，以 `testnormal` 登录
7. 访问加密相册
8. **预期结果**：现在应该可以直接访问，无需密码

#### 4. 验证数据库 / Verify Database

```bash
# 连接到数据库
mysql -h 127.0.0.1 -P 33061 -u lychee -p lychee

# 查看用户表
SELECT id, username, grants_password_bypass FROM users;

# 应该看到类似输出：
# +----+-------------+-----------------------+
# | id | username    | grants_password_bypass|
# +----+-------------+-----------------------+
# |  1 | admin       |                     0 |
# |  2 | testbypass  |                     1 |
# |  3 | testnormal  |                     1 |
# +----+-------------+-----------------------+

# 查看相册表（检查 requires_password_despite_bypass 字段）
SELECT id, title, password, requires_password_despite_bypass FROM base_albums WHERE password IS NOT NULL;
```

### 已知问题 / Known Issues

1. **前端翻译缺失**：
   - 部分语言文件（德语、法语等）尚未添加 `password_bypass` 翻译
   - 这些语言会显示英文键名
   - 解决方案：为每个语言文件添加对应翻译

2. **相册特殊限制 UI**：
   - `requires_password_despite_bypass` 字段的 UI 设置界面尚未实现
   - 当前只能通过数据库直接修改
   - 需要在相册保护设置中添加复选框

### 下一步开发 / Next Development Steps

1. **相册设置 UI**：
   ```
   在相册保护设置中添加：
   □ 即使用户有密码旁路权限也要求密码
     (Require password even for users with bypass permission)
   ```

2. **完善翻译**：
   为所有支持的语言添加 `password_bypass` 翻译

3. **添加测试**：
   编写单元测试和功能测试

4. **用户列表图标**：
   在用户列表中显示密码旁路权限的图标标识

### 故障排除 / Troubleshooting

#### 问题：TypeScript 类型未更新

```bash
# 重新生成 TypeScript 类型
php artisan typescript:transform
```

#### 问题：前端未显示新字段

```bash
# 清理缓存并重新编译
npm run build
# 或
npm run dev
```

#### 问题：API 返回 500 错误

```bash
# 检查 Laravel 日志
tail -f storage/logs/laravel.log

# 清理应用缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

#### 问题：数据库字段不存在

```bash
# 运行迁移
php artisan migrate

# 如果已经运行过，强制重新运行
php artisan migrate:refresh --path=database/migrations/2025_11_28_122524_add_grants_password_bypass_to_users_table.php
php artisan migrate:refresh --path=database/migrations/2025_11_28_122659_add_password_bypass_exception_to_base_albums_table.php
```

---

*Last updated: 2025-01-28*
