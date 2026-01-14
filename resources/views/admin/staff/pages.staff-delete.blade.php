<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.delete-btn');
  if (!btn) return; // not a delete button

  const staffId = btn.dataset.id;
  const row = btn.closest('tr');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  if (!staffId) {
    Swal.fire('Error', 'Missing staff id.', 'error');
    return;
  }

  Swal.fire({
    title: 'Delete staff?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel'
  }).then(async (result) => {
    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Deleting...',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const res = await fetch(`/pages/staff-delete/${staffId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.success) throw new Error(data.message || 'Delete failed.');

      // ✅ If this page uses DataTables, remove row using DataTables API
      if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#staff-table')) {
        $('#staff-table').DataTable().row(row).remove().draw();
      } else {
        // normal table
        row?.remove();
      }

      Swal.fire({
        title: 'Deleted!',
        text: 'Staff has been deleted successfully.',
        icon: 'success',
        timer: 1200,
        showConfirmButton: false
      });

    } catch (err) {
      Swal.fire('Error', err.message || 'Server error. Please try again.', 'error');
    }
  });
});
</script>
