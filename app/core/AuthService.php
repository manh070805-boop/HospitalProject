<?php

require_once __DIR__ . '/PortalAccess.php';

class AuthService
{
    private $userResolver;

    public function __construct(callable $userResolver)
    {
        $this->userResolver = $userResolver;
    }

    public function attemptLogin(string $username, string $password, string $selectedRole): array
    {
        $errors = [];
        $username = trim($username);
        $selectedRole = PortalAccess::normalizeSelection($selectedRole);

        $user = call_user_func($this->userResolver, $username);
        if (!$user || empty($user['password_hash'])) {
            $errors[] = 'Tên đăng nhập, mật khẩu hoặc vai trò không hợp lệ.';
            return ['errors' => $errors, 'user' => null];
        }

        if (($user['status'] ?? '') !== 'active') {
            $errors[] = 'Tài khoản hiện không thể đăng nhập.';
            return ['errors' => $errors, 'user' => null];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $errors[] = 'Tên đăng nhập, mật khẩu hoặc vai trò không hợp lệ.';
            return ['errors' => $errors, 'user' => null];
        }

        if (!PortalAccess::roleMatchesSelection($user['role_name'] ?? '', $selectedRole)) {
            $errors[] = 'Tên đăng nhập, mật khẩu hoặc vai trò không hợp lệ.';
            return ['errors' => $errors, 'user' => null];
        }

        $user['role_key'] = PortalAccess::normalizeRoleName($user['role_name'] ?? '');

        return [
            'errors' => [],
            'user' => $user,
        ];
    }
}

