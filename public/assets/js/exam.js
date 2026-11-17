/**
 * PKBM Mandiri - Exam Engine & Anti-Cheat Protection
 */

document.addEventListener('DOMContentLoaded', () => {
    const examForm = document.getElementById('exam-form');
    if (!examForm) return;

    // 1. Anti-Cheat: Disable Right Click & Context Menu
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        showToastWarning('Klik kanan dinonaktifkan demi ketertiban pengerjaan ujian.');
        return false;
    });

    // 2. Anti-Cheat: Disable Copy, Cut, Paste
    ['copy', 'cut', 'paste'].forEach((evt) => {
        document.addEventListener(evt, (e) => {
            e.preventDefault();
            showToastWarning('Aksi salin-tempel (copy-paste) dinonaktifkan selama ujian.');
            return false;
        });
    });

    // 3. Anti-Cheat: Disable key combinations (Ctrl+C, Ctrl+V, Ctrl+U, F12)
    document.addEventListener('keydown', (e) => {
        if (
            (e.ctrlKey && ['c', 'v', 'u', 's', 'a', 'p'].includes(e.key.toLowerCase())) ||
            e.key === 'F12'
        ) {
            e.preventDefault();
            showToastWarning('Kombinasi tombol dilarang saat ujian berlangsung.');
            return false;
        }
    });

    // 4. Navigasi Soal (Nomor Soal Lompat & Status Dijawab)
    const questionBoxes = document.querySelectorAll('.question-box');
    const navButtons = document.querySelectorAll('.exam-nav-num');
    let currentQ = 1;
    const totalQ = questionBoxes.length;

    function showQuestion(num) {
        questionBoxes.forEach((box) => box.classList.add('hidden'));
        const target = document.getElementById('q-box-' + num);
        if (target) {
            target.classList.remove('hidden');
            currentQ = num;
            updateNavHighlights();
            window.scrollTo({ top: target.offsetTop - 120, behavior: 'smooth' });
        }
    }

    window.jumpToQuestion = showQuestion;

    // Tombol Next & Prev
    const btnNext = document.getElementById('btn-next-q');
    const btnPrev = document.getElementById('btn-prev-q');

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            if (currentQ < totalQ) showQuestion(currentQ + 1);
        });
    }
    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentQ > 1) showQuestion(currentQ - 1);
        });
    }

    function updateNavHighlights() {
        navButtons.forEach((btn) => {
            const num = parseInt(btn.getAttribute('data-q'));
            if (num === currentQ) {
                btn.classList.add('ring-4', 'ring-blue-400');
            } else {
                btn.classList.remove('ring-4', 'ring-blue-400');
            }
        });
        if (btnPrev) btnPrev.disabled = (currentQ === 1);
        if (btnNext) {
            if (currentQ === totalQ) {
                btnNext.classList.add('hidden');
            } else {
                btnNext.classList.remove('hidden');
            }
        }
    }

    // 5. Tandai nomor navigasi saat soal sudah diisi
    function checkAnsweredStatus() {
        questionBoxes.forEach((box, idx) => {
            const num = idx + 1;
            const btn = document.querySelector(`.exam-nav-num[data-q="${num}"]`);
            if (!btn) return;

            const radio = box.querySelector('input[type="radio"]:checked');
            const textarea = box.querySelector('textarea');
            let isAnswered = false;

            if (radio) {
                isAnswered = true;
            } else if (textarea && textarea.value.trim().length > 0) {
                isAnswered = true;
            }

            if (isAnswered) {
                btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-300');
                btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
            } else {
                btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                btn.classList.add('bg-white', 'text-slate-700', 'border-slate-300');
            }
        });

        // Update progress counter
        const answeredCount = document.querySelectorAll('.exam-nav-num.bg-emerald-600').length;
        const progressEl = document.getElementById('answered-progress-text');
        if (progressEl) {
            progressEl.innerText = `${answeredCount} / ${totalQ} Soal Terjawab`;
        }
    }

    // Pasang listener pada seluruh input radio dan textarea
    document.querySelectorAll('#exam-form input[type="radio"], #exam-form textarea').forEach((el) => {
        el.addEventListener('change', checkAnsweredStatus);
        el.addEventListener('input', checkAnsweredStatus);
    });

    // Inisialisasi awal
    if (totalQ > 0) {
        showQuestion(1);
        checkAnsweredStatus();
    }

    // 6. Konfirmasi Selesai Ujian
    const finishBtn = document.getElementById('btn-finish-exam');
    if (finishBtn) {
        finishBtn.addEventListener('click', (e) => {
            const answeredCount = document.querySelectorAll('.exam-nav-num.bg-emerald-600').length;
            const unanswered = totalQ - answeredCount;
            let msg = `Anda telah menjawab ${answeredCount} dari ${totalQ} soal.`;
            if (unanswered > 0) {
                msg += ` Masih ada ${unanswered} soal yang belum terjawab!`;
            }
            msg += ' Apakah Anda yakin ingin menyelesaikan ujian sekarang?';

            if (confirm(msg)) {
                examForm.submit();
            }
        });
    }
});

// Helper toast message
function showToastWarning(text) {
    let toast = document.getElementById('exam-toast-warning');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'exam-toast-warning';
        toast.className = 'fixed bottom-5 right-5 z-50 bg-amber-500 text-white px-5 py-3 rounded-xl shadow-2xl font-medium text-sm flex items-center gap-3 transition-all duration-300';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="bi bi-shield-exclamation text-xl"></i> <span>${text}</span>`;
    toast.style.opacity = '1';
    clearTimeout(window.examToastTimeout);
    window.examToastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
    }, 3000);
}

