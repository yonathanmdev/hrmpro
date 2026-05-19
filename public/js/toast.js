// assets/js/toast.js
$(function () {
  var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
  });

  if (window.__flash.success) {
    Toast.fire({ icon: 'success', title: window.__flash.success });
  }

  if (window.__flash.error) {
    Toast.fire({ icon: 'error', title: window.__flash.error });
  }
});