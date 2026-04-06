function fetchOnboardingCount() {
    fetch(NOTIFICATION_URLS.onboarding)
        .then(response => response.json())
        .then(data => {
            const count = data.count;
            const onboardingItem = document.getElementById('onboarding-item');

            // Show or hide the entire dropdown item
            if (count > 0) {
                onboardingItem.style.display = 'block';
                document.getElementById('onboarding-count').textContent = count;
            } else {
                onboardingItem.style.display = 'none';
            }

            updateTotalCount();
        })
        .catch(error => console.log('Onboarding error:', error));
}

function updateTotalCount() {
    const onboarding = parseInt(document.getElementById('onboarding-count').textContent) || 0;

    const total = onboarding;
    const badge = document.getElementById('total-count');
    const label = document.getElementById('total-notifications');

    badge.textContent = total;
    badge.style.display = total > 0 ? 'inline' : 'none';

    if (total === 0) {
        label.textContent = 'No Notifications';
    } else if (total === 1) {
        label.textContent = '1 Notification';
    } else {
        label.textContent = total + ' Notifications';
    }
}

function fetchAllNotifications() {
    fetchOnboardingCount();
}

fetchAllNotifications();
setInterval(fetchAllNotifications, 30000);