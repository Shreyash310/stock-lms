/**
 * StockVerse - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initSidebar();
    initSearch();
    initQuiz();
    initModals();
    initMarkComplete();
    initLandingNav();
});

/* ========================
   Dark Mode Toggle
   ======================== */
function initThemeToggle() {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(toggle, saved);

    toggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeIcon(toggle, next);
    });
}

function updateThemeIcon(btn, theme) {
    btn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
    btn.title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
}

/* Apply saved theme immediately (before DOM ready) */
(function() {
    const saved = localStorage.getItem('theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);
})();

/* ========================
   Sidebar (Mobile)
   ======================== */
function initSidebar() {
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('active');
    });

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
}

/* ========================
   Search / Filter
   ======================== */
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('[data-searchable]');

        cards.forEach(card => {
            const text = card.getAttribute('data-searchable').toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

/* ========================
   Quiz System
   ======================== */
function initQuiz() {
    // Handle option selection
    document.querySelectorAll('.quiz-option').forEach(option => {
        option.addEventListener('click', () => {
            const question = option.closest('.quiz-question');
            question.querySelectorAll('.quiz-option').forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');
            option.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Handle quiz submission
    const quizForm = document.getElementById('quizForm');
    if (!quizForm) return;

    quizForm.addEventListener('submit', (e) => {
        // Let the form submit normally to PHP
    });
}

/* ========================
   Modals (Admin)
   ======================== */
function initModals() {
    // Open modal
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('active');
        });
    });

    // Close modal
    document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            if (modal) modal.classList.remove('active');
        });
    });

    // Close on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });
}

/* Open modal with data (for editing) */
function openEditModal(modalId, data) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    Object.keys(data).forEach(key => {
        const field = modal.querySelector(`[name="${key}"]`);
        if (field) {
            if (field.tagName === 'TEXTAREA') {
                field.value = data[key];
            } else {
                field.value = data[key];
            }
        }
    });

    modal.classList.add('active');
}

/* ========================
   Mark Chapter Complete
   ======================== */
function initMarkComplete() {
    const btn = document.getElementById('markCompleteBtn');
    if (!btn) return;

    btn.addEventListener('click', () => {
        const chapterId = btn.getAttribute('data-chapter-id');

        fetch(btn.getAttribute('data-url'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `chapter_id=${chapterId}&csrf_token=${btn.getAttribute('data-csrf')}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '✅ Completed';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-secondary');
                btn.disabled = true;

                // Update sidebar item
                const sidebarItem = document.querySelector(`.chapter-list-item[data-chapter="${chapterId}"]`);
                if (sidebarItem) {
                    sidebarItem.classList.add('completed');
                    const checkMark = sidebarItem.querySelector('.check-mark');
                    if (checkMark) checkMark.textContent = '✓';
                }
            }
        })
        .catch(err => console.error('Error:', err));
    });
}

/* ========================
   Landing Page Nav
   ======================== */
function initLandingNav() {
    const nav = document.querySelector('.landing-nav');
    if (!nav) return;

    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 50);
    });
}

/* ========================
   Admin: Delete Confirmation
   ======================== */
function confirmDelete(form, itemName) {
    if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
        form.submit();
    }
    return false;
}

/* ========================
   Admin: Edit Item
   ======================== */
function editItem(modalId, data) {
    openEditModal(modalId, data);

    // Update form action if needed
    const modal = document.getElementById(modalId);
    if (modal) {
        const titleEl = modal.querySelector('.modal-header h3');
        if (titleEl) titleEl.textContent = 'Edit Item';
    }
}
