<!-- Put this once in your main layout <head> -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  document.querySelectorAll('.delete-btn').forEach((button) => {
    button.addEventListener('click', function () {

      const staffId = this.dataset.id; // data-id="..."
      const row = this.closest('tr');  // remove row after delete

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
      }).then((result) => {
        if (!result.isConfirmed) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Staff was not deleted.',
            icon: 'info',
            timer: 1200,
            showConfirmButton: false
          });
          return;
        }

        Swal.fire({
          title: 'Deleting...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch(`/pages/staff-delete/${staffId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
        .then(async (res) => {
          // if server returns HTML error, this prevents crash
          const data = await res.json().catch(() => ({}));
          if (!res.ok) {
            throw new Error(data.message || 'Delete failed.');
          }
          return data;
        })
        .then((data) => {
          if (data.success) {
            Swal.fire({
              title: 'Deleted!',
              text: 'Staff has been deleted successfully.',
              icon: 'success',
              timer: 1400,
              showConfirmButton: false
            });

            // ✅ remove row without reload
            if (row) row.remove();

            // OPTIONAL: if you prefer reload
            // setTimeout(() => location.reload(), 800);

          } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
          }
        })
        .catch((err) => {
          Swal.fire('Error', err.message || 'Server error. Please try again.', 'error');
        });

      });
    });
  });

});
</script>
