    </main>
    <footer class="site-footer">
        <p>Bản quyền © <?= date('Y') ?> - <?= h(APP_NAME) ?></p>
    </footer>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('a[href*="action=delete"]').forEach(function (link) {
            if (link.hasAttribute('onclick')) {
                return;
            }
            link.addEventListener('click', function (event) {
                if (!confirm('Bạn có chắc muốn xóa mục này?')) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
</body>
</html>
