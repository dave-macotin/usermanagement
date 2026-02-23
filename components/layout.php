<?php
// components/layout.php

function renderHead(string $title): void {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - Macotin UMS</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
            width: 220px;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            font-size: 14px;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: white;
        }
        .sidebar a.active {
            background-color: #495057;
            color: white;
            border-left: 3px solid #0d6efd;
        }
        .sidebar .sidebar-heading {
            color: #6c757d;
            font-size: 11px;
            text-transform: uppercase;
            padding: 15px 20px 5px;
            letter-spacing: 1px;
        }
        .main-content {
            flex: 1;
            padding: 25px;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 18px;
        }
        .page-title {
            font-size: 22px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 5px;
        }
        .page-subtitle {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 20px;
        }
        table thead th {
            background-color: #343a40;
            color: white;
            font-size: 13px;
        }
        .badge-admin { background-color: #198754 !important; }
        .badge-user  { background-color: #0d6efd !important; }
    </style>
</head>
<body>
HTML;
}

function renderTopBar(string $username, string $role): void {
    $logoutUrl = '../public/logout.php';
    $roleLabel = ucfirst($role);
    echo <<<HTML
<nav class="navbar navbar-dark bg-dark px-3" style="height:56px;">
    <span class="navbar-brand"><i class="bi bi-people-fill me-2"></i>Macotin UMS</span>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white-50" style="font-size:13px;">
            <i class="bi bi-person-circle me-1"></i>{$username}
            <span class="badge bg-secondary ms-1">{$roleLabel}</span>
        </span>
        <a href="{$logoutUrl}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</nav>
HTML;
}

function renderSidebar(string $active, string $role): void {
    $pages = $role === 'admin'
        ? [
            ['href' => 'admin_users.php',       'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
            ['href' => 'admin_user_create.php', 'icon' => 'bi-person-plus',  'label' => 'Add User'],
            ['href' => 'profile.php',           'icon' => 'bi-person',       'label' => 'My Profile'],
          ]
        : [
            ['href' => 'profile.php',           'icon' => 'bi-person',       'label' => 'My Profile'],
            ['href' => 'change_password.php',   'icon' => 'bi-lock',         'label' => 'Change Password'],
          ];

    echo '<div class="sidebar">';
    echo '<div class="sidebar-heading">Menu</div>';
    foreach ($pages as $page) {
        $cls = (basename($page['href']) === $active) ? ' active' : '';
        echo "<a href='{$page['href']}' class='{$cls}'><i class='bi {$page['icon']} me-2'></i>{$page['label']}</a>";
    }
    echo '</div>';
}

function renderFlash(): void {
    require_once __DIR__ . '/auth.php';
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'danger');
        $msg  = e($flash['message']);
        $icon = $type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                <i class='bi {$icon} me-2'></i>{$msg}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

function renderScripts(): void {
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
    echo '<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>';
}
