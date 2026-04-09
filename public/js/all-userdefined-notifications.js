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

function fetchScholarshipCount() {
    fetch(NOTIFICATION_URLS.scholarship)
        .then(response => response.json())
        .then(data => {
            const count = data.count;
            const scholarshipItem = document.getElementById('scholarship-item');
            const scholarshipBadge = document.getElementById('scholarship-count');

            if (count > 0) {
                if (scholarshipItem) scholarshipItem.style.display = 'block';
                if (scholarshipBadge) scholarshipBadge.textContent = count;
            } else {
                if (scholarshipItem) scholarshipItem.style.display = 'none';
                if (scholarshipBadge) scholarshipBadge.textContent = 0;
            }
            updateTotalCount();
        })
        .catch(error => console.error('Scholarship notification error:', error));
}

function updateTotalCount() {
    // የሁለቱንም ድምር ለመያዝ
    const onboarding = parseInt(document.getElementById('onboarding-count')?.textContent) || 0;
    const scholarship = parseInt(document.getElementById('scholarship-count')?.textContent) || 0;

    const total = onboarding + scholarship;
    const badge = document.getElementById('total-count');
    const label = document.getElementById('total-notifications');

    if (badge) {
        badge.textContent = total;
        badge.style.display = total > 0 ? 'inline' : 'none';
    }

    if (label) {
        if (total === 0) {
            label.textContent = 'No Notifications';
        } else {
            label.textContent = total + (total === 1 ? ' Notification' : ' Notifications');
        }
    }
}

// ሁሉንም በአንድ ላይ ለመጥራት
function fetchAllNotifications() {
    fetchOnboardingCount();
    fetchScholarshipCount(); // አዲሱ ጥሪ
}

fetchAllNotifications();
setInterval(fetchAllNotifications, 30000); // በየ 30 ሰከንዱ ቼክ ያደርጋል