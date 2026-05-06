  
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav w-100"> <!-- Add w-100 to ensure the nav spans the full width -->
  <li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
  </li>
  <li class="nav-item d-none d-sm-inline-block">
    <a href="index3.html" class="nav-link">Home</a>
  </li>
  
  <!-- Centered Branch Name -->
  <?php if ($_SESSION['user']['role'] === 'system_admin'): ?>
      <h4 class="mx-auto mb-0 self-center">Warka Hub HRMS</h4>
  <?php else:?>
      <h4 class="mx-auto mb-0 self-center"><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? 'Organization') ?></h4>
  <?php endif; ?>
  
  <!-- Empty placeholder to balance the center -->
  <li class="nav-item d-none d-sm-inline-block" style="width: 100px;"></li> 
</ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-danger navbar-badge" id="total-count">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header" id="total-notifications"> </span>
           <?php if ($_SESSION['user']['role']==='hr_director'): ?>
          <div class="dropdown-divider"></div>
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-registration" class="dropdown-item" id="onboarding-item">
             <i class="fas fa-user-plus mr-2"></i> ያልጸደቀ የሰራተኞች ምዝገባ
            <span class="float-right badge badge-danger" id="onboarding-count">0</span>
          </a>
          
          <div class="dropdown-divider"></div>
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave" class="dropdown-item" id="scholarship-item">
             <i class="fas fa-graduation-cap mr-2"></i> ያልጸደቀ የትምህርት እድል
            <span class="float-right badge badge-danger" id="scholarship-count">0</span>
          </a>
            
          <div class="dropdown-divider"></div>
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-pending" class="dropdown-item" id="debt-suspension-item">
             <i class="fas fa-dollar-sign mr-2"></i> ያልጸደቀ እዳ/እገዳ
            <span class="float-right badge badge-danger" id="debt-suspension-count">0</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-deletion-requests" class="dropdown-item" id="employee-deletion-request-item">
             <i class="fas fa-dollar-sign mr-2"></i> ያልጸደቀ ሰራተኛ መረጃ መሰረዝ
            <span class="float-right badge badge-danger" id="employee-deletion-request-count">0</span>
          </a>
            <?php endif; ?>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <!-- User Dropdown with Logout -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-user-circle fa-lg"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <a href="#" class="dropdown-item dropdown-header">
            <i class="fas fa-user mr-2"></i>
            <?php
            $display_role = '';
if ($_SESSION['user']['role'] === 'system_admin') {
    $display_role = 'System Admin';
} elseif ($_SESSION['user']['role'] === 'hr_director') {
    $display_role = 'HR Director';
} elseif ($_SESSION['user']['role'] === 'hr_officer') {
    $display_role = 'HR Officer';
} elseif ($_SESSION['user']['role'] === 'org_admin') {
    $display_role = 'Admin';
} else {
    header('Location: ' . rtrim($_ENV['BASE_URL'], '/') . '/login');
    exit;
}

$full_name = ($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['father_name'] ?? '');
$display_name = trim($full_name) ?: 'Guest';

echo mb_convert_case($display_name, MB_CASE_TITLE, "UTF-8") . ' (' . $display_role . ')';
            ?>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" 
             href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/login"
             onclick="return confirm('እርግጠኛ ነዎት? ከስርዓቱ መውጣት ይፈልጋሉ?')">
            <i class="fas fa-sign-out-alt mr-2"></i> ውጣ
          </a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
      
    </ul>
  </nav>
  <!-- /.navbar -->