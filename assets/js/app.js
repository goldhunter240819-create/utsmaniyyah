/**
 * Sistem Informasi Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 * Fullstack Client-Side connected to PHP & MySQL Backend (api.php)
 */

let appData = {
  currentUser: { id: 1, username: 'admin', nama: 'Super Admin Lajnah Pusat', role: 'SUPER_ADMIN', rtq_id: null },
  lembaga: {},
  daftarRtq: [],
  daftarPeriode: [],
  daftarUsers: [],
  santri: []
};

// 9 Mata Ujian Standar Yanbua
const SUBJECT_KEYS = [
  { key: 'ghorib', label: 'Ghoroibul Qur\'an' },
  { key: 'tajwid', label: 'Ilmu Tajwid' },
  { key: 'tartil', label: 'Tartil & Fashohah' },
  { key: 'surat', label: 'Hafalan Surat Pendek' },
  { key: 'doa', label: 'Hafalan Do\'a Harian' },
  { key: 'tauhid', label: 'Tauhid Dasar' },
  { key: 'wudlu', label: 'Praktik Wudlu' },
  { key: 'sholat', label: 'Praktik Sholat' },
  { key: 'kitabah', label: 'Kitabah' }
];

// SweetAlert2 Helper Suite (Islamic Emerald & Gold Luxury Theme)
const SwalAlert = {
  success: (title, text = '') => {
    return Swal.fire({
      icon: 'success',
      title: title,
      text: text,
      confirmButtonColor: '#059669',
      confirmButtonText: 'Alhamdulillah, Baik',
      timer: 2500,
      timerProgressBar: true
    });
  },
  error: (title, text = '') => {
    return Swal.fire({
      icon: 'error',
      title: title,
      text: text,
      confirmButtonColor: '#059669',
      confirmButtonText: 'Tutup'
    });
  },
  warning: (title, text = '') => {
    return Swal.fire({
      icon: 'warning',
      title: title,
      text: text,
      confirmButtonColor: '#f59e0b',
      confirmButtonText: 'Mengerti'
    });
  },
  confirm: async (title, text = '', confirmText = 'Ya, Lanjutkan', isDanger = false) => {
    return await Swal.fire({
      title: title,
      html: text,
      icon: isDanger ? 'warning' : 'question',
      showCancelButton: true,
      confirmButtonColor: isDanger ? '#dc2626' : '#059669',
      cancelButtonColor: '#64748b',
      confirmButtonText: confirmText,
      cancelButtonText: 'Batal',
      reverseButtons: true
    });
  },
  toast: (title, icon = 'success') => {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 1800,
      timerProgressBar: false,
      background: '#ffffff',
      color: '#0f172a',
      iconColor: icon === 'success' ? '#10b981' : '#f59e0b'
    });
    Toast.fire({
      icon: icon,
      title: title
    });
  }
};

// Konverter Nilai Angka ke Huruf Ejaan Bahasa Indonesia
function angkaKeTerbilang(angka) {
  if (angka === null || angka === undefined || isNaN(angka)) return '-';
  
  const digitWords = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
  const units = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

  function toWordsInt(n) {
    n = Math.floor(n);
    if (n < 12) return units[n];
    if (n < 20) return toWordsInt(n - 10) + ' Belas';
    if (n < 100) return toWordsInt(Math.floor(n / 10)) + ' Puluh' + (n % 10 !== 0 ? ' ' + units[n % 10] : '');
    if (n < 200) return 'Seratus' + (n % 100 !== 0 ? ' ' + toWordsInt(n % 100) : '');
    if (n < 1000) return toWordsInt(Math.floor(n / 100)) + ' Ratus' + (n % 100 !== 0 ? ' ' + toWordsInt(n % 100) : '');
    if (n < 2000) return 'Seribu' + (n % 1000 !== 0 ? ' ' + toWordsInt(n % 1000) : '');
    if (n < 1000000) return toWordsInt(Math.floor(n / 1000)) + ' Ribu' + (n % 1000 !== 0 ? ' ' + toWordsInt(n % 1000) : '');
    return n.toString();
  }

  const parts = angka.toString().split('.');
  const integerPart = parseInt(parts[0], 10);
  let result = toWordsInt(integerPart);

  if (parts.length > 1 && parts[1]) {
    result += ' Koma';
    for (let char of parts[1]) {
      const d = parseInt(char, 10);
      result += ' ' + digitWords[d];
    }
  }

  return result.trim();
}

// Inisialisasi Aplikasi
document.addEventListener('DOMContentLoaded', async () => {
  setupNavigation();
  setupEventListeners();
  await loadDataFromBackend();
  switchTab('tab-dashboard');
});

// Load data dari PHP & MySQL Backend
async function loadDataFromBackend() {
  const rtqFilter = document.getElementById('topbarRtqFilter')?.value || 'ALL';
  const periodeFilter = document.getElementById('topbarPeriodeFilter')?.value || 'ALL';

  try {
    const res = await fetch(`api.php?action=get_init&rtq_id=${rtqFilter}&periode_id=${periodeFilter}`);
    const data = await res.json();
    
    if (data.status === 'unauthorized') {
      document.getElementById('loginScreen').style.display = 'flex';
      document.getElementById('mainApp').style.display = 'none';
      return;
    }

    if (data.status === 'success') {
      document.getElementById('loginScreen').style.display = 'none';
      document.getElementById('mainApp').style.display = 'flex';

      appData = {
        currentUser: data.currentUser,
        lembaga: data.lembaga,
        daftarRtq: data.daftarRtq,
        daftarPeriode: data.daftarPeriode,
        daftarUsers: data.daftarUsers,
        santri: data.santri
      };

      // Terapkan isolasi visual untuk Pengurus RTQ vs Developer
      const isDeveloper = (appData.currentUser && (appData.currentUser.role === 'DEVELOPER' || appData.currentUser.role === 'SUPER_ADMIN' || appData.currentUser.username === 'Maul'));
      
      const btnAddRtq = document.getElementById('btnAddRtqBtn');
      if (btnAddRtq) btnAddRtq.style.display = isDeveloper ? 'inline-flex' : 'none';
      
      const btnAddPeriode = document.getElementById('btnAddPeriodeBtn');
      if (btnAddPeriode) btnAddPeriode.style.display = isDeveloper ? 'inline-flex' : 'none';

      const saveLembagaWrap = document.getElementById('btnSaveLembagaWrap');
      if (saveLembagaWrap) saveLembagaWrap.style.display = isDeveloper ? 'block' : 'none';

      const cfgLembagaInputs = document.querySelectorAll('#configForm input');
      cfgLembagaInputs.forEach(inp => {
        inp.disabled = !isDeveloper;
      });

      renderAll();
      updateFilterSelectors();
    }
  } catch (err) {
    console.error('Error fetching data from api.php:', err);
  }
}

// Handle Login Form
async function handleLogin(e) {
  e.preventDefault();
  const u = document.getElementById('loginUsername').value.trim();
  const p = document.getElementById('loginPassword').value.trim();

  try {
    const res = await fetch('api.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: u, password: p })
    });
    const result = await res.json();
    if (result.status === 'success') {
      SwalAlert.toast(`Selamat datang, ${result.user.nama}`);
      await loadDataFromBackend();
      switchTab('tab-dashboard');
    } else {
      SwalAlert.error('Login Gagal', result.message || 'Periksa kembali username & password.');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan Jaringan', 'Terjadi kesalahan saat menghubungi server.');
  }
}

// Handle Logout
async function handleLogout() {
  const confirmResult = await SwalAlert.confirm(
    'Konfirmasi Keluar',
    'Apakah Anda yakin ingin keluar dari sistem Munaqosyah?',
    'Ya, Keluar'
  );
  if (confirmResult.isConfirmed) {
    try {
      await fetch('api.php?action=logout');
      switchTab('tab-dashboard'); // Reset tab aktif kembali ke Dashboard
      document.getElementById('loginScreen').style.display = 'flex';
      document.getElementById('mainApp').style.display = 'none';
      document.getElementById('loginForm')?.reset();
      SwalAlert.toast('Berhasil keluar dari sistem');
    } catch (err) {
      location.reload();
    }
  }
}

// Update Topbar Dropdown Selectors
function updateFilterSelectors() {
  const rtqSelect = document.getElementById('topbarRtqFilter');
  const periodeSelect = document.getElementById('topbarPeriodeFilter');
  const isPengurus = appData.currentUser.role === 'PENGURUS_RTQ';

  if (rtqSelect) {
    const currentVal = rtqSelect.value;
    rtqSelect.innerHTML = isPengurus ? '' : '<option value="ALL">Semua Unit RTQ (Gabungan)</option>';
    
    appData.daftarRtq.forEach(r => {
      if (isPengurus && r.id != appData.currentUser.rtq_id) return;
      const opt = document.createElement('option');
      opt.value = r.id;
      opt.textContent = `${r.kode} - ${r.nama}`;
      rtqSelect.appendChild(opt);
    });

    if (isPengurus) {
      rtqSelect.value = appData.currentUser.rtq_id;
      rtqSelect.disabled = true;
    } else if (currentVal) {
      rtqSelect.value = currentVal;
    }
  }

  if (periodeSelect) {
    const currentVal = periodeSelect.value;
    periodeSelect.innerHTML = '<option value="ALL">Semua Periode Ujian</option>';
    appData.daftarPeriode.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.tahun_pelajaran;
      periodeSelect.appendChild(opt);
    });
    if (currentVal) periodeSelect.value = currentVal;
  }

  // Update current user badge in sidebar
  const userBadge = document.getElementById('currentUserBadge');
  if (userBadge) {
    userBadge.textContent = appData.currentUser.nama;
  }
  const avatarChar = document.getElementById('userAvatarChar');
  if (avatarChar) {
    avatarChar.textContent = (appData.currentUser.nama || 'P').charAt(0).toUpperCase();
  }
  const sidebarRtqLabel = document.getElementById('sidebarActiveRtqLabel');
  if (sidebarRtqLabel) {
    if (isPengurus) {
      const myRtq = appData.daftarRtq.find(r => r.id == appData.currentUser.rtq_id);
      sidebarRtqLabel.textContent = myRtq ? myRtq.nama : 'Pengurus RTQ';
    } else {
      sidebarRtqLabel.textContent = 'Semua RTQ (Developer)';
    }
  }
}

