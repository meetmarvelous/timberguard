<?php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?php echo SITE_NAME; ?> - <?php echo isset($page_title) ? $page_title : 'Forest Management System'; ?></title>

  <!-- Bootstrap CSS -->
  <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    :root {
      --primary-color: #2e7d32;
      --secondary-color: #388e3c;
      --accent-color: #4caf50;
      --light-color: #f1f8e9;
      --dark-color: #1b5e20;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }

    .navbar {
      background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand,
    .nav-link {
      color: white !important;
      font-weight: 500;
    }

    .nav-link:hover {
      color: #e0f7fa !important;
    }

    .hero-section {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg');
      background-size: cover;
      background-position: center;
      height: 70vh;
      display: flex;
      align-items: center;
      color: white;
    }

    .card {
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
      margin-bottom: 20px;
    }

    .card:hover {
      transform: translateY(-5px);
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

    .tree-card {
      border-left: 4px solid var(--accent-color);
    }

    .dashboard-stat {
      text-align: center;
      padding: 20px;
      border-radius: 8px;
      color: white;
      margin-bottom: 20px;
    }

    .dashboard-stat i {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    .available {
      background-color: #4caf50;
    }

    .sold {
      background-color: #f44336;
    }

    .report-pending {
      background-color: #ff9800;
    }

    .report-resolved {
      background-color: #2196f3;
    }

    /* Fix for modal blinking issue */
    .modal {
      z-index: 1050 !important;
    }

    .modal-backdrop {
      z-index: 1040 !important;
    }

    .fixed-top {
      z-index: 1030 !important;
    }

    body.modal-open {
      overflow: auto !important;
      padding-right: 0 !important;
    }

    /* Ensure modals are properly positioned */
    .modal.fade .modal-dialog {
      transform: translate(0, -50px);
      transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
      transform: translate(0, 0);
    }

    /* Prevent multiple scrollbars */
    .modal-open {
      overflow: auto !important;
    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
        <i class="fas fa-tree me-2"></i><?php echo SITE_NAME; ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home me-1"></i>Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>reserve_list.php"><i class="fas fa-leaf me-1"></i>Forest Reserves</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>about.php"><i class="fas fa-info-circle me-1"></i>About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>contact.php"><i class="fas fa-envelope me-1"></i>Contact</a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <?php if (is_logged_in()): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user me-1"></i><?php echo $_SESSION['name']; ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <?php if (has_role('admin') || has_role('forest manager')): ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/index.php"><i class="fas fa-cog me-2"></i>Admin Dashboard</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/index.php"><i class="fas fa-user-circle me-2"></i>User Dashboard</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/transactions.php"><i class="fas fa-receipt me-2"></i>Transactions</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>register.php"><i class="fas fa-user-plus me-1"></i>Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Banner -->
  <?php if (!isset($hide_banner) || !$hide_banner): ?>
    <section class="page-banner" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg'); background-size: cover; height: 300px; display: flex; align-items: center; color: white;">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <h1><?php echo isset($page_title) ? $page_title : 'TimberGuard'; ?></h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php" style="color: white;">Home</a></li>
                <?php if (isset($breadcrumb)): ?>
                  <?php foreach ($breadcrumb as $item): ?>
                    <li class="breadcrumb-item <?php echo $item['active'] ? 'active' : ''; ?>" aria-current="<?php echo $item['active'] ? 'page' : 'false'; ?>">
                      <?php if ($item['active']): ?>
                        <?php echo $item['title']; ?>
                      <?php else: ?>
                        <a href="<?php echo BASE_URL . $item['url']; ?>" style="color: white;"><?php echo $item['title']; ?></a>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li class="breadcrumb-item active" aria-current="page">
                    <?php echo isset($page_title) ? $page_title : 'Page'; ?>
                  </li>
                <?php endif; ?>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Main Content -->
  <main class="container my-5" style="margin-top: 20px;">
    <?php
    // Display any session messages
    if (isset($_SESSION['message'])) {
      echo show_alert($_SESSION['message'], $_SESSION['message_type'] ?? 'success');
      unset($_SESSION['message']);
      unset($_SESSION['message_type']);
    }
    ?>