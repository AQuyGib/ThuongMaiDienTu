<?php
use App\Models\User;

$search = request('search');
$query = User::with('role');

if ($search) {
    $query->where('full_name', 'like', "%$search%")
          ->orWhere('email', 'like', "%$search%")
          ->orWhere('user_id', 'like', "%$search%");
}

$users = $query->orderBy('user_id', 'DESC')->get();
$total_users = $users->count();

// Xử lý Xóa
if (request()->isMethod('post') && request('action') == 'delete') {
    $del_id = request('user_id');
    if ($del_id != 1) {
        User::destroy($del_id);
        header("Location: /users?message=deleted");
        exit();
    }
}

// Xử lý Thêm mới
if (request()->isMethod('post') && request('action') == 'add') {
    User::create([
        'full_name' => request('full_name'),
        'email' => request('email'),
        'password_hash' => Hash::make(request('password')),
        'role_id' => request('role_id'),
        'status' => request('status', 'Active'),
        'member_tier' => 'Dong'
    ]);
    header("Location: /users?message=added");
    exit();
}

// Xử lý Cập nhật
if (request()->isMethod('post') && request('action') == 'edit') {
    $user_id = request('user_id');
    $user = User::find($user_id);
    if ($user) {
        $data = [
            'full_name' => request('full_name'),
            'email' => request('email'),
            'role_id' => request('role_id'),
            'status' => request('status'),
        ];
        if (request('password')) {
            $data['password_hash'] = Hash::make(request('password'));
        }
        $user->update($data);
    }
    header("Location: /users?message=updated");
    exit();
}

$message = '';
$msg_type = request('message');
if ($msg_type == 'deleted') $message = "Đã xóa tài khoản thành công!";
if ($msg_type == 'added') $message = "Đã thêm tài khoản mới thành công!";
if ($msg_type == 'updated') $message = "Đã cập nhật tài khoản thành công!";

