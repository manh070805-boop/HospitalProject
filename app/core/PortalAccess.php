<?php

class PortalAccess
{
    private const SESSION_KEY = 'auth_user';

    public static function normalizeRoleName(?string $roleName): string
    {
        $roleName = trim((string) $roleName);
        if ($roleName === '') {
            return '';
        }

        $lower = mb_strtolower($roleName, 'UTF-8');
        $ascii = self::normalizeAscii($lower);

        if (
            self::containsAny($lower, ['điều dưỡng trưởng'])
            || self::containsAny($ascii, ['head nurse', 'head_nurse', 'dieu duong truong'])
        ) {
            return 'head_nurse';
        }

        if (
            self::containsAny($lower, ['điều dưỡng', 'y tá'])
            || self::containsAny($ascii, ['nurse', 'y ta', 'dieu duong'])
        ) {
            return 'nurse';
        }

        if (
            self::containsAny($lower, ['bác sĩ'])
            || self::containsAny($ascii, ['doctor', 'bac si'])
        ) {
            return 'doctor';
        }

        if (
            self::containsAny($lower, ['quản trị viên', 'quản trị'])
            || self::containsAny($ascii, ['admin', 'administrator', 'quan tri vien', 'quan tri'])
        ) {
            return 'admin';
        }

        return str_replace(' ', '_', $ascii);
    }

    public static function normalizeSelection(?string $selectedRole): string
    {
        $selectedRole = strtolower(trim((string) $selectedRole));
        return in_array($selectedRole, ['admin', 'doctor', 'nurse'], true) ? $selectedRole : '';
    }

    public static function roleMatchesSelection(?string $roleName, ?string $selectedRole): bool
    {
        $normalizedRole = self::normalizeRoleName($roleName);
        $selectedRole = self::normalizeSelection($selectedRole);

        if ($normalizedRole === '' || $selectedRole === '') {
            return false;
        }

        if ($selectedRole === 'nurse') {
            return in_array($normalizedRole, ['nurse', 'head_nurse'], true);
        }

        return $normalizedRole === $selectedRole;
    }

    public static function getHomeUrlForRole(?string $roleName): string
    {
        $normalizedRole = self::normalizeRoleName($roleName);

        if (in_array($normalizedRole, ['nurse', 'head_nurse'], true)) {
            return 'index.php?url=internal';
        }

        if ($normalizedRole === 'doctor') {
            return 'index.php?url=doctor-portal';
        }

        if ($normalizedRole === 'admin') {
            return 'index.php?url=dashboard';
        }

        return 'index.php?url=auth/login';
    }

    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'user_id' => (int) ($user['user_id'] ?? 0),
            'username' => $user['username'] ?? '',
            'full_name' => $user['full_name'] ?? '',
            'role_id' => isset($user['role_id']) ? (int) $user['role_id'] : null,
            'role_name' => $user['role_name'] ?? '',
            'role_key' => self::normalizeRoleName($user['role_name'] ?? ''),
            'department_id' => isset($user['department_id']) ? (int) $user['department_id'] : null,
            'department_name' => $user['department_name'] ?? '',
            'status' => $user['status'] ?? '',
        ];
    }
    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function logoutUser(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function isAuthenticated(): bool
    {
        $user = self::user();
        return !empty($user['user_id']);
    }

    public static function currentRole(): string
    {
        $user = self::user();
        return $user['role_key'] ?? '';
    }

    public static function canAccess(array $allowedRoles = []): bool
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        $currentRole = self::currentRole();

        if (empty($allowedRoles)) {
            return true;
        }

        $normalizedAllowedRoles = array_map([self::class, 'normalizeRoleName'], $allowedRoles);

        if ($currentRole === 'head_nurse' && in_array('nurse', $normalizedAllowedRoles, true)) {
            return true;
        }

        return in_array($currentRole, $normalizedAllowedRoles, true);
    }

    public static function requireAuth(array $allowedRoles = []): void
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php?url=auth/login');
            exit;
        }

        if (!self::canAccess($allowedRoles)) {
            header('Location: ' . self::getHomeUrlForRole(self::currentRole()));
            exit;
        }
    }

    private static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeAscii(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii === false) {
            $ascii = $value;
        }

        $ascii = strtolower($ascii);
        $ascii = preg_replace('/[^a-z0-9\s_]+/', ' ', $ascii) ?? $ascii;
        $ascii = preg_replace('/\s+/', ' ', $ascii) ?? $ascii;

        return trim($ascii);
    }
}