// Navigation Handling
function setupNavigation() {
  document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      switchTab(link.getAttribute('data-tab'));
    });
  });

  const menuToggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  }
}

function switchTab(tabId) {
  document.querySelectorAll('.nav-link[data-tab]').forEach(el => {
    el.classList.toggle('active', el.getAttribute('data-tab') === tabId);
  });
  document.querySelectorAll('.tab-content').forEach(el => {
    el.classList.remove('active');
  });

  const target = document.getElementById(tabId);
  if (target) target.classList.add('active');

  const titles = {
    'tab-dashboard': 'Dashboard Munaqosyah',
    'tab-santri': 'Data Santri Peserta Ujian',
    'tab-penilaian': 'Input & Edit Nilai Munaqosyah',
    'tab-rekap': 'Rekapitulasi Nilai & Rangking',
    'tab-cetak': 'Cetak Dokumen Resmi (Syahadah & Transkrip)',
    'tab-pengaturan': 'Pengaturan Multi-RTQ & Sistem'
  };
  const titleEl = document.getElementById('topbarPageTitle');
  if (titleEl && titles[tabId]) titleEl.textContent = titles[tabId];

  if (tabId === 'tab-dashboard') renderDashboard();
  if (tabId === 'tab-santri') renderSantriTable();
  if (tabId === 'tab-penilaian') renderPenilaianTable();
  if (tabId === 'tab-rekap') renderRekapTable();
  if (tabId === 'tab-cetak') setupCetakTab();
  if (tabId === 'tab-pengaturan') populateConfigTab();

  const sidebar = document.getElementById('sidebar');
  if (sidebar && window.innerWidth <= 992) sidebar.classList.remove('show');

  const mainContent = document.querySelector('.main-content');
  if (mainContent && mainContent.scrollTo) {
    mainContent.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

// Calculate Stats & Ranks
function calculateSantriStats() {
  const kkm = parseFloat(appData.lembaga.kkm) || 60;
  const list = appData.santri.map(s => {
    const ghorib = parseFloat(s.ghorib) || 0;
    const tajwid = parseFloat(s.tajwid) || 0;
    const tartil = parseFloat(s.tartil) || 0;
    const surat = parseFloat(s.surat) || 0;
    const doa = parseFloat(s.doa) || 0;
    const tauhid = parseFloat(s.tauhid) || 0;
    const wudlu = parseFloat(s.wudlu) || 0;
    const sholat = parseFloat(s.sholat) || 0;
    const kitabah = parseFloat(s.kitabah) || 0;

    const total = parseFloat((ghorib + tajwid + tartil + surat + doa + tauhid + wudlu + sholat + kitabah).toFixed(2));
    const avg = parseFloat((total / 9).toFixed(2));
    const isLulus = total >= kkm;

    return {
      ...s,
      ghorib, tajwid, tartil, surat, doa, tauhid, wudlu, sholat, kitabah,
      totalNilai: total,
      rataRata: avg,
      isLulus
    };
  });

  const sorted = [...list].sort((a, b) => b.totalNilai - a.totalNilai);
  let rank = 1;
  sorted.forEach((item, index) => {
    if (index > 0 && item.totalNilai < sorted[index - 1].totalNilai) {
      rank = index + 1;
    }
    item.rank = rank;
  });

  const rankMap = new Map(sorted.map(s => [s.id, s.rank]));
  list.forEach(s => { s.rank = rankMap.get(s.id); });

  return { list, sorted };
}

// Render Master
function renderAll() {
  renderDashboard();
  renderSantriTable();
  renderPenilaianTable();
  renderRekapTable();
  setupCetakTab();
  populateConfigTab();
}

// ==========================================
// 1. DASHBOARD
// ==========================================
function renderDashboard() {
  const { list, sorted } = calculateSantriStats();
  const total = list.length;
  const lulus = list.filter(s => s.isLulus).length;
  const tidakLulus = total - lulus;
  const avgOverall = total > 0 ? (list.reduce((acc, curr) => acc + curr.rataRata, 0) / total).toFixed(1) : 0;

  document.getElementById('dashTotalSantri').textContent = total;
  document.getElementById('dashLulus').textContent = lulus;
  document.getElementById('dashTidakLulus').textContent = tidakLulus;
  document.getElementById('dashRataRata').textContent = avgOverall;

  // Podium
  const podium = document.getElementById('dashPodium');
  if (podium) {
    if (sorted.length === 0) {
      podium.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--dark-500); padding: 20px;">Belum ada data santri</div>';
    } else {
      let html = '';
      sorted.slice(0, 3).forEach((s, idx) => {
        const rankClass = idx === 0 ? 'rank-1' : idx === 1 ? 'rank-2' : 'rank-3';
        const rankLabel = idx === 0 ? 'Juara 1' : idx === 1 ? 'Juara 2' : 'Juara 3';
        const badgeIcon = idx === 0 ? '🥇' : idx === 1 ? '🥈' : '🥉';
        html += `
          <div class="podium-card ${rankClass}">
            <div class="podium-badge">${badgeIcon}</div>
            <div class="badge ${idx === 0 ? 'badge-gold' : 'badge-primary'}" style="margin-bottom: 8px;">${rankLabel}</div>
            <h4 class="podium-name">${escapeHtml(s.nama)}</h4>
            <div class="podium-info">${escapeHtml(s.rtq_nama || '-')} | No. Ujian: ${escapeHtml(s.no_ujian || '-')}</div>
            <div class="podium-score">${s.totalNilai}</div>
            <div style="font-size: 0.75rem; color: var(--dark-500);">Rata-rata: ${s.rataRata} (${s.isLulus ? 'Lulus' : 'Belum Lulus'})</div>
          </div>
        `;
      });
      podium.innerHTML = html;
    }
  }

  // Subject Bars
  const bars = document.getElementById('dashSubjectBars');
  if (bars) {
    let html = '';
    SUBJECT_KEYS.forEach(sub => {
      let subTot = 0;
      list.forEach(s => { subTot += s[sub.key] || 0; });
      const subAvg = total > 0 ? (subTot / total).toFixed(1) : 0;
      const pct = Math.min(100, Math.max(0, subAvg));

      html += `
        <div style="margin-bottom: 14px;">
          <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
            <span>${sub.label}</span>
            <span style="color: var(--primary-700); font-weight: 700;">${subAvg} / 100</span>
          </div>
          <div style="height: 9px; background: var(--gray-200); border-radius: 10px; overflow: hidden;">
            <div style="height: 100%; width: ${pct}%; background: linear-gradient(90deg, #10b981, #f59e0b); border-radius: 10px; transition: width 0.5s ease;"></div>
          </div>
        </div>
      `;
    });
    bars.innerHTML = html;
  }
}

// ==========================================
// 2. DATA SANTRI
// ==========================================
function renderSantriTable() {
  const tbody = document.getElementById('santriTableBody');
  if (!tbody) return;

  const q = (document.getElementById('searchSantriInput')?.value || '').toLowerCase();
  const filtered = appData.santri.filter(s => {
    return (s.nama || '').toLowerCase().includes(q) ||
           (s.no_induk || '').toLowerCase().includes(q) ||
           (s.no_ujian || '').toLowerCase().includes(q) ||
           (s.rtq_nama || '').toLowerCase().includes(q);
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center" style="padding: 30px; color: var(--dark-500);">Tidak ada data santri ditemukan.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((s, idx) => {
    const genderBadge = s.gender === 'L' ? '<span class="badge badge-gender-l">L (Bin)</span>' : '<span class="badge badge-gender-p">P (Binti)</span>';
    return `
      <tr>
        <td class="text-center font-bold">${idx + 1}</td>
        <td>
          <strong style="color: var(--primary-900); font-size: 0.95rem;">${escapeHtml(s.nama)}</strong>
          <div style="font-size: 0.78rem; color: var(--dark-500);">${escapeHtml(s.ttl || '-')}</div>
        </td>
        <td>
          <span class="badge badge-rtq">${escapeHtml(s.rtq_kode || s.rtq_nama || '-')}</span>
        </td>
        <td>${genderBadge}</td>
        <td>
          <div>Induk: <code>${escapeHtml(s.no_induk || '-')}</code></div>
          <div>Ujian: <code>${escapeHtml(s.no_ujian || '-')}</code></div>
        </td>
        <td>
          <div style="font-size: 0.8rem; font-family: monospace; color: #047857; font-weight: 600;">${escapeHtml(s.no_syahadah || '-')}</div>
        </td>
        <td class="text-center">
          <div class="action-btn-group">
            <button class="btn-icon-action edit" title="Edit Data Santri" onclick="openEditSantriModal(${s.id})">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="btn-icon-action syahadah" title="Cetak Syahadah Depan" onclick="previewSingleCetak(${s.id}, 'depan')">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            </button>
            <button class="btn-icon-action transkrip" title="Cetak Transkrip Belakang" onclick="previewSingleCetak(${s.id}, 'belakang')">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </button>
            <button class="btn-icon-action delete" title="Hapus Santri" onclick="confirmDeleteSantri(${s.id})">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

// Modal Santri Form
function openAddSantriModal() {
  document.getElementById('santriModalTitle').textContent = 'Tambah Santri Baru';
  document.getElementById('santriForm').reset();
  document.getElementById('santriIdInput').value = '';

  // Isi dropdown RTQ
  const rtqSelect = document.getElementById('inputRtq');
  rtqSelect.innerHTML = '';
  appData.daftarRtq.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.id;
    opt.textContent = `${r.kode} - ${r.nama}`;
    rtqSelect.appendChild(opt);
  });
  if (appData.currentUser.role === 'PENGURUS_RTQ') {
    rtqSelect.value = appData.currentUser.rtq_id;
  }

  // Isi dropdown Periode
  const periodeSelect = document.getElementById('inputPeriode');
  periodeSelect.innerHTML = '';
  appData.daftarPeriode.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.id;
    opt.textContent = p.tahun_pelajaran;
    periodeSelect.appendChild(opt);
  });

  // Nomor auto-generate
  const nextNo = (appData.santri.length + 1).toString().padStart(4, '0');
  const shortNo = (appData.santri.length + 1).toString().padStart(3, '0');
  document.getElementById('inputNoInduk').value = nextNo;
  document.getElementById('inputNoUjian').value = nextNo;
  document.getElementById('inputNoSyahadah').value = `LMY-LB/RTQ-U3/${shortNo}/09/10/2025`;

  openModal('santriModal');
}

function openEditSantriModal(id) {
  const s = appData.santri.find(x => x.id == id);
  if (!s) return;

  document.getElementById('santriModalTitle').textContent = 'Edit Data Santri';
  document.getElementById('santriIdInput').value = s.id;
  document.getElementById('inputNama').value = s.nama || '';
  document.getElementById('inputGender').value = s.gender || 'L';
  document.getElementById('inputTtl').value = s.ttl || '';
  document.getElementById('inputAyah').value = s.ayah || '';
  document.getElementById('inputIbu').value = s.ibu || '';
  document.getElementById('inputAlamat').value = s.alamat || '';
  document.getElementById('inputNoInduk').value = s.no_induk || '';
  document.getElementById('inputNoUjian').value = s.no_ujian || '';
  document.getElementById('inputNoSyahadah').value = s.no_syahadah || '';

  const rtqSelect = document.getElementById('inputRtq');
  rtqSelect.innerHTML = '';
  appData.daftarRtq.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.id;
    opt.textContent = `${r.kode} - ${r.nama}`;
    rtqSelect.appendChild(opt);
  });
  rtqSelect.value = s.rtq_id;

  const periodeSelect = document.getElementById('inputPeriode');
  periodeSelect.innerHTML = '';
  appData.daftarPeriode.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.id;
    opt.textContent = p.tahun_pelajaran;
    periodeSelect.appendChild(opt);
  });
  periodeSelect.value = s.periode_id;

  openModal('santriModal');
}

async function handleSaveSantri(e) {
  e.preventDefault();
  const id = document.getElementById('santriIdInput').value;
  const payload = {
    id: id || null,
    nama: document.getElementById('inputNama').value.trim(),
    gender: document.getElementById('inputGender').value,
    rtq_id: document.getElementById('inputRtq').value,
    periode_id: document.getElementById('inputPeriode').value,
    ttl: document.getElementById('inputTtl').value.trim(),
    ayah: document.getElementById('inputAyah').value.trim(),
    ibu: document.getElementById('inputIbu').value.trim(),
    alamat: document.getElementById('inputAlamat').value.trim(),
    no_induk: document.getElementById('inputNoInduk').value.trim(),
    no_ujian: document.getElementById('inputNoUjian').value.trim(),
    no_syahadah: document.getElementById('inputNoSyahadah').value.trim()
  };

  try {
    const res = await fetch('api.php?action=save_santri', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      closeModal('santriModal');
      await loadDataFromBackend();
      SwalAlert.success('Berhasil Disimpan', 'Data santri berhasil disimpan ke database MySQL.');
    } else {
      SwalAlert.error('Gagal Menyimpan', result.message || 'Gagal menyimpan data santri');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan', 'Terjadi kesalahan saat menyimpan ke database MySQL.');
  }
}

async function confirmDeleteSantri(id) {
  const s = appData.santri.find(x => x.id == id);
  if (!s) return;
  const confirmResult = await SwalAlert.confirm(
    'Hapus Data Santri?',
    `Yakin ingin menghapus santri "<strong>${escapeHtml(s.nama)}</strong>" dari database MySQL?<br><small style="color: #64748b;">Seluruh riwayat nilai ujian santri ini juga akan terhapus permanen.</small>`,
    'Ya, Hapus Santri',
    true
  );
  if (confirmResult.isConfirmed) {
    try {
      const res = await fetch('api.php?action=delete_santri', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const result = await res.json();
      if (result.status === 'success') {
        await loadDataFromBackend();
        SwalAlert.toast(`Santri ${s.nama} berhasil dihapus`);
      } else {
        SwalAlert.error('Gagal', result.message || 'Gagal menghapus santri dari database.');
      }
    } catch (err) {
      SwalAlert.error('Kesalahan', 'Gagal menghapus data dari database.');
    }
  }
}

// ==========================================
// 3. PENILAIAN MUNAQOSYAH (LIVE MYSQL UPDATE)
// ==========================================
function renderPenilaianTable() {
  const tbody = document.getElementById('penilaianTableBody');
  if (!tbody) return;

  const { list } = calculateSantriStats();
  if (list.length === 0) {
    tbody.innerHTML = `<tr><td colspan="14" class="text-center" style="padding: 30px; color: var(--dark-500);">Belum ada data santri untuk dinilai.</td></tr>`;
    return;
  }

  tbody.innerHTML = list.map((s, idx) => {
    return `
      <tr data-santri-id="${s.id}">
        <td class="text-center font-bold">${idx + 1}</td>
        <td>
          <div style="font-weight: 700; color: var(--dark-900);">${escapeHtml(s.nama)}</div>
          <div style="font-size: 0.75rem; color: var(--dark-500);">${escapeHtml(s.rtq_kode || s.rtq_nama)} | No. Ujian: ${escapeHtml(s.no_ujian || '-')}</div>
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.ghorib}" onchange="updateLiveScore(${s.id}, 'ghorib', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.tajwid}" onchange="updateLiveScore(${s.id}, 'tajwid', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.tartil}" onchange="updateLiveScore(${s.id}, 'tartil', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.surat}" onchange="updateLiveScore(${s.id}, 'surat', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.doa}" onchange="updateLiveScore(${s.id}, 'doa', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.tauhid}" onchange="updateLiveScore(${s.id}, 'tauhid', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.wudlu}" onchange="updateLiveScore(${s.id}, 'wudlu', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.sholat}" onchange="updateLiveScore(${s.id}, 'sholat', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.1" min="0" max="100" class="score-input" value="${s.kitabah}" onchange="updateLiveScore(${s.id}, 'kitabah', this.value)" />
        </td>
        <td class="text-center font-bold" style="color: var(--primary-900); font-size: 0.95rem;">
          ${s.totalNilai}
        </td>
        <td class="text-center font-bold">
          ${s.rataRata}
        </td>
        <td class="text-center">
          ${s.isLulus ? '<span class="badge badge-success">Lulus</span>' : '<span class="badge badge-danger">Belum Lulus</span>'}
        </td>
      </tr>
    `;
  }).join('');
}

async function updateLiveScore(santriId, subject, val) {
  let numVal = parseFloat(val);
  if (isNaN(numVal) || numVal < 0) numVal = 0;

  try {
    const res = await fetch('api.php?action=update_nilai', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        santri_id: santriId,
        subject: subject,
        value: numVal
      })
    });
    const result = await res.json();
    if (result.status === 'success') {
      const s = appData.santri.find(x => x.id == santriId);
      if (s) {
        s[subject] = numVal;
        s.total = result.total;
        s.rata_rata = result.rata_rata;
        s.is_lulus = result.is_lulus;
      }
      renderPenilaianTable();
      renderRekapTable();
      renderDashboard();
    }
  } catch (err) {
    console.error('Error saving score to database:', err);
  }
}

// ==========================================
// 4. REKAPITULASI & RANKING
// ==========================================
function renderRekapTable() {
  const tbody = document.getElementById('rekapTableBody');
  if (!tbody) return;

  const { sorted } = calculateSantriStats();
  if (sorted.length === 0) {
    tbody.innerHTML = `<tr><td colspan="15" class="text-center" style="padding: 30px; color: var(--dark-500);">Belum ada data rekap.</td></tr>`;
    return;
  }

  tbody.innerHTML = sorted.map((s, idx) => {
    const rankBadge = s.rank === 1 ? '🥇 1' : s.rank === 2 ? '🥈 2' : s.rank === 3 ? '🥉 3' : s.rank;
    return `
      <tr>
        <td class="text-center font-bold">${idx + 1}</td>
        <td><strong>${escapeHtml(s.nama)}</strong></td>
        <td>${escapeHtml(s.rtq_nama || '-')}</td>
        <td class="text-center">${s.ghorib}</td>
        <td class="text-center">${s.tajwid}</td>
        <td class="text-center">${s.tartil}</td>
        <td class="text-center">${s.surat}</td>
        <td class="text-center">${s.doa}</td>
        <td class="text-center">${s.tauhid}</td>
        <td class="text-center">${s.wudlu}</td>
        <td class="text-center">${s.sholat}</td>
        <td class="text-center">${s.kitabah}</td>
        <td class="text-center font-bold" style="color: var(--primary-900); font-size: 1rem;">${s.totalNilai}</td>
        <td class="text-center font-bold">${s.rataRata}</td>
        <td class="text-center">
          <span class="badge ${s.rank <= 3 ? 'badge-gold' : 'badge-primary'}">${rankBadge}</span>
        </td>
      </tr>
    `;
  }).join('');
}

// Export Rekap to Excel
function exportRekapExcel() {
  if (typeof XLSX === 'undefined') {
    SwalAlert.error('Pemberitahuan', 'Library ekspor Excel (SheetJS) belum siap.');
    return;
  }

  const { sorted } = calculateSantriStats();
  const dataRows = sorted.map((s, idx) => ({
    'No': idx + 1,
    'No Ujian': s.no_ujian || '',
    'Nama Santri': s.nama || '',
    'Asal Unit RTQ': s.rtq_nama || '',
    'Tahun Pelajaran': s.tahun_pelajaran || '',
    'Ghoroibul Quran': s.ghorib,
    'Ilmu Tajwid': s.tajwid,
    'Tartil & Fashohah': s.tartil,
    'Hafalan Surat Pendek': s.surat,
    'Hafalan Doa Harian': s.doa,
    'Tauhid Dasar': s.tauhid,
    'Praktik Wudlu': s.wudlu,
    'Praktik Sholat': s.sholat,
    'Kitabah': s.kitabah,
    'Total Nilai': s.totalNilai,
    'Rata-Rata': s.rataRata,
    'Peringkat': s.rank,
    'Status': s.isLulus ? 'LULUS' : 'TIDAK LULUS'
  }));

  const ws = XLSX.utils.json_to_sheet(dataRows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, "Rekap Munaqosyah");
  XLSX.writeFile(wb, `Rekap_Munaqosyah_Yanbua_${new Date().toISOString().split('T')[0]}.xlsx`);
}

// Print Rekapitulasi Landscape
function printRekapitulasiLandscape() {
  const { sorted } = calculateSantriStats();
  const lbg = appData.lembaga;

  const win = window.open('', '_blank');
  win.document.write(`
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <title>Rekapitulasi Nilai Munaqosyah - Yanbu'a Lampung Barat</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #111; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h2 { margin: 0 0 4px; font-size: 15pt; text-transform: uppercase; }
        .header h3 { margin: 0; font-size: 11pt; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 14px; }
        th, td { border: 1px solid #333; padding: 5px 6px; }
        th { background: #eef4f0; text-align: center; }
        td.center { text-align: center; }
        td.bold { font-weight: bold; }
        .footer { display: flex; justify-content: flex-end; margin-top: 25px; font-size: 10pt; }
        .sig { text-align: center; width: 220px; }
        @page { size: A4 landscape; margin: 10mm; }
        @media print { .no-print { display: none; } }
      </style>
    </head>
    <body>
      <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; font-size: 11pt; cursor: pointer; background: #0f5132; color: #fff; border: none; border-radius: 4px;">🖨️ Cetak Dokumen</button>
      </div>
      <div class="header">
        <h2>DAFTAR NILAI UJIAN AKHIR MUNAQOSYAH</h2>
        <h3>${escapeHtml(lbg.nama || "LAJNAH MUROQOBAH YANBU'A")} - ${escapeHtml(lbg.wilayah || "LAMPUNG BARAT")}</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>No Ujian</th>
            <th>Nama Santri</th>
            <th>Asal Unit RTQ</th>
            <th>Ghorib</th>
            <th>Tajwid</th>
            <th>Tartil</th>
            <th>Surat</th>
            <th>Do'a</th>
            <th>Tauhid</th>
            <th>Wudlu</th>
            <th>Sholat</th>
            <th>Kitabah</th>
            <th>Total</th>
            <th>Rata</th>
            <th>Rank</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          ${sorted.map((s, idx) => `
            <tr>
              <td class="center">${idx + 1}</td>
              <td class="center">${escapeHtml(s.no_ujian || '-')}</td>
              <td class="bold">${escapeHtml(s.nama)}</td>
              <td>${escapeHtml(s.rtq_nama || '-')}</td>
              <td class="center">${s.ghorib}</td>
              <td class="center">${s.tajwid}</td>
              <td class="center">${s.tartil}</td>
              <td class="center">${s.surat}</td>
              <td class="center">${s.doa}</td>
              <td class="center">${s.tauhid}</td>
              <td class="center">${s.wudlu}</td>
              <td class="center">${s.sholat}</td>
              <td class="center">${s.kitabah}</td>
              <td class="center bold">${s.totalNilai}</td>
              <td class="center bold">${s.rataRata}</td>
              <td class="center bold">${s.rank}</td>
              <td class="center ${s.isLulus ? '' : 'style="color:red;"'}">${s.isLulus ? 'LULUS' : 'TIDAK LULUS'}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
      <div class="footer">
        <div class="sig">
          <div>Lampung Barat, September 2025</div>
          <div style="font-weight: bold; margin-top: 4px;">Ketua Panitia Ujian</div>
          <div style="height: 60px;"></div>
          <div style="font-weight: bold; text-decoration: underline;">${escapeHtml(lbg.ketua_panitia || 'A. Muslih Abdurrahman')}</div>
        </div>
      </div>
    </body>
    </html>
  `);
  win.document.close();
}

// ==========================================
// 5. CETAK SYAHADAH DENGAN LOGO PNG ASLI
// ==========================================
function setupCetakTab() {
  const select = document.getElementById('cetakSantriSelect');
  if (!select) return;

  const cur = select.value;
  select.innerHTML = '<option value="">-- Pilih Santri untuk Dicetak --</option>';
  appData.santri.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id;
    opt.textContent = `${s.no_ujian || '-'} - ${s.nama} (${s.rtq_kode || s.rtq_nama})`;
    select.appendChild(opt);
  });

  if (cur && appData.santri.some(s => s.id == cur)) {
    select.value = cur;
  } else if (appData.santri.length > 0) {
    select.value = appData.santri[0].id;
  }

  generateCetakPreview();
}

function generateCetakPreview() {
  const select = document.getElementById('cetakSantriSelect');
  const docType = document.getElementById('cetakDocTypeSelect')?.value || 'both';
  const showFrame = document.getElementById('chkShowFrame') ? document.getElementById('chkShowFrame').checked : true;
  const showStamp = document.getElementById('chkShowStamp') ? document.getElementById('chkShowStamp').checked : true;
  const container = document.getElementById('cetakPreviewArea');
  if (!container) return;

  const santriId = parseInt(select?.value, 10);
  if (!santriId) {
    container.innerHTML = '<div style="color: #fff; text-align: center; padding: 40px; font-weight: 600;">Pilih santri terlebih dahulu untuk menampilkan pratinjau cetak.</div>';
    return;
  }

  const { list } = calculateSantriStats();
  const s = list.find(x => x.id == santriId);
  if (!s) return;

  let html = '';
  if (docType === 'depan' || docType === 'both') {
    html += renderSyahadahDepan(s, { showFrame, showStamp });
  }
  if (docType === 'belakang' || docType === 'both') {
    html += renderSyahadahBelakang(s, { showFrame, showStamp });
  }
  if (docType === 'formulir') {
    html += renderFormulirSantri(s);
  }

  container.innerHTML = html;
}

function previewSingleCetak(santriId, docType) {
  switchTab('tab-cetak');
  const select = document.getElementById('cetakSantriSelect');
  const docSelect = document.getElementById('cetakDocTypeSelect');
  if (select) select.value = santriId;
  if (docSelect) docSelect.value = docType;
  generateCetakPreview();
}

// -------------------------------------------------------------
// PURE CODE VECTOR SVG ISLAMIC CERTIFICATE FRAME
// -------------------------------------------------------------
let _cachedSyahadahVectorSvg = null;
function getSyahadahVectorFrameSvg() {
  if (_cachedSyahadahVectorSvg) return _cachedSyahadahVectorSvg;

  _cachedSyahadahVectorSvg = `
  <svg class="syahadah-vector-frame" viewBox="0 0 1000 1414" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <!-- Metallic Gold Gradient -->
      <linearGradient id="goldL" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#946b19"/>
        <stop offset="18%" stop-color="#d4af37"/>
        <stop offset="38%" stop-color="#fae79d"/>
        <stop offset="52%" stop-color="#fff8d6"/>
        <stop offset="70%" stop-color="#d4af37"/>
        <stop offset="88%" stop-color="#aa771c"/>
        <stop offset="100%" stop-color="#7a520e"/>
      </linearGradient>

      <!-- Royal Islamic Emerald Green Gradient -->
      <linearGradient id="emeraldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#042016"/>
        <stop offset="30%" stop-color="#0b3d2c"/>
        <stop offset="50%" stop-color="#145d44"/>
        <stop offset="70%" stop-color="#0b3d2c"/>
        <stop offset="100%" stop-color="#031810"/>
      </linearGradient>

      <!-- Gold Radial Glow for Rosettes & Medallions -->
      <radialGradient id="goldRadial" cx="50%" cy="50%" r="50%">
        <stop offset="0%" stop-color="#fff9d4"/>
        <stop offset="35%" stop-color="#ebd06c"/>
        <stop offset="75%" stop-color="#b8860b"/>
        <stop offset="100%" stop-color="#6e4c08"/>
      </radialGradient>

      <!-- 8-Pointed Star (Rub el Hizb / Khatam Sulaiman) -->
      <g id="star8">
        <polygon points="0,-18 12.7,-12.7 18,0 12.7,12.7 0,18 -12.7,12.7 -18,0 -12.7,-12.7" fill="url(#goldL)" stroke="#6e4c08" stroke-width="0.8"/>
        <polygon points="0,-12 8.5,-8.5 12,0 8.5,8.5 0,12 -8.5,8.5 -12,0 -8.5,-8.5" fill="#0b3d2c" stroke="url(#goldL)" stroke-width="0.8"/>
        <circle cx="0" cy="0" r="4.2" fill="url(#goldRadial)" stroke="#80560a" stroke-width="0.6"/>
      </g>

      <!-- Repeating Geometric Border Pattern (Horizontal) -->
      <pattern id="islamicChainH" width="24" height="12" patternUnits="userSpaceOnUse">
        <path d="M 0 6 L 6 0 L 12 6 L 6 12 Z M 12 6 L 18 0 L 24 6 L 18 12 Z" fill="none" stroke="url(#goldL)" stroke-width="0.85"/>
        <circle cx="6" cy="6" r="1.5" fill="#d4af37"/>
        <circle cx="18" cy="6" r="1.5" fill="#d4af37"/>
        <polygon points="12,2 14.5,6 12,10 9.5,6" fill="#0c4331"/>
      </pattern>

      <!-- Repeating Geometric Border Pattern (Vertical) -->
      <pattern id="islamicChainV" width="12" height="24" patternUnits="userSpaceOnUse">
        <path d="M 6 0 L 0 6 L 6 12 L 12 6 Z M 6 12 L 0 18 L 6 24 L 12 18 Z" fill="none" stroke="url(#goldL)" stroke-width="0.85"/>
        <circle cx="6" cy="6" r="1.5" fill="#d4af37"/>
        <circle cx="6" cy="18" r="1.5" fill="#d4af37"/>
        <polygon points="2,12 6,14.5 10,12 6,9.5" fill="#0c4331"/>
      </pattern>

      <!-- Intricate Islamic Corner Bracket Motif (Top-Left) -->
      <g id="cornerOrnament">
        <!-- Translucent Emerald Corner Fan Backdrop -->
        <path d="M 18 18 L 125 18 C 110 32, 95 48, 85 70 C 75 92, 70 110, 70 125 L 18 125 Z" fill="url(#emeraldGrad)" opacity="0.18"/>
        
        <!-- Scalloped Arabesque Arcs with Gold Pinstriping -->
        <path d="M 28 88 C 38 88, 50 78, 50 66 C 50 54, 62 46, 74 46 C 86 46, 94 36, 94 26" fill="none" stroke="url(#goldL)" stroke-width="2.6" stroke-linecap="round"/>
        <path d="M 28 118 C 50 118, 68 100, 68 78 C 68 56, 86 42, 118 28" fill="none" stroke="url(#goldL)" stroke-width="1.8" stroke-dasharray="4 2"/>

        <!-- Flowing Arabesque Tendrils & Botanical Curls -->
        <path d="M 28 48 C 38 48, 48 38, 48 28" fill="none" stroke="url(#goldL)" stroke-width="2"/>
        <path d="M 48 28 C 54 22, 54 34, 48 28 Z" fill="url(#goldRadial)"/>
        
        <path d="M 72 88 C 82 78, 90 70, 102 70 C 114 70, 122 62, 122 48" fill="none" stroke="url(#goldL)" stroke-width="1.5"/>
        <circle cx="122" cy="48" r="3.2" fill="url(#goldRadial)"/>
        
        <path d="M 88 72 C 78 82, 70 90, 70 102 C 70 114, 62 122, 48 122" fill="none" stroke="url(#goldL)" stroke-width="1.5"/>
        <circle cx="48" cy="122" r="3.2" fill="url(#goldRadial)"/>

        <!-- Prominent Corner 8-Pointed Star -->
        <g transform="translate(64, 64) scale(1.65)">
          <use href="#star8"/>
        </g>
        
        <!-- Satellite Stars -->
        <g transform="translate(122, 30) scale(0.75)">
          <use href="#star8"/>
        </g>
        <g transform="translate(30, 122) scale(0.75)">
          <use href="#star8"/>
        </g>
      </g>

      <!-- Top Center Mihrab Arch Crown Crest -->
      <g id="topCrest">
        <path d="M -115 0 C -95 -16, -65 -28, -32 -36 C -14 -42, 0 -54, 0 -54 C 0 -54, 14 -42, 32 -36 C 65 -28, 95 -16, 115 0 Z" fill="url(#emeraldGrad)" stroke="url(#goldL)" stroke-width="2.6"/>
        <path d="M -94 -3 C -78 -16, -52 -24, -26 -30 C -11 -34, 0 -45, 0 -45 C 0 -45, 11 -34, 26 -30 C 52 -24, 78 -16, 94 -3" fill="none" stroke="url(#goldL)" stroke-width="1.3"/>
        <g transform="translate(0, -25) scale(0.95)">
          <use href="#star8"/>
        </g>
        <polygon points="0,-60 -4,-53 0,-49 4,-53" fill="url(#goldL)"/>
        <circle cx="0" cy="-62" r="2.8" fill="url(#goldRadial)"/>
      </g>

      <!-- Bottom Center Medallion Crest -->
      <g id="bottomCrest">
        <path d="M -105 0 C -85 14, -58 24, -28 30 C -11 34, 0 45, 0 45 C 0 45, 11 34, 28 30 C 58 24, 85 14, 105 0 Z" fill="url(#emeraldGrad)" stroke="url(#goldL)" stroke-width="2.6"/>
        <path d="M -85 3 C -68 14, -48 20, -22 25 C -9 28, 0 37, 0 37 C 0 37, 9 28, 22 25 C 48 20, 68 14, 85 3" fill="none" stroke="url(#goldL)" stroke-width="1.3"/>
        <g transform="translate(0, 20) scale(0.95)">
          <use href="#star8"/>
        </g>
        <circle cx="0" cy="52" r="2.8" fill="url(#goldRadial)"/>
      </g>

      <!-- Side Midpoint Rosettes -->
      <g id="sideMedallion">
        <path d="M 0 -38 C 11 -22, 17 -11, 17 0 C 17 11, 11 22, 0 38 C -11 22, -17 11, -17 0 C -17 -11, -11 -22, 0 -38 Z" fill="url(#emeraldGrad)" stroke="url(#goldL)" stroke-width="1.8"/>
        <g transform="scale(0.88)">
          <use href="#star8"/>
        </g>
      </g>
    </defs>

    <!-- Base Frame Background -->
    <rect x="0" y="0" width="1000" height="1414" fill="none"/>

    <!-- Layer 1: Outermost Gold Hairline -->
    <rect x="14" y="14" width="972" height="1386" rx="5" fill="none" stroke="url(#goldL)" stroke-width="2.2"/>

    <!-- Layer 2: Royal Emerald Wide Band (14px) with Center Gold Dash -->
    <rect x="23" y="23" width="954" height="1368" rx="4" fill="none" stroke="url(#emeraldGrad)" stroke-width="14"/>
    <rect x="23" y="23" width="954" height="1368" rx="4" fill="none" stroke="url(#goldL)" stroke-width="1.2" stroke-dasharray="10 5"/>

    <!-- Layer 3: Gold Separator Line -->
    <rect x="34" y="34" width="932" height="1346" rx="3" fill="none" stroke="url(#goldL)" stroke-width="2.2"/>

    <!-- Layer 4: Islamic Geometric Frieze Pattern Bands (Top, Bottom, Left, Right) -->
    <rect x="124" y="38" width="752" height="12" fill="url(#islamicChainH)"/>
    <rect x="124" y="1364" width="752" height="12" fill="url(#islamicChainH)"/>
    <rect x="38" y="124" width="12" height="1166" fill="url(#islamicChainV)"/>
    <rect x="950" y="124" width="12" height="1166" fill="url(#islamicChainV)"/>

    <!-- Layer 5: Inner Gold Border with Rounded Edges -->
    <rect x="54" y="54" width="892" height="1306" rx="3" fill="none" stroke="url(#goldL)" stroke-width="2.5"/>

    <!-- Layer 6: Deep Emerald Inner Hairline -->
    <rect x="60" y="60" width="880" height="1294" rx="2" fill="none" stroke="#0d4734" stroke-width="1.6"/>

    <!-- Layer 7: Subtle Inset Dotted Gold Line -->
    <rect x="66" y="66" width="868" height="1282" fill="none" stroke="url(#goldL)" stroke-width="0.9" stroke-dasharray="4 3"/>

    <!-- 4 Corner Ornaments -->
    <g transform="translate(0, 0)">
      <use href="#cornerOrnament"/>
    </g>
    <g transform="translate(1000, 0) scale(-1, 1)">
      <use href="#cornerOrnament"/>
    </g>
    <g transform="translate(0, 1414) scale(1, -1)">
      <use href="#cornerOrnament"/>
    </g>
    <g transform="translate(1000, 1414) scale(-1, -1)">
      <use href="#cornerOrnament"/>
    </g>

    <!-- Top & Bottom Crests -->
    <g transform="translate(500, 54)">
      <use href="#topCrest"/>
    </g>
    <g transform="translate(500, 1360)">
      <use href="#bottomCrest"/>
    </g>

    <!-- Left & Right Midpoint Rosettes -->
    <g transform="translate(44, 707)">
      <use href="#sideMedallion"/>
    </g>
    <g transform="translate(956, 707)">
      <use href="#sideMedallion"/>
    </g>
  </svg>
  `;

  return _cachedSyahadahVectorSvg;
}

// -------------------------------------------------------------
// 1. SYAHADAH HALAMAN DEPAN (Replikasi Otentik syahadah.pdf)
// -------------------------------------------------------------
function renderSyahadahDepan(s, opts = {}) {
  const showFrame = opts.showFrame !== undefined ? opts.showFrame : (document.getElementById('chkShowFrame')?.checked ?? true);
  const showStamp = opts.showStamp !== undefined ? opts.showStamp : (document.getElementById('chkShowStamp')?.checked ?? true);
  const lbg = appData.lembaga || {};
  const binWord = s.gender === 'P' ? 'Binti' : 'Bin';
  const ayahNama = s.ayah || '-';
  const noSyahadah = s.no_syahadah || 'LMY-LB/RTQ-U1/001/09/10/2025';

  return `
    <div class="a4-page syahadah-page-depan ${showFrame ? 'has-frame' : 'no-frame'}">
      ${showFrame ? `<div class="syahadah-frame-bg">${getSyahadahVectorFrameSvg()}</div>` : ''}
      
      <div class="syahadah-watermark-center">
        <img src="assets/images/logo-yanbua.png" alt="Yanbua Watermark" />
      </div>

      <div class="syahadah-inner-content">
        <!-- 1. Arabic Calligraphy Header (وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا) -->
        <div class="syahadah-arabic-calligraphy">
          <img src="assets/images/arabic_header_syahadah.png" alt="وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا" />
        </div>

        <!-- 2. Main Title -->
        <h1 class="syahadah-main-title">SYAHADAH</h1>

        <!-- 3. Sub-headers Lembaga Sesuai Standar Yanbu'a -->
        <div class="syahadah-instansi-headers">
          <div class="syahadah-h-sub">ROUDLOTU TARBIYATIL QUR'AN</div>
          <div class="syahadah-h-sub">${escapeHtml(lbg.nama || "LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR'AN")}</div>
          <div class="syahadah-h-sub">${escapeHtml(lbg.metode || "THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR'AN YANBU'A")}</div>
          <div class="syahadah-h-sub">${escapeHtml(lbg.wilayah || "KABUPATEN LAMPUNG BARAT")}</div>
        </div>

        <!-- 4. Nomor Syahadah & Ribbon Subtitle -->
        <div class="syahadah-nomor-wrap">
          <div class="nomor-title">Nomor :</div>
          <div class="nomor-value">${escapeHtml(noSyahadah)}</div>
          <div class="nomor-ribbon-sub">THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR'AN</div>
        </div>

        <!-- 5. Pembuka Narasi -->
        <div class="syahadah-narasi-opening">
          Setelah mengadakan ujian akhir RTQ tingkat dasar pada tanggal <strong>${escapeHtml(s.tanggal_h || "14 Rabi'ul Awwal 1447 H")} / ${escapeHtml(s.tanggal_m || "07 September 2025 M")}</strong> yang diselenggarakan di <strong>${escapeHtml(s.rtq_nama || "RTQ UTSMANIYYAH I")}</strong> ${escapeHtml(s.rtq_alamat ? s.rtq_alamat : "")}, maka kami menyatakan bahwa anak yang bernama :
        </div>

        <!-- 6. Nama Santri (Tampil Menonjol Sesuai Syahadah PDF) -->
        <div class="syahadah-santri-name-box">
          ${escapeHtml(s.nama)}
        </div>

        <!-- 7. Biodata Santri -->
        <table class="syahadah-bio-table">
          <tr>
            <td class="bio-lbl">${binWord}</td>
            <td class="bio-col">:</td>
            <td class="bio-val">${escapeHtml(ayahNama)}</td>
          </tr>
          <tr>
            <td class="bio-lbl">Tempat / Tanggal Lahir</td>
            <td class="bio-col">:</td>
            <td class="bio-val">${escapeHtml(s.ttl || '-')}</td>
          </tr>
          <tr>
            <td class="bio-lbl">No Induk Santri</td>
            <td class="bio-col">:</td>
            <td class="bio-val">${escapeHtml(s.no_induk || '-')}</td>
          </tr>
          <tr>
            <td class="bio-lbl">Alamat</td>
            <td class="bio-col">:</td>
            <td class="bio-val">${escapeHtml(s.alamat || '-')}</td>
          </tr>
        </table>

        <!-- 8. Pernyataan Kelulusan -->
        <div class="syahadah-narasi-closing">
          Dinyatakan <strong>Khotam dan Lulus</strong> tingkat dasar di lembaga Roudlotu Tarbiyatil Qur'an dan sebagai bukti kepadanya diberikan <strong>Syahadah</strong>. Semoga Allah memberinya ilmu yang bermanfaat. Amin.
        </div>

        <!-- 9. Footer: Pasfoto & TTD Pimpinan Lajnah -->
        <div class="syahadah-bottom-grid">
          <div class="syahadah-photo-container">
            <div class="syahadah-photo-box">
              <span class="photo-text">Foto 3 x 4</span>
            </div>
          </div>

          <div class="syahadah-sig-container">
            <div class="sig-date">${escapeHtml(s.titimangsa || "Lampung Barat, 21 September 2025")}</div>
            <div class="sig-role">Lajnah Muroqobah Yanbu'a</div>
            <div class="sig-stamp-space">
              ${showStamp ? `
                <img src="assets/images/stempel_ttd_lajnah.png" class="stamp-lajnah-img" alt="Stempel & TTD" />
              ` : `
                <div class="sig-empty-space"></div>
              `}
            </div>
            <div class="sig-name"><u><strong>${escapeHtml(lbg.pimpinan_lajnah || "SYA'BUL MUBAROK")}</strong></u></div>
          </div>
        </div>
      </div>
    </div>
  `;
}

// -------------------------------------------------------------
// 2. SYAHADAH HALAMAN BELAKANG (Transkrip Nilai Otentik)
// -------------------------------------------------------------
function renderSyahadahBelakang(s, opts = {}) {
  const showFrame = opts.showFrame !== undefined ? opts.showFrame : (document.getElementById('chkShowFrame')?.checked ?? true);
  const showStamp = opts.showStamp !== undefined ? opts.showStamp : (document.getElementById('chkShowStamp')?.checked ?? true);
  const lbg = appData.lembaga || {};
  
  const rows = [
    { no: 1, materi: 'Tartil Fasohah', val: s.tartil },
    { no: 2, materi: 'Ghoroibul Qur\'an', val: s.ghorib },
    { no: 3, materi: 'Ilmu Tajwid', val: s.tajwid },
    { no: 4, materi: 'Tauhid', val: s.tauhid },
    { no: 5, materi: 'Praktik Wudlu', val: s.wudlu },
    { no: 6, materi: 'Praktik Sholat', val: s.sholat },
    { no: 7, materi: 'Hafalan Surat Pendek', val: s.surat },
    { no: 8, materi: 'Hafalan Do\'a Harian', val: s.doa },
    { no: 9, materi: 'Kitabah', val: s.kitabah }
  ];

  const total = s.totalNilai;

  return `
    <div class="a4-page syahadah-page-belakang ${showFrame ? 'has-frame' : 'no-frame'}">
      ${showFrame ? `<div class="syahadah-frame-bg">${getSyahadahVectorFrameSvg()}</div>` : ''}

      <div class="syahadah-watermark-center">
        <img src="assets/images/logo-yanbua.png" alt="Yanbua Watermark" />
      </div>

      <div class="syahadah-inner-content">
        <!-- 1. Header Transkrip Resmi -->
        <div class="transkrip-header-official">
          <h2>DAFTAR NILAI UJIAN AKHIR RTQ</h2>
          <h3>TAHUN PELAJARAN ${escapeHtml(s.tahun_pelajaran || "2025 M / 1447 H")}</h3>
        </div>

        <!-- 2. Biodata Santri Nilai -->
        <table class="transkrip-bio-table">
          <tr>
            <td class="tb-lbl">Nama</td>
            <td class="tb-col">:</td>
            <td class="tb-val tb-val-name">${escapeHtml(s.nama)}</td>
          </tr>
          <tr>
            <td class="tb-lbl">Tempat, Tanggal Lahir</td>
            <td class="tb-col">:</td>
            <td class="tb-val">${escapeHtml(s.ttl || '-')}</td>
          </tr>
          <tr>
            <td class="tb-lbl">Asal RTQ</td>
            <td class="tb-col">:</td>
            <td class="tb-val">${escapeHtml(s.rtq_nama || 'RTQ Utsmaniyyah')}</td>
          </tr>
          <tr>
            <td class="tb-lbl">Nomor Induk Santri</td>
            <td class="tb-col">:</td>
            <td class="tb-val">${escapeHtml(s.no_induk || '-')}</td>
          </tr>
          <tr>
            <td class="tb-lbl">Nomor Ujian</td>
            <td class="tb-col">:</td>
            <td class="tb-val">${escapeHtml(s.no_ujian || '-')}</td>
          </tr>
        </table>

        <!-- 3. Tabel Nilai 9 Mata Pelajaran Sesuai Syahadah PDF -->
        <table class="transkrip-grades-table">
          <thead>
            <tr>
              <th rowspan="2" class="th-no">NO</th>
              <th rowspan="2" class="th-materi">MATA UJIAN</th>
              <th colspan="2" class="th-nilai-group">NILAI</th>
            </tr>
            <tr>
              <th class="th-angka">ANGKA</th>
              <th class="th-huruf">HURUF</th>
            </tr>
          </thead>
          <tbody>
            ${rows.map(r => `
              <tr>
                <td class="td-no">${r.no}</td>
                <td class="td-materi">${r.materi}</td>
                <td class="td-angka">${r.val}</td>
                <td class="td-huruf">${angkaKeTerbilang(r.val)}</td>
              </tr>
            `).join('')}
            <tr class="tr-total">
              <td colspan="2" class="td-total-lbl">JUMLAH</td>
              <td class="td-angka td-total-val">${total}</td>
              <td class="td-huruf td-total-terbilang">${angkaKeTerbilang(total)}</td>
            </tr>
          </tbody>
        </table>

        <!-- 4. Signatures Transkrip (Kepala RTQ & Ketua Tim Penguji) -->
        <div class="transkrip-footer-block">
          <div class="transkrip-date-line">
            ${escapeHtml(s.titimangsa || "Tambak Jaya, 21 September 2025")}
          </div>

          <div class="transkrip-sigs-cols">
            <!-- Kepala RTQ -->
            <div class="sig-col-box">
              <div class="sig-col-title">Kepala ${escapeHtml(s.rtq_nama || "RTQ Utsmaniyah")}</div>
              <div class="sig-stamp-space">
                ${showStamp ? `
                  <img src="assets/images/stempel_ttd_kepala_rtq.png" class="stamp-transkrip-img" alt="Stempel Kepala RTQ" />
                ` : `
                  <div class="sig-empty-space"></div>
                `}
              </div>
              <div class="sig-col-name"><u><strong>${escapeHtml(s.kepala_rtq || "IKHWANUL KIROM")}</strong></u></div>
            </div>

            <!-- Ketua Tim Penguji -->
            <div class="sig-col-box">
              <div class="sig-col-title">Ketua Tim Penguji</div>
              <div class="sig-stamp-space">
                ${showStamp ? `
                  <img src="assets/images/stempel_ttd_penguji.png" class="stamp-transkrip-img" alt="Stempel Tim Penguji" />
                ` : `
                  <div class="sig-empty-space"></div>
                `}
              </div>
              <div class="sig-col-name"><u><strong>${escapeHtml(s.ketua_penguji || "A. MUSLIH ABDURROHMAN")}</strong></u></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

// Formulir Santri Template
function renderFormulirSantri(s) {
  return `
    <div class="a4-page" style="padding: 18mm 20mm;">
      <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
        <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">FORMULIR UJIAN AKHIR RTQ</h2>
        <h3 style="font-size: 11pt; font-weight: normal;">THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR'AN YANBU'A</h3>
        <p style="font-size: 10pt; font-weight: bold;">PERIODE ${escapeHtml(s.tahun_pelajaran || "2025 M / 1447 H")}</p>
      </div>

      <table style="width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 30px;">
        <tr><td style="width: 32%; padding: 7px 4px;">Nama Lengkap</td><td style="width: 3%;">:</td><td style="font-weight: bold; text-transform: uppercase;">${escapeHtml(s.nama)}</td></tr>
        <tr><td style="padding: 7px 4px;">Jenis Kelamin</td><td>:</td><td>${s.gender === 'P' ? 'Perempuan' : 'Laki-laki'}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Ayah Kandung</td><td>:</td><td>${escapeHtml(s.ayah || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Ibu Kandung</td><td>:</td><td>${escapeHtml(s.ibu || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Tempat, Tanggal Lahir</td><td>:</td><td>${escapeHtml(s.ttl || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Alamat Lengkap</td><td>:</td><td>${escapeHtml(s.alamat || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nomor Induk Santri</td><td>:</td><td>${escapeHtml(s.no_induk || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nomor Ujian</td><td>:</td><td>${escapeHtml(s.no_ujian || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Unit RTQ</td><td>:</td><td>${escapeHtml(s.rtq_nama || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Alamat RTQ</td><td>:</td><td>${escapeHtml(s.rtq_alamat || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">No. WhatsApp RTQ</td><td>:</td><td>${escapeHtml(s.rtq_wa || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Kepala RTQ</td><td>:</td><td>${escapeHtml(s.kepala_rtq || '-')}</td></tr>
      </table>

      <div style="display: flex; justify-content: space-between; margin-top: 40px; font-size: 11pt;">
        <div style="text-align: center; width: 200px;">
          <div>Orang Tua / Wali Santri</div>
          <div style="height: 70px;"></div>
          <div style="border-bottom: 1px dashed #333;"></div>
        </div>
        <div style="text-align: center; width: 220px;">
          <div>${escapeHtml(s.titimangsa || 'Tambak Jaya, September 2025')}</div>
          <div>Kepala ${escapeHtml(s.rtq_nama || '-')}</div>
          <div style="height: 55px;"></div>
          <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">${escapeHtml(s.kepala_rtq || '-')}</div>
        </div>
      </div>
    </div>
  `;
}

// Batch Print All
function printAllSyahadah(docType = 'both') {
  const container = document.getElementById('cetakPreviewArea');
  if (!container) return;

  const showFrame = document.getElementById('chkShowFrame') ? document.getElementById('chkShowFrame').checked : true;
  const showStamp = document.getElementById('chkShowStamp') ? document.getElementById('chkShowStamp').checked : true;

  const { list } = calculateSantriStats();
  if (list.length === 0) {
    SwalAlert.warning('Pemberitahuan', 'Tidak ada data santri untuk dicetak.');
    return;
  }

  let html = '';
  list.forEach(s => {
    if (docType === 'depan' || docType === 'both') html += renderSyahadahDepan(s, { showFrame, showStamp });
    if (docType === 'belakang' || docType === 'both') html += renderSyahadahBelakang(s, { showFrame, showStamp });
    if (docType === 'formulir') html += renderFormulirSantri(s);
  });

  container.innerHTML = html;
  setTimeout(() => { window.print(); }, 200);
}

// ==========================================
// 6. PENGATURAN MULTI-RTQ & PERIODE (CRUD)
// ==========================================
function populateConfigTab() {
  const lbg = appData.lembaga || {};
  document.getElementById('cfgLembaga').value = lbg.nama || '';
  document.getElementById('cfgMetode').value = lbg.metode || '';
  document.getElementById('cfgWilayah').value = lbg.wilayah || '';
  document.getElementById('cfgTingkat').value = lbg.tingkat || '';
  document.getElementById('cfgPimpinanLajnah').value = lbg.pimpinan_lajnah || '';
  document.getElementById('cfgKetuaPanitia').value = lbg.ketua_panitia || '';
  document.getElementById('cfgKkm').value = lbg.kkm || 60;

  const isDeveloper = (appData.currentUser && (appData.currentUser.role === 'DEVELOPER' || appData.currentUser.role === 'SUPER_ADMIN' || appData.currentUser.username === 'Maul'));

  // Tampilkan notifikasi role status
  const noticeEl = document.getElementById('cfgRoleNotice');
  if (noticeEl) {
    if (isDeveloper) {
      noticeEl.innerHTML = `
        <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; color: #065f46; font-size: 0.88rem; box-shadow: 0 2px 6px rgba(16,185,129,0.08);">
          <span style="font-size: 1.4rem; line-height: 1;">👑</span>
          <div>
            <strong>Hak Akses Developer Aktif (${escapeHtml(appData.currentUser.nama || 'Developer')}):</strong> 
            Formulir terbuka untuk diedit. Anda dapat memperbarui profil Lembaga Induk, Pimpinan Lajnah, dan Batas KKM Kelulusan di bawah.
          </div>
        </div>
      `;
    } else {
      noticeEl.innerHTML = `
        <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; color: #92400e; font-size: 0.88rem;">
          <span style="font-size: 1.4rem; line-height: 1;">🔒</span>
          <div>
            <strong>Akses Pengurus RTQ (${escapeHtml(appData.currentUser.nama || 'Pengurus')}):</strong> 
            Parameter Lembaga Induk & KKM berstatus Terkunci (Hanya Developer). Anda dapat mengelola data operasional RTQ Anda pada tabel unit di bawah.
          </div>
        </div>
      `;
    }
  }

  // Terapkan status disable/enable pada input config lembaga
  const cfgLembagaInputs = document.querySelectorAll('#configForm input');
  cfgLembagaInputs.forEach(inp => {
    inp.disabled = !isDeveloper;
    if (isDeveloper) {
      inp.removeAttribute('disabled');
      inp.style.backgroundColor = '#ffffff';
      inp.style.cursor = 'text';
    } else {
      inp.setAttribute('disabled', 'disabled');
      inp.style.backgroundColor = '#f8fafc';
      inp.style.cursor = 'not-allowed';
    }
  });

  const saveLembagaWrap = document.getElementById('btnSaveLembagaWrap');
  if (saveLembagaWrap) saveLembagaWrap.style.display = isDeveloper ? 'block' : 'none';

  const btnAddRtq = document.getElementById('btnAddRtqBtn');
  if (btnAddRtq) btnAddRtq.style.display = isDeveloper ? 'inline-flex' : 'none';

  const btnAddPeriode = document.getElementById('btnAddPeriodeBtn');
  if (btnAddPeriode) btnAddPeriode.style.display = isDeveloper ? 'inline-flex' : 'none';

  // Render Tabel RTQ
  const rtqTbody = document.getElementById('rtqListTableBody');
  if (rtqTbody) {
    rtqTbody.innerHTML = appData.daftarRtq.map(r => `
      <tr>
        <td><strong>${escapeHtml(r.kode)}</strong></td>
        <td><strong>${escapeHtml(r.nama)}</strong></td>
        <td style="font-size: 0.8rem;">${escapeHtml(r.alamat)}</td>
        <td>${escapeHtml(r.kepala_rtq)}</td>
        <td>${escapeHtml(r.ketua_penguji)}</td>
        <td>${escapeHtml(r.wa || '-')}</td>
        <td class="text-center">
          <button class="btn btn-outline btn-sm btn-icon" onclick="openEditRtqModal(${r.id})" title="Edit RTQ">✏️</button>
        </td>
      </tr>
    `).join('');
  }

  // Render Tabel Periode
  const periodeTbody = document.getElementById('periodeListTableBody');
  if (periodeTbody) {
    periodeTbody.innerHTML = appData.daftarPeriode.map(p => `
      <tr>
        <td><strong>${escapeHtml(p.tahun_pelajaran)}</strong></td>
        <td>${escapeHtml(p.tanggal_h || '-')}</td>
        <td>${escapeHtml(p.tanggal_m || '-')}</td>
        <td>${escapeHtml(p.tanggal_syahadah || '-')}</td>
        <td>${escapeHtml(p.titimangsa || '-')}</td>
        <td class="text-center">
          <button class="btn btn-outline btn-sm btn-icon" onclick="openEditPeriodeModal(${p.id})" title="Edit Periode">✏️</button>
        </td>
      </tr>
    `).join('');
  }
}

async function handleSaveConfig(e) {
  e.preventDefault();
  const payload = {
    nama: document.getElementById('cfgLembaga').value.trim(),
    metode: document.getElementById('cfgMetode').value.trim(),
    wilayah: document.getElementById('cfgWilayah').value.trim(),
    tingkat: document.getElementById('cfgTingkat').value.trim(),
    pimpinan_lajnah: document.getElementById('cfgPimpinanLajnah').value.trim(),
    ketua_panitia: document.getElementById('cfgKetuaPanitia').value.trim(),
    kkm: document.getElementById('cfgKkm').value
  };

  try {
    const res = await fetch('api.php?action=save_lembaga', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      SwalAlert.success('Berhasil Disimpan', 'Data Lembaga berhasil disimpan ke database MySQL!');
      await loadDataFromBackend();
      populateConfigTab();
    } else {
      SwalAlert.error('Gagal', result.message || 'Gagal menyimpan data lembaga.');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan', 'Gagal menyimpan data lembaga.');
  }
}

// RTQ Modal
function openAddRtqModal() {
  document.getElementById('rtqModalTitle').textContent = 'Tambah Unit RTQ Baru';
  document.getElementById('rtqForm').reset();
  document.getElementById('rtqIdInput').value = '';
  openModal('rtqModal');
}

function openEditRtqModal(id) {
  const r = appData.daftarRtq.find(x => x.id == id);
  if (!r) return;
  document.getElementById('rtqModalTitle').textContent = 'Edit Data Unit RTQ';
  document.getElementById('rtqIdInput').value = r.id;
  document.getElementById('inputRtqKode').value = r.kode;
  document.getElementById('inputRtqNamaModal').value = r.nama;
  document.getElementById('inputRtqAlamatModal').value = r.alamat;
  document.getElementById('inputRtqKepalaModal').value = r.kepala_rtq;
  document.getElementById('inputRtqPengujiModal').value = r.ketua_penguji;
  document.getElementById('inputRtqWaModal').value = r.wa || '';
  openModal('rtqModal');
}

async function handleSaveRtq(e) {
  e.preventDefault();
  const id = document.getElementById('rtqIdInput').value;
  const payload = {
    id: id || null,
    kode: document.getElementById('inputRtqKode').value.trim(),
    nama: document.getElementById('inputRtqNamaModal').value.trim(),
    alamat: document.getElementById('inputRtqAlamatModal').value.trim(),
    kepala_rtq: document.getElementById('inputRtqKepalaModal').value.trim(),
    ketua_penguji: document.getElementById('inputRtqPengujiModal').value.trim(),
    wa: document.getElementById('inputRtqWaModal').value.trim()
  };

  try {
    const res = await fetch('api.php?action=save_rtq', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      closeModal('rtqModal');
      await loadDataFromBackend();
      SwalAlert.success('Berhasil Disimpan', 'Data Unit RTQ berhasil disimpan!');
    } else {
      SwalAlert.error('Gagal', result.message || 'Gagal menyimpan unit RTQ.');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan', 'Gagal menyimpan unit RTQ.');
  }
}

// Periode Modal
function openAddPeriodeModal() {
  document.getElementById('periodeModalTitle').textContent = 'Tambah Tahun / Periode Ujian';
  document.getElementById('periodeForm').reset();
  document.getElementById('periodeIdInput').value = '';
  openModal('periodeModal');
}

function openEditPeriodeModal(id) {
  const p = appData.daftarPeriode.find(x => x.id == id);
  if (!p) return;
  document.getElementById('periodeModalTitle').textContent = 'Edit Periode Ujian';
  document.getElementById('periodeIdInput').value = p.id;
  document.getElementById('inputPeriodeTahun').value = p.tahun_pelajaran;
  document.getElementById('inputPeriodeH').value = p.tanggal_h || '';
  document.getElementById('inputPeriodeM').value = p.tanggal_m || '';
  document.getElementById('inputPeriodeSyahadah').value = p.tanggal_syahadah || '';
  document.getElementById('inputPeriodeTitimangsa').value = p.titimangsa || '';
  openModal('periodeModal');
}

async function handleSavePeriode(e) {
  e.preventDefault();
  const id = document.getElementById('periodeIdInput').value;
  const payload = {
    id: id || null,
    tahun_pelajaran: document.getElementById('inputPeriodeTahun').value.trim(),
    tanggal_h: document.getElementById('inputPeriodeH').value.trim(),
    tanggal_m: document.getElementById('inputPeriodeM').value.trim(),
    tanggal_syahadah: document.getElementById('inputPeriodeSyahadah').value.trim(),
    titimangsa: document.getElementById('inputPeriodeTitimangsa').value.trim()
  };

  try {
    const res = await fetch('api.php?action=save_periode', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      closeModal('periodeModal');
      await loadDataFromBackend();
      SwalAlert.success('Berhasil Disimpan', 'Periode ujian berhasil disimpan!');
    } else {
      SwalAlert.error('Gagal', result.message || 'Gagal menyimpan periode ujian.');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan', 'Gagal menyimpan periode ujian.');
  }
}

// Role Switcher Modal
function openRoleModal() {
  const container = document.getElementById('roleOptionList');
  if (!container) return;

  container.innerHTML = appData.daftarUsers.map(u => {
    const isCur = u.id == appData.currentUser.id;
    return `
      <div class="role-card ${isCur ? 'active' : ''}" onclick="selectUserRole(${u.id})">
        <div>
          <div class="role-title">
            <span>${u.role === 'DEVELOPER' ? '👑' : '🕌'}</span>
            <span>${escapeHtml(u.nama)}</span>
          </div>
          <div class="role-desc">Username: <code>${escapeHtml(u.username)}</code> ${u.rtq_nama ? '| ' + escapeHtml(u.rtq_nama) : '| Seluruh 3 RTQ'}</div>
        </div>
        <div>
          ${isCur ? '<span class="badge badge-success">Aktif</span>' : '<button class="btn btn-sm btn-outline">Pilih</button>'}
        </div>
      </div>
    `;
  }).join('');

  openModal('roleModal');
}

async function selectUserRole(userId) {
  try {
    const res = await fetch('api.php?action=switch_user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId })
    });
    const result = await res.json();
    if (result.status === 'success') {
      closeModal('roleModal');
      await loadDataFromBackend();
      SwalAlert.toast(`Beralih profil ke: ${result.user.nama}`);
    } else {
      SwalAlert.error('Gagal', result.message || 'Gagal mengganti profil pengurus.');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan', 'Gagal mengganti profil pengurus.');
  }
}

// Backup JSON
function backupDataJson() {
  const str = JSON.stringify(appData, null, 2);
  const blob = new Blob([str], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `backup_database_utsmaniyyah_${new Date().toISOString().split('T')[0]}.json`;
  a.click();
}

// Event Listeners
function setupEventListeners() {
  document.getElementById('loginForm')?.addEventListener('submit', handleLogin);
  document.getElementById('searchSantriInput')?.addEventListener('input', renderSantriTable);
  document.getElementById('santriForm')?.addEventListener('submit', handleSaveSantri);
  document.getElementById('rtqForm')?.addEventListener('submit', handleSaveRtq);
  document.getElementById('periodeForm')?.addEventListener('submit', handleSavePeriode);
  document.getElementById('configForm')?.addEventListener('submit', handleSaveConfig);

  document.getElementById('topbarRtqFilter')?.addEventListener('change', () => loadDataFromBackend());
  document.getElementById('topbarPeriodeFilter')?.addEventListener('change', () => loadDataFromBackend());

  document.getElementById('cetakSantriSelect')?.addEventListener('change', generateCetakPreview);
  document.getElementById('cetakDocTypeSelect')?.addEventListener('change', generateCetakPreview);
}

function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('show');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('show');
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
