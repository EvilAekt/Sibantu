// === DOM Elements ===
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');
const formLaporan = document.getElementById('formLaporan');
const laporanGrid = document.getElementById('laporanGrid');
const filterJenis = document.getElementById('filterJenis');
const filterStatus = document.getElementById('filterStatus');
const modal = document.getElementById('modalPopup');
const closeModal = document.querySelector('.close');

// === Scroll & Navbar ===
if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
window.addEventListener('load', () => window.scrollTo(0, 0));

hamburger.addEventListener('click', () => navMenu.classList.toggle('active'));

document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', () => navMenu.classList.remove('active'));
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    navbar.style.boxShadow = window.scrollY > 50 ? '0 5px 20px rgba(0,0,0,0.15)' : '0 2px 10px rgba(0,0,0,0.1)';
});

// === Update Statistik dari Database ===
function updateStatistik() {
    fetch('api/get_stats.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            animateNumber('totalLaporan', data.total);
            animateNumber('laporanSelesai', data.selesai);
            animateNumber('laporanProses', data.diproses);
        }
    })
    .catch(err => console.error('Gagal ambil statistik:', err));
}

function animateNumber(elementId, target) {
    const element = document.getElementById(elementId);
    let current = 0;
    const increment = target / 50;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 20);
}

// === Render Laporan dari Database ===
function renderLaporan(data) {
    if (data.length === 0) {
        laporanGrid.innerHTML = '<p style="text-align: center; grid-column: 1/-1; font-size: 1.2rem; color: #6b7280;">Tidak ada laporan yang sesuai dengan filter.</p>';
        return;
    }
    laporanGrid.innerHTML = data.map(laporan => {
        const statusClass = laporan.status.toLowerCase().replace(' ', '-');
        return `
            <div class="laporan-card">
                <div class="card-header">
                    <h3>${laporan.nama_pelapor}</h3>
                    <div class="card-meta">
                        <span><i class="fas fa-map-marker-alt"></i> ${laporan.lokasi}</span>
                        <span><i class="fas fa-calendar"></i> ${formatTanggal(laporan.tanggal)}</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>${laporan.deskripsi}</p>
                    <span class="jenis-badge"><i class="fas fa-tag"></i> ${laporan.jenis_bantuan}</span>
                    <div style="margin-top: 1rem;">
                        <span class="badge badge-${statusClass}">${laporan.status}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function formatTanggal(tanggal) {
    const date = new Date(tanggal);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

// === Ambil Data Laporan dari Database ===
function loadLaporan() {
    fetch('api/get_laporan.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderLaporan(data.laporan);
        }
    })
    .catch(err => console.error('Gagal ambil laporan:', err));
}

// === Filter Laporan ===
function filterLaporan() {
    const jenisFilter = filterJenis.value;
    const statusFilter = filterStatus.value;

    let url = 'api/get_laporan.php';
    const params = [];
    if (jenisFilter) params.push(`jenis=${encodeURIComponent(jenisFilter)}`);
    if (statusFilter) params.push(`status=${encodeURIComponent(statusFilter)}`);
    if (params.length) url += '?' + params.join('&');

    fetch(url)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderLaporan(data.laporan);
        }
    })
    .catch(err => console.error('Gagal filter laporan:', err));
}

filterJenis.addEventListener('change', filterLaporan);
filterStatus.addEventListener('change', filterLaporan);

// === Submit Form ke Database ===
formLaporan.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(formLaporan);

    // ✅ Debug: pastikan namaPelapor ada
    console.log('Nama Pelapor:', formData.get('namaPelapor'));

    fetch('proses_laporan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ✅ Tampilkan modal sukses
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            formLaporan.reset();

            // ✅ Refresh data
            loadLaporan();
            updateStatistik();

            // ✅ Auto-close modal
            setTimeout(() => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }, 5000);
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat mengirim laporan.');
        console.error('Error:', error);
    });
});

// === Modal Handler ===
closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
});
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// === Scrollspy ===
const sections = document.querySelectorAll('section[id]');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (scrollY >= sectionTop - 200) current = section.getAttribute('id');
    });
    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) link.classList.add('active');
    });
});

// === Scroll Reveal ===
document.addEventListener('DOMContentLoaded', () => {
    updateStatistik();
    loadLaporan();

    const revealElements = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    revealElements.forEach(el => {
        el.classList.remove('active');
        observer.observe(el);
    });
});