$roles = \App\Models\Role::all();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tài Khoản - DIENMAYPRO</title>
    <style>
        /* CSS RESET & CƠ BẢN */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f3f4f6; color: #333; display: flex; height: 100vh; overflow: hidden; }
        a { text-decoration: none; color: inherit; }

        /* ==================== SIDEBAR ==================== */
        .sidebar { width: 260px; background-color: #111827; color: #9ca3af; display: flex; flex-direction: column; height: 100%; transition: all 0.3s; }
        .logo-area { padding: 20px; display: flex; align-items: center; gap: 10px; font-size: 20px; font-weight: 800; color: #fff; border-bottom: 1px solid #1f2937; margin-bottom: 10px;}
        .logo-area svg { color: #fbbf24; width: 24px; height: 24px; }
        
        .menu-group { font-size: 11px; text-transform: uppercase; font-weight: 600; padding: 15px 20px 5px; color: #6b7280; letter-spacing: 0.5px; }
        .menu-list { list-style: none; padding: 0 10px; flex: 1; overflow-y: auto; }
        .menu-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; margin-bottom: 4px; border-radius: 8px; cursor: pointer; color: #d1d5db; transition: 0.2s; font-weight: 500; font-size: 14px;}
        .menu-item:hover { background-color: #1f2937; color: #fff; }
        .menu-item svg { width: 18px; height: 18px; opacity: 0.8; }
        
        /* YÊU CẦU: Menu Tài khoản Active (Nền xanh, chữ trắng) */
        .menu-item.active { background-color: #0d6efd; color: #fff; }
        .menu-item.active svg { opacity: 1; }

        /* YÊU CẦU: Thông tin User ở cuối Sidebar */
        .sidebar-footer { padding: 15px; background-color: #1f2937; border-top: 1px solid #374151; }
        .user-profile { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: #4b5563; display: flex; justify-content: center; align-items: center; color: white; font-weight: bold;}
        .user-info .name { color: #fff; font-weight: 600; font-size: 14px; margin-bottom: 2px;}
        .user-info .role { color: #10b981; font-size: 11px; font-weight: 700; text-transform: uppercase; } /* ADMIN màu xanh lá */
        
        .btn-back-web { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px; background-color: #374151; color: #d1d5db; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-back-web:hover { background-color: #4b5563; color: white; }

        /* ==================== MAIN CONTENT ==================== */
        .main-content { flex: 1; display: flex; flex-direction: column; height: 100%; overflow: hidden; }
        
        /* Topbar */
        .topbar { background-color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; }
        /* YÊU CẦU: Text "Quản lý Tài Khoản" in đậm */
        .topbar-title { font-size: 18px; font-weight: 700; color: #1f2937; } 
        
        /* Form tìm kiếm */
        .search-box { position: relative; width: 300px; }
        .search-box input { width: 100%; padding: 10px 15px 10px 40px; border: 1px solid #e5e7eb; border-radius: 20px; background-color: #f9fafb; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .search-box input:focus { border-color: #0d6efd; background-color: #fff; }
        .search-box svg { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; width: 16px; height: 16px; }

        /* Content Body */
        .content-body { padding: 30px; flex: 1; overflow-y: auto; }
        .card { background-color: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; }
        
        /* Header Card */
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header .total-count { font-size: 16px; color: #4b5563; font-weight: 500; }
        .btn-add { display: flex; align-items: center; gap: 8px; background-color: #0d6efd; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: 0.2s; }
        .btn-add:hover { background-color: #0b5ed7; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 15px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; font-size: 14px;}
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }

        /* ID Style */
        .col-id { color: #9ca3af; font-weight: 500; font-size: 13px; width: 50px;}

        /* Name & Username */
        .user-name-cell .name { font-weight: 600; color: #1f2937; margin-bottom: 3px; }
        .user-name-cell .username { color: #6b7280; font-size: 12px; }

        /* Badges Quyền */
        .badge { padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-admin { background-color: #fee2e2; color: #ef4444; } /* Nền đỏ */
        .badge-manager { background-color: #e0f2fe; color: #0ea5e9; } /* Nền xanh dương */
        .badge-customer { background-color: #f3f4f6; color: #4b5563; } /* Nền xám */
        .badge-staff { background-color: #d1fae5; color: #10b981; } /* Nền xanh lá */

        /* Actions */
        .actions { display: flex; gap: 15px; justify-content: flex-end; }
        .btn-icon { background: none; border: none; cursor: pointer; transition: transform 0.2s; }
        .btn-icon:hover { transform: scale(1.1); }
        .btn-edit { color: #0d6efd; }
        .btn-delete { color: #ef4444; }

        /* Modal / Popup */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal { background: #fff; width: 450px; border-radius: 12px; padding: 25px; transform: translateY(-20px); transition: transform 0.3s; }
        .modal-overlay.active .modal { transform: translateY(0); }
        .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; }
        
        .alert { padding: 12px; background-color: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 15px; font-size: 14px; font-weight: 500;}
        
        .form-control { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151; }
        .btn-submit { background-color: #0d6efd; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background-color: #fff; border: 1px solid #d1d5db; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo-area">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            DIENMAYPRO
        </div>
        
        <ul class="menu-list">
            <div class="menu-group">Quản lý Bán hàng</div>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg> Đơn hàng</li>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Y/c Bảo hành</li>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> Y/c Đổi Trả</li>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> Sản phẩm</li>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg> Trang chủ</li>

            <div class="menu-group">Phân loại</div>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> Danh mục</li>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> Thương hiệu</li>
            <li class="menu-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg> Mã giảm giá</li>

            <div class="menu-group">Hệ thống</div>
            <!-- Mục "Tài khoản" đang ở trạng thái Active -->
            <li class="menu-item active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Tài khoản
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <div class="user-info">
                    <div class="name">Quản Trị Viên</div>
                    <div class="role">ADMIN</div>
                </div>
            </div>
            <button class="btn-back-web" onclick="window.location.href='/'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Trở về Web
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="topbar">
            <!-- Text "Quản lý Tài Khoản" in đậm -->
            <div class="topbar-title">Quản lý Tài Khoản</div>
            
            <!-- Tìm kiếm theo Tên, Username hoặc Số điện thoại -->
            <form method="GET" action="" class="search-box">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </header>

        <div class="content-body">
            <?php if(!empty($message)): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <!-- Cập nhật động số lượng từ DB -->
                    <div class="total-count">Tổng cộng: <?php echo $total_users; ?> tài khoản</div>
                    
                    <button class="btn-add" onclick="openAddModal()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Thêm Tài Khoản
                    </button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Tên & Username</th>
                            <th>Số điện thoại</th>
                            <th>Cấp quyền (Role)</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <!-- Hiển thị dạng #ID -->
                            <td class="col-id">#<?php echo $u->user_id; ?></td>
                            
                            <td class="user-name-cell">
                                <div class="name"><?php echo htmlspecialchars($u->full_name); ?></div>
                                <div class="username"><?php echo htmlspecialchars($u->email); ?></div>
                            </td>
                            
                            <td><?php echo htmlspecialchars($u->status); ?></td>
                            
                            <!-- Phân loại màu Badge -->
                            <td>
                                <?php 
                                    $roleName = $u->role->name ?? 'N/A';
                                    $badgeClass = 'badge-customer';
                                    if ($roleName === 'Admin') $badgeClass = 'badge-admin';
                                    elseif ($roleName === 'Quản lý') $badgeClass = 'badge-manager';
                                    elseif ($roleName === 'Nhân viên') $badgeClass = 'badge-staff';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($roleName); ?>
                                </span>
                            </td>
                            
                            <td class="actions">
                                <!-- Nút Sửa (Bút xanh) -->
                                <button class="btn-icon btn-edit" title="Sửa" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </button>
                                
                                <!-- Nút Xóa (Thùng rác đỏ) - Ẩn ở Admin gốc ID #1 -->
                                <?php if ($u->user_id != 1): ?>
                                <button class="btn-icon btn-delete" title="Xóa" onclick="confirmDelete(<?php echo $u->user_id; ?>, '<?php echo htmlspecialchars($u->full_name); ?>')">
                                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                </button>
                                <?php else: ?>
                                <div style="width: 18px;"></div> <!-- Dành chỗ trống để các icon thẳng hàng -->
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if($total_users == 0): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: #9ca3af;">Không tìm thấy kết quả nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form Thêm/Sửa -->
    <div id="formModal" class="modal-overlay">
        <div class="modal">
            <h3 class="modal-title" id="modalTitle">Thêm Tài Khoản</h3>
            <form method="POST" action="">
                @csrf
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="formUserId" value="">
                
                <div class="form-group">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="full_name" id="fieldFullName" class="form-control" required placeholder="Nhập họ tên">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email (Username)</label>
                    <input type="email" name="email" id="fieldEmail" class="form-control" required placeholder="Nhập email">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mật khẩu <span id="pwdHint" style="font-weight: normal; font-size: 12px; color: #6b7280;">(Để trống nếu không đổi)</span></label>
                    <input type="password" name="password" id="fieldPassword" class="form-control" placeholder="Nhập mật khẩu">
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <div style="flex: 1;">
                        <label class="form-label">Vai trò</label>
                        <select name="role_id" id="fieldRoleId" class="form-control">
                            <?php foreach($roles as $r): ?>
                                <option value="<?php echo $r->role_id; ?>"><?php echo $r->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" id="fieldStatus" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Banned">Banned</option>
                        </select>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-submit" id="btnSubmitForm">Lưu tài khoản</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xóa -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal" style="width: 400px; text-align: center;">
            <svg style="width: 50px; height: 50px; color: #ef4444; margin: 0 auto 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 class="modal-title" style="margin-bottom: 10px;">Xác nhận xóa!</h3>
            <p style="color: #6b7280; font-size: 14px; margin-bottom: 25px;">Bạn có chắc chắn muốn xóa tài khoản <strong id="delUserName"></strong> không? Hành động này không thể hoàn tác.</p>
            
            <form method="POST" action="" style="display: flex; gap: 10px; justify-content: center;">
                @csrf
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="delUserId" value="">
                <button type="button" style="padding: 10px 20px; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; font-weight: 600; cursor: pointer;" onclick="closeModal()">Hủy bỏ</button>
                <button type="submit" style="padding: 10px 20px; border-radius: 6px; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">Đồng ý Xóa</button>
            </form>
        </div>
    </div>

    <script>
        // Các hàm xử lý Modal
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Thêm Tài Khoản';
            document.getElementById('formAction').value = 'add';
            document.getElementById('formUserId').value = '';
            document.getElementById('fieldFullName').value = '';
            document.getElementById('fieldEmail').value = '';
            document.getElementById('fieldPassword').required = true;
            document.getElementById('pwdHint').style.display = 'none';
            document.getElementById('formModal').classList.add('active');
        }

        function openEditModal(user) {
            document.getElementById('modalTitle').innerText = 'Cập nhật Tài Khoản';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('formUserId').value = user.user_id;
            document.getElementById('fieldFullName').value = user.full_name;
            document.getElementById('fieldEmail').value = user.email;
            document.getElementById('fieldPassword').required = false;
            document.getElementById('fieldPassword').value = '';
            document.getElementById('pwdHint').style.display = 'inline';
            document.getElementById('fieldRoleId').value = user.role_id;
            document.getElementById('fieldStatus').value = user.status;
            document.getElementById('formModal').classList.add('active');
        }

        function confirmDelete(id, name) {
            document.getElementById('delUserId').value = id;
            document.getElementById('delUserName').innerText = name;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeModal() {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.classList.remove('active');
            });
        }

        // Đóng modal khi bấm ra ngoài vùng tối
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>