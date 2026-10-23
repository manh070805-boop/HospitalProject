<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — Hệ thống quản lý bệnh viện</title>
    <meta name="description" content="Đăng nhập vào hệ thống quản lý bệnh viện. Dành cho Admin, Bác sĩ và Y tá.">
    <link rel="stylesheet" href="/DACN/HospitalProject/public/assets/css/login.css">
    <link rel="stylesheet" href="/DACN/HospitalProject/public/assets/css/iconsax.css">
</head>

<body>
    <?php
    $old = $old ?? [];
    $message = $message ?? '';
    $messageType = $messageType ?? 'error';
    $selectedRole = $old['role'] ?? 'admin';
    $oldUsername = $old['username'] ?? '';
    ?>
    <div class="login-bg">
        <div class="login-bg-dot"></div>
    </div>
    <div class="login-wrapper">
        <div class="login-left">
            <div class="login-brand reveal-item">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z" />
                    </svg>
                </div>
                <span class="brand-name">Med<span>Care</span> System</span>
            </div>
            <h1 class="login-headline reveal-item delay-1">
                Quản lý bệnh viện<br>
                <em>thông minh & hiệu quả</em>
            </h1>

            <p class="login-sub reveal-item delay-2">
                Nền tảng quản trị toàn diện giúp bệnh viện vận hành trơn tru — từ hồ sơ bệnh nhân, quản lý giường bệnh
                đến lịch làm việc bác sĩ.
            </p>
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <div class="feature-text">
                        <strong>Quản lý bệnh nhân toàn diện</strong>
                        <span>Hồ sơ điện tử, lịch sử khám chữa bệnh</span>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                            <line x1="8" y1="21" x2="8" y2="17" />
                            <line x1="16" y1="21" x2="16" y2="17" />
                            <line x1="2" y1="17" x2="22" y2="17" />
                        </svg>
                    </div>
                    <div class="feature-text">
                        <strong>Quản lý phòng & giường bệnh</strong>
                        <span>Theo dõi tình trạng thời gian thực</span>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <div class="feature-text">
                        <strong>Bảo mật đa tầng</strong>
                        <span>Phân quyền theo vai trò, audit log đầy đủ</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-right">
            <div class="login-card">
                <div class="login-card-header reveal-item">
                    <div class="tag">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        Đăng nhập bảo mật
                    </div>
                    <h2>Chào mừng trở lại!</h2>
                    <p>Vui lòng nhập thông tin đăng nhập để vào hệ thống quản lý bệnh viện.</p>
                </div>

                <div class="reveal-item delay-1">
                    <div class="role-chips" id="roleChips">

                        <button class="role-chip <?= $selectedRole === 'admin' ? 'active' : '' ?>" type="button"
                            id="chip-admin" data-role-chip data-role="admin">
                            <div class="role-chip-icon is-admin">
                                <svg viewBox="0 0 24 24" stroke="#2563eb">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M20 21a8 8 0 1 0-16 0" />
                                    <path d="M15 11l2 2 4-4" />
                                </svg>
                            </div>
                            <span>Admin</span>
                        </button>

                        <button class="role-chip <?= $selectedRole === 'doctor' ? 'active' : '' ?>" type="button"
                            id="chip-doctor" data-role-chip data-role="doctor">
                            <div class="role-chip-icon is-doctor">
                                <svg viewBox="0 0 24 24" stroke="#10b981">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                            </div>
                            <span>Bác sĩ</span>
                        </button>

                        <button class="role-chip <?= $selectedRole === 'nurse' ? 'active' : '' ?>" type="button"
                            id="chip-nurse" data-role-chip data-role="nurse">
                            <div class="role-chip-icon is-nurse">
                                <svg viewBox="0 0 24 24" stroke="#f59e0b">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    <line x1="12" y1="8" x2="12" y2="16" />
                                    <line x1="8" y1="12" x2="16" y2="12" />
                                </svg>
                            </div>
                            <span>Y tá</span>
                        </button>

                    </div>
                </div>

                <form action="index.php?url=auth/login" method="POST" id="loginForm" novalidate>
                    <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($selectedRole) ?>">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error reveal-item">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="alert-content">
                                <?php if (count((array) $error) > 1): ?>
                                    <ul style="margin: 0; padding-left: 1.2rem;">
                                        <?php foreach ((array) $error as $er): ?>
                                            <li><?= htmlspecialchars($er) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <?= htmlspecialchars($error[0]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($message !== ''): ?>
                        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?> reveal-item">
                            <?php if ($messageType === 'success'): ?>
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            <?php else: ?>
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="15" y1="9" x2="9" y2="15" />
                                    <line x1="9" y1="9" x2="15" y2="15" />
                                </svg>
                            <?php endif; ?>
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group reveal-item delay-2">
                        <div class="form-label">
                            <label for="username">Tên đăng nhập</label>
                        </div>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <input type="text" id="username" name="username" class="form-control"
                                placeholder="Nhập tên đăng nhập..." autocomplete="username"
                                value="<?= htmlspecialchars($oldUsername) ?>" required>
                        </div>
                    </div>
                    <div class="form-group reveal-item delay-3">
                        <div class="form-label">
                            <label for="password">Mật khẩu</label>
                        </div>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-control has-toggle"
                                placeholder="Nhập mật khẩu..." autocomplete="current-password" required>
                            <button type="button" class="input-toggle" id="toggleBtn" data-toggle-password
                                title="Hiện/Ẩn mật khẩu">
                                <svg id="eyeIcon" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-login reveal-item delay-4" id="loginBtn">
                        <svg viewBox="0 0 24 24">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" y1="12" x2="3" y2="12" />
                        </svg>
                        Đăng nhập hệ thống
                    </button>

                </form>

                <div class="login-footer reveal-item delay-5">
                    <p>
                        Bằng cách đăng nhập, bạn đồng ý với<br>
                        <strong>Chính sách bảo mật</strong> &amp; <strong>Điều khoản sử dụng</strong> của hệ
                        thống.<br><br>
                        Hệ thống Quản lý Bệnh viện © <?= date('Y') ?> — v2.0
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/login.js?v=<?= time(); ?>"></script>

</body>

</html>