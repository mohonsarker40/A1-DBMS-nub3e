</main> <!-- Close Main Content -->
</div> <!-- Close App Wrapper -->

<!-- Footer -->
<footer class="mt-auto py-3 text-center border-top border-secondary border-opacity-10 text-secondary small">
    &copy; <?= date('Y') ?> HRMS Portal. All Rights Reserved.
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile Sidebar Toggle Script
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if(sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }
</script>
</body>
</html>