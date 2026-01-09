<?php
require_once 'config.php';
require_once 'auth.php';

// Check if we're in admin section
$is_admin_page = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'TimberGuard - A comprehensive forestry management system for sustainable timber trading and illegal logging monitoring.'; ?>">
  <title><?php echo SITE_NAME; ?> - <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Forest Management System'; ?></title>

  <!-- Bootstrap CSS -->
  <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">

  <!-- Admin CSS (only for admin pages) -->
  <?php if ($is_admin_page): ?>
  <link href="<?php echo BASE_URL; ?>assets/css/admin.css" rel="stylesheet">
  <?php endif; ?>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

  <!-- Google Fonts - Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #2e7d32;
      --secondary-color: #388e3c;
      --accent-color: #4caf50;
      --light-color: #f1f8e9;
      --dark-color: #1b5e20;
      --navbar-height: 70px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* =====================================================
       Navigation Bar
       ===================================================== */
    .navbar {
      background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
      padding: 0;
      min-height: var(--navbar-height);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1030;
    }

    .navbar > .container {
      min-height: var(--navbar-height);
      display: flex;
      align-items: center;
    }

    .navbar-brand {
      font-size: 1.35rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0;
    }

    .navbar-brand,
    .nav-link {
      color: white !important;
      font-weight: 500;
    }

    .nav-link {
      padding: 0.5rem 0.85rem !important;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .nav-link:hover {
      background: rgba(255, 255, 255, 0.15);
    }

    .nav-link i {
      font-size: 0.85rem;
    }

    .navbar-toggler {
      border: 2px solid rgba(255, 255, 255, 0.3);
      padding: 0.4rem 0.6rem;
      border-radius: 8px;
    }

    .navbar-toggler:focus {
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
    }

    .dropdown-menu {
      border: none;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      border-radius: 12px;
      padding: 0.5rem;
      margin-top: 0.5rem;
    }

    .dropdown-item {
      padding: 0.6rem 1rem;
      border-radius: 8px;
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }

    .dropdown-item:hover {
      background-color: var(--light-color);
      color: var(--primary-color);
    }

    .dropdown-item i {
      width: 20px;
    }

    /* Mobile navbar */
    @media (max-width: 991.98px) {
      .navbar-collapse {
        background: rgba(0, 0, 0, 0.1);
        margin: 0.75rem -12px;
        padding: 1rem;
        border-radius: 12px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
      }

      .navbar-nav {
        gap: 0.25rem;
      }

      .nav-link {
        padding: 0.75rem 1rem !important;
      }

      .dropdown-menu {
        background: rgba(255, 255, 255, 0.08);
        box-shadow: none;
        padding: 0;
        margin: 0;
      }

      .dropdown-item {
        color: rgba(255, 255, 255, 0.9);
        padding: 0.6rem 1.5rem;
      }

      .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
      }

      .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.1);
      }
    }

    /* =====================================================
       Page Banner
       ===================================================== */
    .page-banner {
      background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.5)), url('<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg');
      background-size: cover;
      background-position: center;
      min-height: 180px;
      display: flex;
      align-items: center;
      color: white;
      margin-top: var(--navbar-height);
      padding: 1.5rem 0;
    }

    .page-banner h1 {
      color: white;
      font-weight: 700;
      font-size: 1.75rem;
      margin-bottom: 0.5rem;
    }

    .page-banner .breadcrumb {
      background: transparent;
      padding: 0;
      margin: 0;
    }

    .page-banner .breadcrumb-item + .breadcrumb-item::before {
      color: rgba(255, 255, 255, 0.5);
    }

    .page-banner .breadcrumb a {
      color: rgba(255, 255, 255, 0.75);
      text-decoration: none;
    }

    .page-banner .breadcrumb a:hover {
      color: white;
    }

    .page-banner .breadcrumb .active span {
      color: rgba(255, 255, 255, 0.9);
    }

    @media (min-width: 768px) {
      .page-banner {
        min-height: 200px;
        padding: 2rem 0;
      }

      .page-banner h1 {
        font-size: 2.25rem;
      }
    }

    /* =====================================================
       Main Content
       ===================================================== */
    .main-content {
      flex: 1;
      margin-top: var(--navbar-height);
    }

    .page-banner + .main-content {
      margin-top: 0;
    }

    /* =====================================================
       General Styles
       ===================================================== */
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
      border: none;
    }

    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--dark-color);
      border-color: var(--dark-color);
    }

    .section-title {
      position: relative;
      padding-bottom: 15px;
      margin-bottom: 30px;
    }

    .section-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      background-color: var(--accent-color);
    }

    .dashboard-stat {
      text-align: center;
      padding: 1.25rem;
      border-radius: 12px;
      color: white;
    }

    .dashboard-stat i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .available { background-color: #4caf50; }
    .sold { background-color: #f44336; }
    .report-pending { background-color: #ff9800; }
    .report-resolved { background-color: #2196f3; }

    /* Skip link for accessibility */
    .skip-link {
      position: absolute;
      top: -100px;
      left: 0;
      background: var(--primary-color);
      color: white;
      padding: 0.75rem 1.5rem;
      z-index: 9999;
      transition: top 0.3s;
      border-radius: 0 0 8px 0;
      text-decoration: none;
    }

    .skip-link:focus {
      top: 0;
      color: white;
    }
  </style>
</head>
<body>
  <!-- Skip to content link -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark" role="navigation" aria-label="Main navigation">
    <div class="container">
      <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php" aria-label="<?php echo SITE_NAME; ?> - Home">
        <i class="fas fa-tree" aria-hidden="true"></i>
        <span><?php echo SITE_NAME; ?></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home me-1" aria-hidden="true"></i>Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>reserve_list.php"><i class="fas fa-leaf me-1" aria-hidden="true"></i>Forest Reserves</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>about.php"><i class="fas fa-info-circle me-1" aria-hidden="true"></i>About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>contact.php"><i class="fas fa-envelope me-1" aria-hidden="true"></i>Contact</a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <?php if (is_logged_in()): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-1" aria-hidden="true"></i><?php echo htmlspecialchars($_SESSION['name']); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <?php if (has_role('admin') || has_role('forest manager')): ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/index.php"><i class="fas fa-tachometer-alt me-2" aria-hidden="true"></i>Admin Dashboard</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/index.php"><i class="fas fa-user-circle me-2" aria-hidden="true"></i>User Dashboard</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/profile.php"><i class="fas fa-user-edit me-2" aria-hidden="true"></i>Profile</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/transactions.php"><i class="fas fa-receipt me-2" aria-hidden="true"></i>Transactions</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>login.php"><i class="fas fa-sign-in-alt me-1" aria-hidden="true"></i>Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>register.php"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Banner (optional) -->
  <?php if (!isset($hide_banner) || !$hide_banner): ?>
  <section class="page-banner">
    <div class="container">
      <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'TimberGuard'; ?></h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
            <?php foreach ($breadcrumb as $item): ?>
              <li class="breadcrumb-item <?php echo isset($item['active']) && $item['active'] ? 'active' : ''; ?>" <?php echo isset($item['active']) && $item['active'] ? 'aria-current="page"' : ''; ?>>
                <?php if (isset($item['active']) && $item['active']): ?>
                  <span><?php echo htmlspecialchars($item['title']); ?></span>
                <?php else: ?>
                  <a href="<?php echo BASE_URL . $item['url']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="breadcrumb-item active" aria-current="page">
              <span><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Page'; ?></span>
            </li>
          <?php endif; ?>
        </ol>
      </nav>
    </div>
  </section>
  <?php endif; ?>

  <!-- Main Content -->
  <main id="main-content" class="main-content" role="main">
    <?php
    // Display any session messages
    if (isset($_SESSION['message'])) {
      $alert_type = $_SESSION['message_type'] ?? 'success';
      $icon = '';
      switch ($alert_type) {
        case 'success':
          $icon = '<i class="fas fa-check-circle me-2" aria-hidden="true"></i>';
          break;
        case 'error':
        case 'danger':
          $icon = '<i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>';
          $alert_type = 'danger';
          break;
        case 'warning':
          $icon = '<i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>';
          break;
        case 'info':
          $icon = '<i class="fas fa-info-circle me-2" aria-hidden="true"></i>';
          break;
      }
      echo '<div class="container mt-3"><div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">' . $icon . htmlspecialchars($_SESSION['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div></div>';
      unset($_SESSION['message']);
      unset($_SESSION['message_type']);
    }
    ?>