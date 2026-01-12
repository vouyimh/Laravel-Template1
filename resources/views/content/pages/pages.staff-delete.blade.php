<!-- Make sure you have CSRF token in your head -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll('.delete-btn').forEach(button => {
  button.addEventListener('click', function() {
    const staffId = this.dataset.id; // button should have data-id="{{ $s->StaffID }}"

    Swal.fire({
      title: 'Are you sure?',
      text: "Do you really want to delete this staff?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // Send DELETE request via fetch
        fetch(`/pages/staff-delete/${staffId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Deleted!', 'Staff has been deleted.', 'success')
              .then(() => location.reload()); // reload page
          } else {
            Swal.fire('Error!', 'Something went wrong.', 'error');
          }
        })
        .catch(() => Swal.fire('Error!', 'Something went wrong.', 'error'));
      }
    });
  });
});
</script>
