    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar overlay close on mobile
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('adminSidebar');
    if (sidebar && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !e.target.closest('.sidebar-toggle')) {
            sidebar.classList.remove('open');
        }
    }
});

// Auto-hide alerts
document.querySelectorAll('.alert-dismissible').forEach(alert => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        if (bsAlert) bsAlert.close();
    }, 5000);
});
</script>
</body>
</html>
