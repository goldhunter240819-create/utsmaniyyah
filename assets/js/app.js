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
  
  const currentPage = document.body.dataset.page || 'dashboard';
  const tabMapping = {
    'dashboard': 'tab-dashboard',
    'santri': 'tab-santri',
    'nilai': 'tab-penilaian',
    'penilaian': 'tab-penilaian',
    'rekap': 'tab-rekap',
    'cetak': 'tab-cetak',
    'pengaturan': 'tab-pengaturan'
  };
  const activeTabId = tabMapping[currentPage] || 'tab-dashboard';
  switchTab(activeTabId);
});

// Load data dari PHP & MySQL Backend
async function loadDataFromBackend() {
  const rtqFilter = document.getElementById('topbarRtqFilter')?.value || 'ALL';
  const periodeFilter = document.getElementById('topbarPeriodeFilter')?.value || 'ALL';

  try {
    const res = await fetch(`api.php?action=get_init&rtq_id=${rtqFilter}&periode_id=${periodeFilter}&_t=${Date.now()}`);
    const data = await res.json();
    
    if (data.status === 'unauthorized') {
      window.location.href = 'login.php';
      return;
    }

    if (data.status === 'success') {
      const loginScreen = document.getElementById('loginScreen');
      if (loginScreen) loginScreen.style.display = 'none';
      const mainApp = document.getElementById('mainApp');
      if (mainApp) mainApp.style.display = 'flex';

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
      window.location.href = 'dashboard.php';
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
    window.location.href = 'logout.php';
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
// 2. DATA SANTRI (DENGAN SORTING REAL-TIME ALA EXCEL)
// ==========================================
let santriSortConfig = {
  column: 'no',
  direction: 'asc'
};

function sortSantriTable(column, forceDirection = null) {
  if (forceDirection) {
    santriSortConfig.column = column;
    santriSortConfig.direction = forceDirection;
  } else {
    if (santriSortConfig.column === column) {
      santriSortConfig.direction = santriSortConfig.direction === 'asc' ? 'desc' : 'asc';
    } else {
      santriSortConfig.column = column;
      santriSortConfig.direction = 'asc';
    }
  }
  renderSantriTable();
}

function renderSantriTable() {
  const tbody = document.getElementById('santriTableBody');
  if (!tbody) return;

  const q = (document.getElementById('searchSantriInput')?.value || '').toLowerCase();
  let filtered = appData.santri.filter(s => {
    return (s.nama || '').toLowerCase().includes(q) ||
           (s.no_induk || '').toLowerCase().includes(q) ||
           (s.no_ujian || '').toLowerCase().includes(q) ||
           (s.ayah || '').toLowerCase().includes(q) ||
           (s.ibu || '').toLowerCase().includes(q) ||
           (s.alamat || '').toLowerCase().includes(q) ||
           (s.rtq_nama || '').toLowerCase().includes(q);
  });

  // Urutkan data berdasarkan kolom dan arah yang dipilih
  filtered.sort((a, b) => {
    let valA, valB;
    switch (santriSortConfig.column) {
      case 'no':
        valA = parseInt(a.no_urut || a.id) || 0;
        valB = parseInt(b.no_urut || b.id) || 0;
        return santriSortConfig.direction === 'asc' ? valA - valB : valB - valA;
      case 'nama':
        valA = (a.nama || '').trim();
        valB = (b.nama || '').trim();
        return santriSortConfig.direction === 'asc' 
          ? valA.localeCompare(valB, 'id', { sensitivity: 'base' })
          : valB.localeCompare(valA, 'id', { sensitivity: 'base' });
      case 'gender':
        valA = (a.gender || '').trim();
        valB = (b.gender || '').trim();
        return santriSortConfig.direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
      case 'rtq':
        valA = (a.rtq_nama || a.rtq_kode || '').trim();
        valB = (b.rtq_nama || b.rtq_kode || '').trim();
        return santriSortConfig.direction === 'asc' ? valA.localeCompare(valB, 'id') : valB.localeCompare(valA, 'id');
      case 'ttl':
        valA = (a.ttl || '').trim();
        valB = (b.ttl || '').trim();
        return santriSortConfig.direction === 'asc' ? valA.localeCompare(valB, 'id') : valB.localeCompare(valA, 'id');
      case 'ayah':
        valA = (a.ayah || '').trim();
        valB = (b.ayah || '').trim();
        return santriSortConfig.direction === 'asc' ? valA.localeCompare(valB, 'id') : valB.localeCompare(valA, 'id');
      case 'ibu':
        valA = (a.ibu || '').trim();
        valB = (b.ibu || '').trim();
        return santriSortConfig.direction === 'asc' ? valA.localeCompare(valB, 'id') : valB.localeCompare(valA, 'id');
      case 'alamat':
        valA = (a.alamat || '').trim();
        valB = (b.alamat || '').trim();
        return santriSortConfig.direction === 'asc' ? valA.localeCompare(valB, 'id') : valB.localeCompare(valA, 'id');
      case 'no_induk':
        valA = (a.no_induk || '').trim();
        valB = (b.no_induk || '').trim();
        return santriSortConfig.direction === 'asc' 
          ? valA.localeCompare(valB, undefined, { numeric: true })
          : valB.localeCompare(valA, undefined, { numeric: true });
      case 'no_ujian':
        valA = (a.no_ujian || '').trim();
        valB = (b.no_ujian || '').trim();
        return santriSortConfig.direction === 'asc' 
          ? valA.localeCompare(valB, undefined, { numeric: true })
          : valB.localeCompare(valA, undefined, { numeric: true });
      case 'no_syahadah':
        valA = (a.no_syahadah || '').trim();
        valB = (b.no_syahadah || '').trim();
        return santriSortConfig.direction === 'asc' 
          ? valA.localeCompare(valB, undefined, { numeric: true })
          : valB.localeCompare(valA, undefined, { numeric: true });
      default:
        return 0;
    }
  });

  // Sinkronkan status aktif tombol panah di header tabel
  document.querySelectorAll('.sort-arrows-group').forEach(group => {
    const col = group.getAttribute('data-col');
    const upBtn = group.querySelector('.arrow-up');
    const downBtn = group.querySelector('.arrow-down');
    if (upBtn) upBtn.classList.remove('active');
    if (downBtn) downBtn.classList.remove('active');

    if (col === santriSortConfig.column) {
      if (santriSortConfig.direction === 'asc' && upBtn) {
        upBtn.classList.add('active');
      } else if (santriSortConfig.direction === 'desc' && downBtn) {
        downBtn.classList.add('active');
      }
    }
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="12" class="text-center" style="padding: 30px; color: var(--dark-500);">Tidak ada data santri ditemukan.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((s, idx) => {
    const genderBadge = s.gender === 'L' ? '<span class="badge badge-gender-l">L</span>' : '<span class="badge badge-gender-p">P</span>';
    return `
      <tr>
        <td class="text-center font-bold" style="color: var(--dark-600);">${idx + 1}</td>
        <td>
          <strong style="color: var(--primary-900); font-size: 0.92rem;">${escapeHtml(s.nama)}</strong>
        </td>
        <td class="text-center">${genderBadge}</td>
        <td>
          <span class="badge badge-rtq" style="font-size: 0.75rem;">${escapeHtml(s.rtq_nama || s.rtq_kode || '-')}</span>
        </td>
        <td style="font-size: 0.82rem;">${escapeHtml(s.ttl || '-')}</td>
        <td style="font-size: 0.84rem; font-weight: 600;">${escapeHtml(s.ayah || '-')}</td>
        <td style="font-size: 0.84rem;">${escapeHtml(s.ibu || '-')}</td>
        <td style="font-size: 0.8rem; color: var(--dark-700); line-height: 1.3;">${escapeHtml(s.alamat || '-')}</td>
        <td class="text-center"><code>${escapeHtml(s.no_induk || '-')}</code></td>
        <td class="text-center"><code>${escapeHtml(s.no_ujian || '-')}</code></td>
        <td>
          <div style="font-size: 0.78rem; font-family: monospace; color: #047857; font-weight: 600; white-space: nowrap;">${escapeHtml(s.no_syahadah || '-')}</div>
        </td>
        <td class="text-center">
          <div class="action-btn-group" style="justify-content: center;">
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
      renderSantriTable();
      renderPenilaianTable();
      renderRekapTable();
      renderDashboard();
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
  const khatStyle = document.getElementById('cetakKhatStyleSelect')?.value || 'katibeh';
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
    html += renderSyahadahDepan(s, { showFrame, showStamp, khatStyle });
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
        <stop offset="20%" stop-color="#d4af37"/>
        <stop offset="45%" stop-color="#fae79d"/>
        <stop offset="55%" stop-color="#fff8d6"/>
        <stop offset="70%" stop-color="#d4af37"/>
        <stop offset="90%" stop-color="#aa771c"/>
        <stop offset="100%" stop-color="#7a520e"/>
      </linearGradient>

      <!-- Royal Islamic Emerald Green Gradient -->
      <linearGradient id="emeraldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#042016"/>
        <stop offset="35%" stop-color="#0b3d2c"/>
        <stop offset="50%" stop-color="#145d44"/>
        <stop offset="65%" stop-color="#0b3d2c"/>
        <stop offset="100%" stop-color="#031810"/>
      </linearGradient>

      <!-- Gold Radial Glow -->
      <radialGradient id="goldRadial" cx="50%" cy="50%" r="50%">
        <stop offset="0%" stop-color="#fff9d4"/>
        <stop offset="40%" stop-color="#ebd06c"/>
        <stop offset="80%" stop-color="#b8860b"/>
        <stop offset="100%" stop-color="#6e4c08"/>
      </radialGradient>

      <!-- Slender 8-Pointed Star (Rub el Hizb) -->
      <g id="star8">
        <polygon points="0,-12 8.5,-8.5 12,0 8.5,8.5 0,12 -8.5,8.5 -12,0 -8.5,-8.5" fill="url(#goldL)" stroke="#6e4c08" stroke-width="0.6"/>
        <polygon points="0,-8 5.7,-5.7 8,0 5.7,5.7 0,8 -5.7,5.7 -8,0 -5.7,-5.7" fill="#0b3d2c" stroke="url(#goldL)" stroke-width="0.6"/>
        <circle cx="0" cy="0" r="2.8" fill="url(#goldRadial)" stroke="#80560a" stroke-width="0.4"/>
      </g>

      <!-- Slender Repeating Pattern (Horizontal) -->
      <pattern id="islamicChainH" width="18" height="6" patternUnits="userSpaceOnUse">
        <path d="M 0 3 L 4.5 0 L 9 3 L 4.5 6 Z M 9 3 L 13.5 0 L 18 3 L 13.5 6 Z" fill="none" stroke="url(#goldL)" stroke-width="0.6"/>
        <circle cx="4.5" cy="3" r="1" fill="#d4af37"/>
        <circle cx="13.5" cy="3" r="1" fill="#d4af37"/>
        <polygon points="9,1 10.5,3 9,5 7.5,3" fill="#0c4331"/>
      </pattern>

      <!-- Slender Repeating Pattern (Vertical) -->
      <pattern id="islamicChainV" width="6" height="18" patternUnits="userSpaceOnUse">
        <path d="M 3 0 L 0 4.5 L 3 9 L 6 4.5 Z M 3 9 L 0 13.5 L 3 18 L 6 13.5 Z" fill="none" stroke="url(#goldL)" stroke-width="0.6"/>
        <circle cx="3" cy="4.5" r="1" fill="#d4af37"/>
        <circle cx="3" cy="13.5" r="1" fill="#d4af37"/>
        <polygon points="1,9 3,10.5 5,9 3,7.5" fill="#0c4331"/>
      </pattern>

      <!-- Slender Islamic Corner Bracket Motif -->
      <g id="cornerOrnament">
        <!-- Delicate Arabesque Arcs with Gold Pinstriping -->
        <path d="M 18 55 C 26 55, 34 46, 34 38 C 34 30, 42 24, 50 24 C 58 24, 64 18, 64 12" fill="none" stroke="url(#goldL)" stroke-width="1.6" stroke-linecap="round"/>
        <path d="M 18 72 C 34 72, 46 60, 46 44 C 46 28, 58 20, 72 18" fill="none" stroke="url(#goldL)" stroke-width="1.2" stroke-dasharray="3 2"/>

        <!-- Flowing Arabesque Tendrils -->
        <path d="M 20 34 C 26 34, 32 28, 32 20" fill="none" stroke="url(#goldL)" stroke-width="1.3"/>
        <circle cx="32" cy="20" r="2" fill="url(#goldRadial)"/>
        
        <path d="M 46 56 C 54 48, 60 42, 68 42" fill="none" stroke="url(#goldL)" stroke-width="1.1"/>
        <circle cx="68" cy="42" r="2" fill="url(#goldRadial)"/>
        
        <path d="M 56 46 C 48 54, 42 60, 42 68" fill="none" stroke="url(#goldL)" stroke-width="1.1"/>
        <circle cx="42" cy="68" r="2" fill="url(#goldRadial)"/>

        <!-- Refined Corner 8-Pointed Star -->
        <g transform="translate(36, 36) scale(1.05)">
          <use href="#star8"/>
        </g>
        
        <!-- Satellite Stars -->
        <g transform="translate(76, 18) scale(0.6)">
          <use href="#star8"/>
        </g>
        <g transform="translate(18, 76) scale(0.6)">
          <use href="#star8"/>
        </g>
      </g>

      <!-- Slender Top Center Mihrab Arch Crown Crest -->
      <g id="topCrest">
        <path d="M -75 0 C -55 -7, -30 -13, 0 -17 C 30 -13, 55 -7, 75 0 Z" fill="url(#emeraldGrad)" stroke="url(#goldL)" stroke-width="1.5"/>
        <path d="M -58 -2 C -40 -7, -22 -11, 0 -14 C 22 -11, 40 -7, 58 -2" fill="none" stroke="url(#goldL)" stroke-width="0.9"/>
        <g transform="translate(0, -9) scale(0.75)">
          <use href="#star8"/>
        </g>
        <circle cx="0" cy="-21" r="2" fill="url(#goldRadial)"/>
      </g>

      <!-- Slender Bottom Center Medallion Crest -->
      <g id="bottomCrest">
        <path d="M -70 0 C -50 7, -28 13, 0 16 C 28 13, 50 7, 70 0 Z" fill="url(#emeraldGrad)" stroke="url(#goldL)" stroke-width="1.5"/>
        <path d="M -55 2 C -38 7, -20 11, 0 13 C 20 11, 38 7, 55 2" fill="none" stroke="url(#goldL)" stroke-width="0.9"/>
        <g transform="translate(0, 8) scale(0.75)">
          <use href="#star8"/>
        </g>
        <circle cx="0" cy="20" r="2" fill="url(#goldRadial)"/>
      </g>

      <!-- Side Midpoint Rosettes -->
      <g id="sideMedallion">
        <path d="M 0 -22 C 7 -13, 11 -6, 11 0 C 11 6, 7 13, 0 22 C -7 13, -11 6, -11 0 C -11 -6, -7 -13, 0 -22 Z" fill="url(#emeraldGrad)" stroke="url(#goldL)" stroke-width="1.3"/>
        <g transform="scale(0.65)">
          <use href="#star8"/>
        </g>
      </g>
    </defs>

    <!-- Base Frame Background -->
    <rect x="0" y="0" width="1000" height="1414" fill="none"/>

    <!-- Layer 1: Outermost Thin Gold Hairline -->
    <rect x="12" y="12" width="976" height="1390" rx="4" fill="none" stroke="url(#goldL)" stroke-width="1.6"/>

    <!-- Layer 2: Slender Royal Emerald Band (6px wide) with Gold Dash -->
    <rect x="18" y="18" width="964" height="1378" rx="3" fill="none" stroke="url(#emeraldGrad)" stroke-width="6"/>
    <rect x="18" y="18" width="964" height="1378" rx="3" fill="none" stroke="url(#goldL)" stroke-width="0.8" stroke-dasharray="6 3"/>

    <!-- Layer 3: Gold Separator Line -->
    <rect x="23" y="23" width="954" height="1368" rx="2" fill="none" stroke="url(#goldL)" stroke-width="1.2"/>

    <!-- Layer 4: Slender Islamic Geometric Pattern Bands -->
    <rect x="76" y="25" width="848" height="6" fill="url(#islamicChainH)"/>
    <rect x="76" y="1383" width="848" height="6" fill="url(#islamicChainH)"/>
    <rect x="25" y="76" width="6" height="1262" fill="url(#islamicChainV)"/>
    <rect x="969" y="76" width="6" height="1262" fill="url(#islamicChainV)"/>

    <!-- Layer 5: Inner Gold Border with Clean Edges -->
    <rect x="33" y="33" width="934" height="1348" rx="2" fill="none" stroke="url(#goldL)" stroke-width="1.5"/>

    <!-- Layer 6: Deep Emerald Inner Hairline -->
    <rect x="37" y="37" width="926" height="1340" rx="1.5" fill="none" stroke="#0d4734" stroke-width="1"/>

    <!-- Layer 7: Subtle Inset Dotted Gold Line -->
    <rect x="41" y="41" width="918" height="1332" fill="none" stroke="url(#goldL)" stroke-width="0.7" stroke-dasharray="3 2"/>

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
    <g transform="translate(500, 33)">
      <use href="#topCrest"/>
    </g>
    <g transform="translate(500, 1381)">
      <use href="#bottomCrest"/>
    </g>

    <!-- Left & Right Midpoint Rosettes -->
    <g transform="translate(29, 707)">
      <use href="#sideMedallion"/>
    </g>
    <g transform="translate(971, 707)">
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
  const khatStyle = opts.khatStyle || (document.getElementById('cetakKhatStyleSelect')?.value ?? 'katibeh');
  const lbg = appData.lembaga || {};
  const binWord = s.gender === 'P' ? 'Binti' : 'Bin';
  const ayahNama = s.ayah || '-';
  const noSyahadah = s.no_syahadah || 'LMY-LB/RTQ-U1/001/09/10/2025';

  let arabicHeaderHtml = '';
  if (khatStyle === 'original') {
    arabicHeaderHtml = `<img src="assets/images/arabic_header_syahadah.png" alt="وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا" class="khat-img-original" />`;
  } else if (khatStyle === 'ruqaa') {
    arabicHeaderHtml = `<span class="arabic-quran-text font-ruqaa">وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا</span>`;
  } else if (khatStyle === 'amiri') {
    arabicHeaderHtml = `<span class="arabic-quran-text font-amiri">وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا</span>`;
  } else {
    // Default: Katibeh (Font Kaligrafi Berseni Tinggi, Melengkung Luwes & Megah)
    arabicHeaderHtml = `<span class="arabic-quran-text font-katibeh">وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا</span>`;
  }

  return `
    <div class="a4-page syahadah-page-depan ${showFrame ? 'has-frame' : 'no-frame'}">
      ${showFrame ? `<div class="syahadah-frame-bg">${getSyahadahVectorFrameSvg()}</div>` : ''}
      
      <div class="syahadah-watermark-center">
        <img src="assets/images/logo-yanbua.png" alt="Yanbua Watermark" />
      </div>

      <div class="syahadah-inner-content">
        <!-- 1. Arabic Calligraphy Header (وَمَا شَهِدْنَا إِلَّا بِمَا عَلِمْنَا) -->
        <div class="syahadah-arabic-calligraphy" dir="rtl" lang="ar">
          ${arabicHeaderHtml}
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
          <div class="transkrip-sigs-cols">
            <!-- Kolom Kiri: Kepala RTQ -->
            <div class="sig-col-box">
              <div class="sig-col-date-spacer">&nbsp;</div>
              <div class="sig-col-title">Kepala ${escapeHtml(s.rtq_nama || "RTQ Utsmaniyyah")}</div>
              <div class="sig-stamp-space">
                ${showStamp ? `
                  <img src="assets/images/stempel_ttd_kepala_rtq.png" class="stamp-transkrip-img" alt="Stempel Kepala RTQ" />
                ` : `
                  <div class="sig-empty-space"></div>
                `}
              </div>
              <div class="sig-col-name"><u><strong>${escapeHtml(s.kepala_rtq || "IKHWANUL KIROM")}</strong></u></div>
            </div>

            <!-- Kolom Kanan: Titimangsa & Ketua Tim Penguji -->
            <div class="sig-col-box">
              <div class="sig-col-date">${escapeHtml(s.titimangsa || "Tambak Jaya, 25 Oktober 2026")}</div>
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
  const khatStyle = document.getElementById('cetakKhatStyleSelect')?.value || 'katibeh';

  const { list } = calculateSantriStats();
  if (list.length === 0) {
    SwalAlert.warning('Pemberitahuan', 'Tidak ada data santri untuk dicetak.');
    return;
  }

  let html = '';
  list.forEach(s => {
    if (docType === 'depan' || docType === 'both') html += renderSyahadahDepan(s, { showFrame, showStamp, khatStyle });
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
          <div style="display: flex; gap: 6px; justify-content: center;">
            <button class="btn btn-outline btn-sm btn-icon" onclick="openEditRtqModal(${r.id})" title="Edit RTQ">✏️</button>
            <button class="btn btn-outline btn-sm btn-icon" style="color: #dc2626; border-color: #fca5a5;" onclick="confirmDeleteRtq(${r.id})" title="Hapus RTQ">🗑️</button>
          </div>
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
          <div style="display: flex; gap: 6px; justify-content: center;">
            <button class="btn btn-outline btn-sm btn-icon" onclick="openEditPeriodeModal(${p.id})" title="Edit Periode">✏️</button>
            <button class="btn btn-outline btn-sm btn-icon" style="color: #dc2626; border-color: #fca5a5;" onclick="confirmDeletePeriode(${p.id})" title="Hapus Periode">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
  }
  
  // Render Tabel Akun Pengguna
  renderUserList();
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

// ==========================================
// MANAJEMEN AKUN PENGGUNA & LOGIN (DEVELOPER)
// ==========================================
function renderUserList() {
  const tbody = document.getElementById('userListTableBody');
  if (!tbody) return;
  const isDeveloper = (appData.currentUser && (appData.currentUser.role === 'DEVELOPER' || appData.currentUser.role === 'SUPER_ADMIN' || appData.currentUser.username === 'Maul'));

  const btnAddUser = document.getElementById('btnAddUserBtn');
  if (btnAddUser) btnAddUser.style.display = isDeveloper ? 'inline-flex' : 'none';

  if (!appData.daftarUsers || appData.daftarUsers.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 20px; color: var(--dark-500);">Belum ada data akun pengguna.</td></tr>';
    return;
  }

  tbody.innerHTML = appData.daftarUsers.map(u => {
    const isMaul = u.username === 'Maul';
    const isMe = u.id == appData.currentUser.id;
    const roleBadge = u.role === 'DEVELOPER' 
      ? '<span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">👑 DEVELOPER</span>' 
      : '<span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">🕌 PENGURUS RTQ</span>';

    return `
      <tr>
        <td><strong style="color: var(--primary-800); font-family: monospace; font-size: 0.95rem;">${escapeHtml(u.username)}</strong></td>
        <td><strong>${escapeHtml(u.nama)}</strong></td>
        <td>${escapeHtml(u.rtq_nama || (u.role === 'DEVELOPER' ? 'Semua Unit RTQ (Pusat)' : '-'))}</td>
        <td>${roleBadge}</td>
        <td class="text-center">
          ${isDeveloper ? `
            <div style="display: flex; gap: 6px; justify-content: center;">
              <button class="btn btn-outline btn-sm btn-icon" onclick="openEditUserModal(${u.id})" title="Edit Akun & Reset Password">✏️</button>
              ${!isMaul && !isMe ? `
                <button class="btn btn-outline btn-sm btn-icon" style="color: #dc2626; border-color: #fca5a5;" onclick="confirmDeleteUser(${u.id}, '${escapeHtml(u.username)}')" title="Hapus Akun">🗑️</button>
              ` : `
                <span title="Akun utama / aktif tidak dapat dihapus" style="opacity: 0.4; padding: 4px;">🔒</span>
              `}
            </div>
          ` : `
            <span style="color: var(--dark-500); font-size: 0.8rem;">Terkunci</span>
          `}
        </td>
      </tr>
    `;
  }).join('');
}

function openAddUserModal() {
  document.getElementById('userModalTitle').textContent = 'Tambah Akun Pengurus RTQ';
  document.getElementById('userForm').reset();
  document.getElementById('userIdInput').value = '';
  document.getElementById('labelUserPassword').textContent = 'Password Login *';
  document.getElementById('inputUserPassword').setAttribute('required', 'required');
  document.getElementById('inputUserPassword').placeholder = 'Password akun baru';
  document.getElementById('helpUserPassword').style.display = 'none';

  populateUserModalRtqSelect();
  handleUserRoleChange();
  openModal('userModal');
}

function openEditUserModal(id) {
  const u = appData.daftarUsers.find(x => x.id == id);
  if (!u) return;

  document.getElementById('userModalTitle').textContent = `Edit Akun: ${u.username}`;
  document.getElementById('userIdInput').value = u.id;
  document.getElementById('inputUserUsername').value = u.username;
  document.getElementById('inputUserNama').value = u.nama;
  document.getElementById('inputUserRole').value = u.role;
  
  document.getElementById('labelUserPassword').textContent = 'Ubah Password (Opsional)';
  document.getElementById('inputUserPassword').removeAttribute('required');
  document.getElementById('inputUserPassword').value = '';
  document.getElementById('inputUserPassword').placeholder = 'Biarkan kosong jika tidak diganti';
  document.getElementById('helpUserPassword').style.display = 'block';

  populateUserModalRtqSelect();
  if (u.rtq_id) {
    document.getElementById('inputUserRtq').value = u.rtq_id;
  }
  handleUserRoleChange();
  openModal('userModal');
}

function populateUserModalRtqSelect() {
  const sel = document.getElementById('inputUserRtq');
  if (!sel) return;
  sel.innerHTML = '<option value="">-- Pilih Unit RTQ --</option>';
  appData.daftarRtq.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.id;
    opt.textContent = `${r.kode} - ${r.nama}`;
    sel.appendChild(opt);
  });
}

function handleUserRoleChange() {
  const role = document.getElementById('inputUserRole').value;
  const groupRtq = document.getElementById('groupUserRtq');
  const inputRtq = document.getElementById('inputUserRtq');
  if (role === 'DEVELOPER') {
    groupRtq.style.display = 'none';
    inputRtq.removeAttribute('required');
  } else {
    groupRtq.style.display = 'block';
    inputRtq.setAttribute('required', 'required');
  }
}

async function handleSaveUser(e) {
  e.preventDefault();
  const id = document.getElementById('userIdInput').value;
  const username = document.getElementById('inputUserUsername').value.trim();
  const password = document.getElementById('inputUserPassword').value.trim();
  const nama = document.getElementById('inputUserNama').value.trim();
  const role = document.getElementById('inputUserRole').value;
  const rtq_id = document.getElementById('inputUserRtq').value;

  if (role === 'PENGURUS_RTQ' && !rtq_id) {
    SwalAlert.error('Peringatan', 'Silakan pilih Unit RTQ yang dikelola oleh akun pengurus ini.');
    return;
  }

  const payload = {
    id: id || null,
    username,
    password,
    nama,
    role,
    rtq_id: role === 'DEVELOPER' ? null : rtq_id
  };

  try {
    const res = await fetch('api.php?action=save_user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      closeModal('userModal');
      await loadDataFromBackend();
      populateConfigTab();
      SwalAlert.success('Berhasil', `Akun "${username}" berhasil disimpan.`);
    } else {
      SwalAlert.error('Gagal', result.message || 'Gagal menyimpan akun.');
    }
  } catch (err) {
    SwalAlert.error('Kesalahan', 'Terjadi kesalahan sistem saat menyimpan akun.');
  }
}

async function confirmDeleteUser(id, username) {
  const confirmResult = await SwalAlert.confirm(
    'Hapus Akun Pengguna?',
    `Apakah Anda yakin ingin menghapus akun login <strong>"${escapeHtml(username)}"</strong>? Pengurus tidak akan bisa login lagi dengan akun ini.`,
    'Ya, Hapus Akun',
    true
  );

  if (confirmResult.isConfirmed) {
    try {
      const res = await fetch('api.php?action=delete_user', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const result = await res.json();
      if (result.status === 'success') {
        await loadDataFromBackend();
        populateConfigTab();
        SwalAlert.toast(`Akun ${username} berhasil dihapus.`);
      } else {
        SwalAlert.error('Gagal', result.message || 'Gagal menghapus akun.');
      }
    } catch (err) {
      SwalAlert.error('Kesalahan', 'Gagal menghapus akun dari server.');
    }
  }
}

// RTQ Modal
function openAddRtqModal() {
  document.getElementById('rtqModalTitle').textContent = 'Tambah Unit RTQ Baru';
  document.getElementById('rtqForm').reset();
  document.getElementById('rtqIdInput').value = '';
  
  const rtqUserSection = document.getElementById('rtqAccountSection');
  if (rtqUserSection) rtqUserSection.style.display = 'block';
  const uInp = document.getElementById('inputRtqUsername');
  const pInp = document.getElementById('inputRtqPassword');
  if (uInp) uInp.value = '';
  if (pInp) pInp.value = '';

  const delBtn = document.getElementById('btnDeleteRtqModal');
  if (delBtn) delBtn.style.display = 'none';
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

  const rtqUserSection = document.getElementById('rtqAccountSection');
  if (rtqUserSection) rtqUserSection.style.display = 'none';

  const delBtn = document.getElementById('btnDeleteRtqModal');
  if (delBtn) delBtn.style.display = 'inline-block';
  openModal('rtqModal');
}

function deleteRtqFromModal() {
  const id = document.getElementById('rtqIdInput').value;
  if (id) {
    closeModal('rtqModal');
    confirmDeleteRtq(id);
  }
}

async function confirmDeleteRtq(id) {
  const r = appData.daftarRtq.find(x => x.id == id);
  if (!r) return;
  const confirmResult = await SwalAlert.confirm(
    'Hapus Unit RTQ?',
    `Yakin ingin menghapus unit RTQ "<strong>${escapeHtml(r.nama)}</strong>" (${escapeHtml(r.kode)})?`,
    'Ya, Hapus RTQ',
    true
  );
  if (confirmResult.isConfirmed) {
    try {
      const res = await fetch('api.php?action=delete_rtq', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const result = await res.json();
      if (result.status === 'success') {
        await loadDataFromBackend();
        populateConfigTab();
        SwalAlert.toast(`Unit RTQ ${r.nama} berhasil dihapus`);
      } else if (result.status === 'has_santri') {
        const forceRes = await SwalAlert.confirm(
          'RTQ Masih Berisi Santri',
          `Unit RTQ "<strong>${escapeHtml(r.nama)}</strong>" masih memiliki <strong>${result.count} data santri</strong>.<br><br>Apakah Anda yakin ingin menghapus unit RTQ ini beserta seluruh data santri di dalamnya?`,
          'Ya, Hapus Semua Data Terkait',
          true
        );
        if (forceRes.isConfirmed) {
          const res2 = await fetch('api.php?action=delete_rtq', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, force: true })
          });
          const res2Json = await res2.json();
          if (res2Json.status === 'success') {
            await loadDataFromBackend();
            populateConfigTab();
            SwalAlert.toast(`Unit RTQ ${r.nama} dan seluruh santrinya berhasil dihapus`);
          } else {
            SwalAlert.error('Gagal', res2Json.message || 'Gagal menghapus unit RTQ.');
          }
        }
      } else {
        SwalAlert.error('Gagal', result.message || 'Gagal menghapus unit RTQ.');
      }
    } catch (err) {
      SwalAlert.error('Kesalahan', 'Gagal menghapus unit RTQ dari database.');
    }
  }
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
    wa: document.getElementById('inputRtqWaModal').value.trim(),
    username: document.getElementById('inputRtqUsername')?.value.trim() || '',
    password: document.getElementById('inputRtqPassword')?.value.trim() || ''
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
      populateConfigTab();
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
  const delBtn = document.getElementById('btnDeletePeriodeModal');
  if (delBtn) delBtn.style.display = 'none';
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
  const delBtn = document.getElementById('btnDeletePeriodeModal');
  if (delBtn) delBtn.style.display = 'inline-block';
  openModal('periodeModal');
}

function deletePeriodeFromModal() {
  const id = document.getElementById('periodeIdInput').value;
  if (id) {
    closeModal('periodeModal');
    confirmDeletePeriode(id);
  }
}

async function confirmDeletePeriode(id) {
  const p = appData.daftarPeriode.find(x => x.id == id);
  if (!p) return;
  const confirmResult = await SwalAlert.confirm(
    'Hapus Periode Ujian?',
    `Yakin ingin menghapus periode "<strong>${escapeHtml(p.tahun_pelajaran)}</strong>"?`,
    'Ya, Hapus Periode',
    true
  );
  if (confirmResult.isConfirmed) {
    try {
      const res = await fetch('api.php?action=delete_periode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const result = await res.json();
      if (result.status === 'success') {
        await loadDataFromBackend();
        populateConfigTab();
        SwalAlert.toast(`Periode ${p.tahun_pelajaran} berhasil dihapus`);
      } else if (result.status === 'has_santri') {
        const forceRes = await SwalAlert.confirm(
          'Periode Masih Berisi Santri',
          `Periode "<strong>${escapeHtml(p.tahun_pelajaran)}</strong>" masih memiliki <strong>${result.count} data santri</strong>.<br><br>Apakah Anda yakin ingin menghapus periode ini beserta seluruh data santri di dalamnya?`,
          'Ya, Hapus Semua Data Terkait',
          true
        );
        if (forceRes.isConfirmed) {
          const res2 = await fetch('api.php?action=delete_periode', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, force: true })
          });
          const res2Json = await res2.json();
          if (res2Json.status === 'success') {
            await loadDataFromBackend();
            populateConfigTab();
            SwalAlert.toast(`Periode ${p.tahun_pelajaran} dan seluruh santrinya berhasil dihapus`);
          } else {
            SwalAlert.error('Gagal', res2Json.message || 'Gagal menghapus periode.');
          }
        }
      } else {
        SwalAlert.error('Gagal', result.message || 'Gagal menghapus periode.');
      }
    } catch (err) {
      SwalAlert.error('Kesalahan', 'Gagal menghapus periode dari database.');
    }
  }
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
      populateConfigTab();
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
  document.getElementById('userForm')?.addEventListener('submit', handleSaveUser);

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
