<?php

require_once __DIR__ . '/../models/Admin/UserModel.php';
require_once __DIR__ . '/../core/AuditLogger.php';
require_once __DIR__ . '/../core/AuthService.php';
require_once __DIR__ . '/../core/PortalAccess.php';

class AuthController
{
    private $userModel;
    private $authService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->authService = new AuthService(function (string $username): ?array {
            return $this->userModel->getUserForAuthByUsername($username) ?: null;
        });
    }

    public function showLoginForm(): void
    {
        if (PortalAccess::isAuthenticated()) {
            header('Location: ' . PortalAccess::getHomeUrlForRole(PortalAccess::currentRole()));
            exit;
        }

        $error = [];
        $message = '';
        $messageType = 'error';
        $old = [
            'role' => PortalAccess::normalizeSelection($_GET['role'] ?? 'admin') ?: 'admin',
        ];

        if (isset($_GET['logged_out'])) {
            $message = 'Bạn đã đăng xuất khỏi hệ thống.';
            $messageType = 'success';
        }

        $this->renderLogin($old, $error, $message, $messageType);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginForm();
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? '');

        $error = [];

        if ($username === '') {
            $error[] = 'Tên đăng nhập không được để trống';
        }
        if ($password === '') {
            $error[] = 'Mật khẩu không được để trống';
        }

        if (empty($error)) {
            $result = $this->authService->attemptLogin($username, $password, $role);

            if (!empty($result['errors'])) {
                $error = $result['errors'];
            } else {
                $user = $result['user'];
                PortalAccess::loginUser($user);

                AuditLogger::log(
                    $user['user_id'] ?? null,
                    'LOGIN',
                    'AUTH',
                    'Đăng nhập hệ thống vai trò: ' . ($user['role_name'] ?? $user['role_key'])
                );

                header('Location: ' . PortalAccess::getHomeUrlForRole($user['role_key'] ?? ''));
                exit;
            }
        }

        $this->renderLogin(
            [
                'username' => $username,
                'role' => PortalAccess::normalizeSelection($role) ?: 'admin',
            ],
            $error
        );
    }

    public function logout(): void
    {
        $currentUser = PortalAccess::user();
        if ($currentUser) {
            AuditLogger::log(
                $currentUser['user_id'] ?? null,
                'LOGOUT',
                'AUTH',
                'Đăng xuất khỏi hệ thống'
            );
        }

        PortalAccess::logoutUser();
        header('Location: index.php?url=auth/login&logged_out=1');
        exit;
    }

    private function renderLogin(array $old = [], array $error = [], string $message = '', string $messageType = 'error'): void
    {
        require __DIR__ . '/../views/Auth/login.php';
    }

}
