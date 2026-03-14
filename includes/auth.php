<?php

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/index.php?page=login');
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles)) {
        flash('danger', 'Access denied.');
        redirect(BASE_URL . '/index.php?page=dashboard');
    }
}

function currentUser(): array {
    return [
        'id'          => $_SESSION['user_id']   ?? 0,
        'name'        => $_SESSION['user_name']  ?? '',
        'email'       => $_SESSION['user_email'] ?? '',
        'role'        => $_SESSION['role']       ?? '',
        'branch_id'   => $_SESSION['branch_id']  ?? null,
        'branch_name' => $_SESSION['branch_name'] ?? 'All Branches',
    ];
}

/**
 * Returns the branch_id restriction for the current user.
 * Super admins return null (no restriction).
 */
function getBranchScope(): ?int {
    if (($_SESSION['role'] ?? '') === 'superadmin') {
        return null;
    }
    return $_SESSION['branch_id'] ? (int)$_SESSION['branch_id'] : null;
}

/**
 * Check whether the current user can access data from the given branch.
 */
function canAccessBranch(?int $branchId): bool {
    if (($_SESSION['role'] ?? '') === 'superadmin') {
        return true;
    }
    return (int)($_SESSION['branch_id'] ?? 0) === (int)$branchId;
}
