const laporanGrid   = document.getElementById('laporanGrid');
const filterJenis   = document.getElementById('filterJenis');
const filterStatus  = document.getElementById('filterStatus');
const formLaporan   = document.getElementById('formLaporan');
const modal         = document.getElementById('modalPopup');
const closeModal    = document.querySelector('.modal .close');
const hamburger     = document.getElementById('hamburger');
const navMenu       = document.getElementById('navMenu');

const formatTanggal = (t) => {
    if (!t) return '-';
    return new Date(t).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
};


function loadLaporan() {
    if (!laporanGrid) return;

    const jenis  = filterJenis?.value || 'Semua';
    const status = filterStatus?.value || 'Semua';

    let url = 'api/get_laporan.php';
    if (jenis !== 'Semua')  url += `?jenis=${encodeURIComponent(jenis)}`;
    if (status !== 'Semua') url += `${url.includes('?') ? '&' : '?'}status=${encodeURIComponent(status)}`;

    laporanGrid.innerHTML = `<p style="text-align:center; grid-column:1/-1; padding:4rem; color:#94a3b8;">
        <i class="fas fa-spinner fa-spin"></i> Memuat laporan...
    </p>`;

    fetch(url)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(res => {
            const data = res.success && res.data ? res.data : [];

            if (data.length === 0) {
                laporanGrid.innerHTML = `<p style="text-align:center; grid-column:1/-1; color:#94a3b8; padding:5rem;">
                    Tidak ada laporan yang sesuai filter.
                </p>`;
                return;
            }

            laporanGrid.innerHTML = data.map(l => {
                const statusClass = (l.status || 'Baru').toLowerCase().replace(' ', '-');
                const desc = l.deskripsi.length > 150 ? l.deskripsi.substring(0,150) + '...' : l.deskripsi;

                return `
                <div class="laporan-card reveal">
                    <div class="laporan-card-header">
                        <h4>${l.namaPelapor || 'Anonim'}</h4>
                        <small><i class="fas fa-map-marker-alt"></i> ${l.lokasi}</small>
                        <small><i class="fas fa-calendar"></i> ${formatTanggal(l.tanggal)}</small>
                    </div>
                    <div class="laporan-card-body">
                        <p>${desc}</p>
                        <div class="badge badge-${statusClass}">${l.status || 'Baru'}</div>
                        <span class="jenis-badge"><i class="fas fa-tag"></i> ${l.jenisBantuan}</span>
                    </div>
                </div>`;
            }).join('');

            document.querySelectorAll('.reveal').forEach(el => {
                const observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('active');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });
                observer.observe(el);
            });
        })
        .catch(() => {
            laporanGrid.innerHTML = `<p style="text-align:center; color:#ef4444; grid-column:1/-1; padding:5rem;">
                Gagal memuat data. Coba refresh halaman.
            </p>`;
        });
}

function updateStatistik() {
    fetch('api/get_stats.php')
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            if (data?.success) {
                animateValue('totalLaporan', data.total || 0);
                animateValue('laporanSelesai', data.selesai || 0);
                animateValue('laporanProses', data.diproses || 0);
            }
        })
        .catch(() => {});
}

function animateValue(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const duration = 1500;
    const increment = target / (duration / 20);

    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            el.textContent = target;
            clearInterval(timer);
        } else {
            el.textContent = Math.floor(start);
        }
    }, 20);
}


if (formLaporan) {
    formLaporan.addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = this.querySelector('.btn-submit');
        const originalHTML = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

        const formData = new FormData(this);

        try {
            const res = await fetch('proses_laporan.php', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();

            if (result.success) {
                modal.style.display = 'flex';
                this.reset();

                updateStatistik();
                loadLaporan();

                setTimeout(() => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                    }
                }, 5000);

            } else {
                alert('Gagal mengirim laporan: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Koneksi error. Pastikan server menyala dan coba lagi.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    });
}

if (closeModal) {
    closeModal.onclick = () => modal.style.display = 'none';
}
window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
});

if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => navMenu.classList.toggle('active'));
}

document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', () => {
        if (navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
        }
    });
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    updateStatistik();
    loadLaporan();

    window.scrollTo(0, 0);

    document.querySelectorAll('.reveal').forEach(el => {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        observer.observe(el);
    });
});