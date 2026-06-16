<script>
    (function() {
        const bellBtn = document.getElementById('notification-bell-btn');
        const dropdown = document.getElementById('notification-dropdown');
        const baseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;

        if (bellBtn && dropdown) {
            bellBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden') && window.RealtimePoll) {
                    window.RealtimePoll.refresh().then(() => {
                        if (window.RealtimePoll.renderNotificationList) {
                            // List rendered by refresh payload when dropdown open
                        }
                    });
                }
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            document.addEventListener('realtime:update', function(e) {
                if (!dropdown.classList.contains('hidden') && e.detail?.notifications?.items) {
                    window.RealtimePoll.renderNotificationList(e.detail.notifications.items);
                }
            });
        }

        window.markAllNotificationsRead = async function() {
            try {
                const formData = new FormData();
                formData.append('csrf_token', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
                await fetch(baseUrl + '/notifications/mark-read', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                if (window.RealtimePoll) {
                    window.RealtimePoll.applyNotificationBadge(0);
                    await window.RealtimePoll.refresh();
                }
            } catch (err) {
                console.warn('Mark read error:', err);
            }
        };
    })();
</script>
