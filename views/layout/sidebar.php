  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">HRMS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo $_SESSION['user']['first_name'].' '. $_SESSION['user']['father_name'] ?? 'Guest'; ?></a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item menu-open">
            <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/dashboard" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

          </li>
          <li class="nav-item">
            <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-developer" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                register-developer
                <span class="right badge badge-danger">New</span>
              </p>
            </a>
          </li>
          
         
         <?php 
        $userRole = $_SESSION['user']['role'] ?? null; 

        // 1. Only show the "Registration" menu if the user has one of these three roles
          if (in_array($userRole, ['system_admin', 'org_admin', 'hr_director', 'hr_officer'])): 
          ?>
     <li class="nav-item">
    <a href="#" class="nav-link">
      <i class="nav-icon fas fa-edit"></i>
      <p>
        መመዝገብ
        <i class="fas fa-angle-left right"></i>
      </p>
    </a>
    <ul class="nav nav-treeview">
       <?php if ($userRole === 'system_admin' || $userRole === 'org_admin'): ?>
      <?php if ($userRole === 'system_admin'): ?>
        <li class="nav-item">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-organization" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>ድርጅት</p>
          </a>
        </li>
      <?php elseif ($userRole === 'org_admin'): ?>
        <li class="nav-item">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-branch" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>ቅርንጫፍ</p>
          </a>
        </li>
      <?php endif; ?>
         <li class="nav-item">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-user" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>ተቆጣጣሪ</p>
          </a>
        </li>
        <?php endif; ?>
      <?php if ($userRole === 'hr_director'): ?>
              <li class="nav-item">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-director" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>ዲይሬክተር</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-position" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>መደብ መመዝገብ</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-registration" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>ሰራተኛ</p>
          </a>
        </li>
      <?php endif; ?>

    </ul>
  </li>
<?php endif; ?>
         

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>