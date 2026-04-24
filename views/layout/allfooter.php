    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php include_once 'footer.php'; ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- bs-custom-file-input -->
<script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->

<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<!-- AdminLTE for demo purposes -->
 <script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- AdminLTE App -->

<!-- Page specific script -->
 <?php if (isset($is_dashboard) && $is_dashboard === true): ?>
  <!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
   <script src="dist/js/pages/dashboard.js"></script>
<?php endif; ?>
<?php if (isset($is_organization_page) && $is_organization_page === true): ?>
    
    <script src="js/organizations-logic.js"></script>
    <?php endif; ?>
   <?php if (isset($is_register_user_page) && $is_register_user_page === true): ?>
    <script>const BASE_URL = "<?= '/HRM' ?>"; // or use $_ENV['BASE_URL'] if you have .env</script>
    <script src="js/edit-user.js"></script>
    <script>
    const BRANCH_NAME    = <?= json_encode($branchNameString) ?>;
    const ORGANIZATIONS  = <?= json_encode($organizations) ?>;
</script>
<script src="js/register-user.js"></script>
    <?php endif; ?>
   <?php if (isset($is_employee_registration_page) && $is_employee_registration_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/employee-registration.js"></script>
     <script src="js/ethiopian-calendar.js"></script>
    
  
    <?php endif; ?>
   <?php if (isset($is_employee_edit_page) && $is_employee_edit_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/employee-edit.js"></script>
    <script src="js/ethiopian-calendar.js"></script>
    
    <?php endif; ?>
     <?php if (isset($is_employee_scholarship_page) && $is_employee_scholarship_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/ethiopian-calendar.js"></script>
    <script src="js/employee-scholarship.js"></script>  
    <?php endif; ?>
<?php if (isset($is_employee_scholarship_edit_page) && $is_employee_scholarship_edit_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/ethiopian-calendar.js"></script>
    <script src="js/employee-scholarship.js"></script>
    
    <?php endif; ?>
   
    <?php if (isset($is_employee_debt_suspension_page) && $is_employee_debt_suspension_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/ethiopian-calendar.js"></script>
    <script src="js/employee-debt-suspenssion.js"></script>
    
    <?php endif; ?>
    <?php if (isset($is_employee_debt_suspension_clearing_page) && $is_employee_debt_suspension_clearing_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/ethiopian-calendar.js"></script>
    <script src="js/employee-debt-suspenssion.js"></script>
    
    <?php endif; ?>
<?php if (isset($is_employee_debt_suspension_edit_page) && $is_employee_debt_suspension_edit_page === true): ?>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="js/ethiopian-calendar.js"></script>
     <script src="js/employee-debt-suspenssion.js"></script>
    <?php endif; ?>

    <?php if ($_SESSION['user']['role']==='hr_director'): ?>
    <script>const BASE_URL = "<?= '/HRM' ?>"; // or use $_ENV['BASE_URL'] if you have .env</script>
   <script>
    const NOTIFICATION_URLS = {
        onboarding: BASE_URL + '/onBoardingEmployees',
        scholarship: BASE_URL + '/on-leave-scholarship-count',
        debtsuspension: BASE_URL + '/debt-suspension-count'
    };
    
</script>
<script src="js/all-userdefined-notifications.js"></script>
    <?php endif; ?>
<script>
$(function () {
  bsCustomFileInput.init();
});
</script>
<script>
  $(function () {
    function resetSubmitButtons($form) {
      $form.data('submitting', false);
      $form.removeData('submitButton');
      $form.find('button[type="submit"], input[type="submit"]').prop('disabled', false);
    }

    $(document).on('click', 'form button[type="submit"], form input[type="submit"]', function() {
      var $button = $(this);
      var $form = $button.closest('form');
      if ($form.data('submitting')) {
        return;
      }
      $form.data('submitButton', $button);
    });

    $(document).on('submit', 'form', function(event) {
      var $form = $(this);
      if ($form.data('submitting')) {
        event.preventDefault();
        return false;
      }

      $form.data('submitting', true);
      $form.find('button[type="submit"], input[type="submit"]').prop('disabled', true);
    });

    $(document).on('invalid-form.validate invalid', 'form', function() {
      resetSubmitButtons($(this));
    });
  });
</script>
</body>
</html>

<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>
<script>
  $(function() {
    var Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });

    <?php if (isset($_SESSION['success'])): ?>
      Toast.fire({
        icon: 'success',
        title: '<?php echo $_SESSION['success']; ?>'
      });
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      Toast.fire({
        icon: 'error',
        title: '<?php echo $_SESSION['error']; ?>'
      });
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
  });
</script>