/**
 * Sistem Informasi Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 * Thoriqoh Baca Tulis dan Menghafal Al-Qur'an Yanbu'a
 */

// Global State
let appData = {
  config: {},
  santri: []
};

const SUBJECT_KEYS = [
  { key: 'tartil', label: 'Tartil & Fashohah' },
  { key: 'ghorib', label: 'Ghoroibul Qur\'an' },
  { key: 'tajwid', label: 'Ilmu Tajwid' },
  { key: 'tauhid', label: 'Tauhid Dasar' },
  { key: 'wudlu', label: 'Praktik Wudlu' },
  { key: 'sholat', label: 'Praktik Sholat' },
  { key: 'surat', label: 'Hafalan Surat Pendek' },
  { key: 'doa', label: 'Hafalan Do\'a Harian' },
  { key: 'kitabah', label: 'Kitabah' }
];

// Indonesian Number to Words Converter (Terbilang Nilai Angka)
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

// Initialize Application
document.addEventListener('DOMContentLoaded', async () => {
  await loadData();
  setupNavigation();
  setupEventListeners();
  renderAll();
});

// Load Data from LocalStorage or Fallback to JSON
async function loadData() {
  const saved = localStorage.getItem('utsmaniyyah_munaqosyah_data');
  if (saved) {
    try {
      appData = JSON.parse(saved);
      return;
    } catch (e) {
      console.error('Error parsing localStorage:', e);
    }
  }

  // Fallback: fetch from assets/data/initialData.json
  try {
    const res = await fetch('assets/data/initialData.json');
    if (res.ok) {
      appData = await res.json();
      saveData();
    }
  } catch (err) {
    console.warn('Could not load initialData.json, using built-in defaults:', err);
    useHardcodedFallback();
  }
}

function saveData() {
  localStorage.setItem('utsmaniyyah_munaqosyah_data', JSON.stringify(appData));
}

function useHardcodedFallback() {
  appData = {
    config: {
      lembaga: "LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR'AN",
      metode: "THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR'AN YANBU'A",
      wilayah: "KABUPATEN LAMPUNG BARAT",
      tingkat: "RTQ TINGKAT DASAR",
      rtqName: "RTQ UTSMANIYYAH III",
      rtqAlamat: "Jl. Tirtomoyo, No.083, Margorahayu, Desa Tambak Jaya Kec, Way Tenong Kab, Lampung Barat",
      rtqWa: "082184427288",
      tahunPelajaran: "2025 M / 1447 H",
      tanggalMunaqosyahH: "14 Rabi'ul Awwal 1447 H",
      tanggalMunaqosyahM: "07 September 2025 M",
      tanggalSyahadah: "21 September 2025",
      tempatTanggal: "Tambak Jaya, 07 September 2025",
      kepalaRtq: "ABU HANIFAN",
      ketuaPenguji: "IKHWANUL KIROM",
      ketuaPanitia: "A. Muslih Abdurrahman",
      pimpinanLajnah: "SYA'BUL MUBAROK",
      kkm: 65
    },
    santri: []
  };
  saveData();
}

// Navigation Handling
function setupNavigation() {
  const navLinks = document.querySelectorAll('.nav-link[data-tab]');
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const tabId = link.getAttribute('data-tab');
      switchTab(tabId);
    });
  });

  // Mobile menu toggle
  const menuToggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  }
}

function switchTab(tabId) {
  // Update nav links
  document.querySelectorAll('.nav-link[data-tab]').forEach(el => {
    el.classList.toggle('active', el.getAttribute('data-tab') === tabId);
  });

  // Update tabs
  document.querySelectorAll('.tab-content').forEach(el => {
    el.classList.remove('active');
  });

  const activeTab = document.getElementById(tabId);
  if (activeTab) {
    activeTab.classList.add('active');
  }

  // Update topbar title
  const titles = {
    'tab-dashboard': 'Dashboard Munaqosyah',
    'tab-santri': 'Data Santri Peserta Ujian',
    'tab-penilaian': 'Input & Edit Nilai Munaqosyah',
    'tab-rekap': 'Rekapitulasi Nilai & Rangking',
    'tab-cetak': 'Cetak Dokumen Resmi (Syahadah & Transkrip)',
    'tab-pengaturan': 'Pengaturan Lembaga & Sistem'
  };
  const topbarTitle = document.getElementById('topbarPageTitle');
  if (topbarTitle && titles[tabId]) {
    topbarTitle.textContent = titles[tabId];
  }

  // Re-render specific tab
  if (tabId === 'tab-dashboard') renderDashboard();
  if (tabId === 'tab-santri') renderSantriTable();
  if (tabId === 'tab-penilaian') renderPenilaianTable();
  if (tabId === 'tab-rekap') renderRekapTable();
  if (tabId === 'tab-cetak') setupCetakTab();
  if (tabId === 'tab-pengaturan') populateConfigForm();

  // Close sidebar on mobile
  const sidebar = document.getElementById('sidebar');
  if (sidebar && window.innerWidth <= 992) {
    sidebar.classList.remove('show');
  }
}

// Calculations: Total, Average, Ranks, Pass Status
function calculateSantriStats() {
  const kkm = Number(appData.config.kkm) || 65;
  const list = appData.santri.map(s => {
    let total = 0;
    let count = 0;
    SUBJECT_KEYS.forEach(sub => {
      const val = Number(s.scores?.[sub.key]) || 0;
      total += val;
      count++;
    });
    const avg = count > 0 ? (total / count) : 0;
    const isLulus = avg >= kkm;

    return {
      ...s,
      totalNilai: Number(total.toFixed(2)),
      rataRata: Number(avg.toFixed(2)),
      isLulus
    };
  });

  // Sort descending by total score to calculate ranks
  const sorted = [...list].sort((a, b) => b.totalNilai - a.totalNilai);
  let currentRank = 1;
  sorted.forEach((item, index) => {
    if (index > 0 && item.totalNilai < sorted[index - 1].totalNilai) {
      currentRank = index + 1;
    }
    item.rank = currentRank;
  });

  // Map back rank to original order
  const rankMap = new Map(sorted.map(s => [s.id, s.rank]));
  list.forEach(s => {
    s.rank = rankMap.get(s.id);
  });

  return { list, sorted };
}

// Master Render
function renderAll() {
  renderDashboard();
  renderSantriTable();
  renderPenilaianTable();
  renderRekapTable();
  setupCetakTab();
  populateConfigForm();
}

// ==========================================
// 1. DASHBOARD
// ==========================================
function renderDashboard() {
  const { list, sorted } = calculateSantriStats();
  const totalSantri = list.length;
  const lulusCount = list.filter(s => s.isLulus).length;
  const tidakLulusCount = totalSantri - lulusCount;
  
  const totalAllAvg = list.reduce((acc, curr) => acc + curr.rataRata, 0);
  const avgOverall = totalSantri > 0 ? (totalAllAvg / totalAllAvg.length || totalAllAvg / totalSantri).toFixed(1) : 0;
  const topScore = sorted.length > 0 ? sorted[0].totalNilai : 0;

  // Stat Counters
  document.getElementById('dashTotalSantri').textContent = totalSantri;
  document.getElementById('dashLulus').textContent = lulusCount;
  document.getElementById('dashTidakLulus').textContent = tidakLulusCount;
  document.getElementById('dashRataRata').textContent = avgOverall;

  // Podium (Top 3)
  const podiumContainer = document.getElementById('dashPodium');
  if (podiumContainer) {
    if (sorted.length === 0) {
      podiumContainer.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--dark-500); padding: 20px;">Belum ada data santri</div>';
    } else {
      let html = '';
      const top3 = sorted.slice(0, 3);
      top3.forEach((s, idx) => {
        const rankClass = idx === 0 ? 'rank-1' : idx === 1 ? 'rank-2' : 'rank-3';
        const rankLabel = idx === 0 ? 'Juara 1' : idx === 1 ? 'Juara 2' : 'Juara 3';
        const badgeIcon = idx === 0 ? '🥇' : idx === 1 ? '🥈' : '🥉';
        html += `
          <div class="podium-card ${rankClass}">
            <div class="podium-badge">${badgeIcon}</div>
            <div class="badge ${idx === 0 ? 'badge-gold' : 'badge-primary'}" style="margin-bottom: 8px;">${rankLabel}</div>
            <h4 class="podium-name">${escapeHtml(s.nama)}</h4>
            <div class="podium-info">No. Ujian: ${escapeHtml(s.noUjian || '-')} | RTQ: ${escapeHtml(s.rtq || '-')}</div>
            <div class="podium-score">${s.totalNilai}</div>
            <div style="font-size: 0.75rem; color: var(--dark-500);">Rata-rata: ${s.rataRata} (${s.isLulus ? 'Lulus' : 'Belum Lulus'})</div>
          </div>
        `;
      });
      podiumContainer.innerHTML = html;
    }
  }

  // Subject Averages Breakdown Bar
  const subjectBars = document.getElementById('dashSubjectBars');
  if (subjectBars) {
    let barsHtml = '';
    SUBJECT_KEYS.forEach(sub => {
      let subTotal = 0;
      list.forEach(s => {
        subTotal += Number(s.scores?.[sub.key]) || 0;
      });
      const subAvg = totalSantri > 0 ? (subTotal / totalSantri).toFixed(1) : 0;
      const pct = Math.min(100, Math.max(0, (subAvg / 100) * 100));

      barsHtml += `
        <div style="margin-bottom: 14px;">
          <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
            <span>${sub.label}</span>
            <span style="color: var(--primary-700);">${subAvg}</span>
          </div>
          <div style="height: 9px; background: var(--gray-200); border-radius: 10px; overflow: hidden;">
            <div style="height: 100%; width: ${pct}%; background: linear-gradient(90deg, var(--primary-600), var(--gold-500)); border-radius: 10px; transition: width 0.5s ease;"></div>
          </div>
        </div>
      `;
    });
    subjectBars.innerHTML = barsHtml;
  }
}

// ==========================================
// 2. DATA SANTRI
// ==========================================
function renderSantriTable() {
  const tbody = document.getElementById('santriTableBody');
  if (!tbody) return;

  const searchQuery = (document.getElementById('searchSantriInput')?.value || '').toLowerCase();
  const filtered = appData.santri.filter(s => {
    return (
      (s.nama || '').toLowerCase().includes(searchQuery) ||
      (s.noInduk || '').toLowerCase().includes(searchQuery) ||
      (s.noUjian || '').toLowerCase().includes(searchQuery) ||
      (s.ayah || '').toLowerCase().includes(searchQuery)
    );
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9" class="text-center" style="padding: 30px; color: var(--dark-500);">Tidak ada data santri ditemukan.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((s, index) => {
    const genderBadge = s.gender === 'L' ? '<span class="badge badge-primary">Laki-laki (Bin)</span>' : '<span class="badge badge-gold">Perempuan (Binti)</span>';
    return `
      <tr>
        <td class="text-center font-bold">${index + 1}</td>
        <td>
          <strong style="color: var(--primary-900); font-size: 0.95rem;">${escapeHtml(s.nama)}</strong>
          <div style="font-size: 0.78rem; color: var(--dark-500);">${escapeHtml(s.ttl || '-')}</div>
        </td>
        <td>${genderBadge}</td>
        <td>
          <div>Ayah: <strong>${escapeHtml(s.ayah || '-')}</strong></div>
          <div style="font-size: 0.78rem; color: var(--dark-500);">Ibu: ${escapeHtml(s.ibu || '-')}</div>
        </td>
        <td>
          <div>No. Induk: <code>${escapeHtml(s.noInduk || '-')}</code></div>
          <div style="font-size: 0.78rem;">No. Ujian: <code>${escapeHtml(s.noUjian || '-')}</code></div>
        </td>
        <td>
          <div style="font-size: 0.8rem; font-family: monospace; color: var(--primary-800);">${escapeHtml(s.noSyahadah || '-')}</div>
        </td>
        <td class="text-center">
          <div style="display: flex; gap: 6px; justify-content: center;">
            <button class="btn btn-outline btn-sm btn-icon" title="Edit Santri" onclick="openEditSantriModal(${s.id})">
              ✏️
            </button>
            <button class="btn btn-outline btn-sm btn-icon" title="Cetak Syahadah Depan" onclick="previewSingleCetak(${s.id}, 'depan')">
              📜
            </button>
            <button class="btn btn-outline btn-sm btn-icon" title="Cetak Transkrip Belakang" onclick="previewSingleCetak(${s.id}, 'belakang')">
              📋
            </button>
            <button class="btn btn-danger btn-sm btn-icon" title="Hapus" onclick="confirmDeleteSantri(${s.id})">
              🗑️
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

// Modal Tambah / Edit Santri
function openAddSantriModal() {
  document.getElementById('santriModalTitle').textContent = 'Tambah Santri Baru';
  document.getElementById('santriForm').reset();
  document.getElementById('santriIdInput').value = '';
  
  // Auto generate next No Syahadah & No Ujian
  const nextNo = (appData.santri.length + 1).toString().padStart(4, '0');
  const shortNum = (appData.santri.length + 1).toString().padStart(3, '0');
  document.getElementById('inputNoInduk').value = nextNo;
  document.getElementById('inputNoUjian').value = nextNo;
  document.getElementById('inputNoSyahadah').value = `LMY-LB/RTQ-U3/${shortNum}/09/10/2025`;
  document.getElementById('inputRtq').value = appData.config.rtqName || 'RTQ Utsmaniyyah III';

  openModal('santriModal');
}

function openEditSantriModal(id) {
  const s = appData.santri.find(x => x.id === id);
  if (!s) return;

  document.getElementById('santriModalTitle').textContent = 'Edit Data Santri';
  document.getElementById('santriIdInput').value = s.id;
  document.getElementById('inputNama').value = s.nama || '';
  document.getElementById('inputRtq').value = s.rtq || appData.config.rtqName;
  document.getElementById('inputGender').value = s.gender || 'L';
  document.getElementById('inputTtl').value = s.ttl || '';
  document.getElementById('inputAyah').value = s.ayah || '';
  document.getElementById('inputIbu').value = s.ibu || '';
  document.getElementById('inputAlamat').value = s.alamat || '';
  document.getElementById('inputNoInduk').value = s.noInduk || '';
  document.getElementById('inputNoUjian').value = s.noUjian || '';
  document.getElementById('inputNoSyahadah').value = s.noSyahadah || '';

  openModal('santriModal');
}

function handleSaveSantri(e) {
  e.preventDefault();
  const idVal = document.getElementById('santriIdInput').value;
  const isEdit = Boolean(idVal);

  const santriData = {
    nama: document.getElementById('inputNama').value.trim(),
    rtq: document.getElementById('inputRtq').value.trim(),
    gender: document.getElementById('inputGender').value,
    ttl: document.getElementById('inputTtl').value.trim(),
    ayah: document.getElementById('inputAyah').value.trim(),
    ibu: document.getElementById('inputIbu').value.trim(),
    alamat: document.getElementById('inputAlamat').value.trim(),
    noInduk: document.getElementById('inputNoInduk').value.trim(),
    noUjian: document.getElementById('inputNoUjian').value.trim(),
    noSyahadah: document.getElementById('inputNoSyahadah').value.trim()
  };

  if (!santriData.nama) {
    alert('Nama santri wajib diisi!');
    return;
  }

  if (isEdit) {
    const id = parseInt(idVal, 10);
    const index = appData.santri.findIndex(x => x.id === id);
    if (index !== -1) {
      appData.santri[index] = {
        ...appData.santri[index],
        ...santriData
      };
    }
  } else {
    const newId = appData.santri.length > 0 ? Math.max(...appData.santri.map(x => x.id)) + 1 : 1;
    appData.santri.push({
      id: newId,
      no: appData.santri.length + 1,
      ...santriData,
      scores: {
        tartil: 0,
        ghorib: 0,
        tajwid: 0,
        tauhid: 0,
        wudlu: 0,
        sholat: 0,
        surat: 0,
        doa: 0,
        kitabah: 0
      }
    });
  }

  saveData();
  closeModal('santriModal');
  renderSantriTable();
  renderPenilaianTable();
  renderRekapTable();
  renderDashboard();
}

function confirmDeleteSantri(id) {
  const s = appData.santri.find(x => x.id === id);
  if (!s) return;
  if (confirm(`Yakin ingin menghapus santri "${s.nama}"? Data nilai yang sudah diinput juga akan terhapus.`)) {
    appData.santri = appData.santri.filter(x => x.id !== id);
    saveData();
    renderAll();
  }
}

// ==========================================
// 3. PENILAIAN MUNAQOSYAH (INPUT NILAI)
// ==========================================
function renderPenilaianTable() {
  const tbody = document.getElementById('penilaianTableBody');
  if (!tbody) return;

  const { list } = calculateSantriStats();
  if (list.length === 0) {
    tbody.innerHTML = `<tr><td colspan="14" class="text-center" style="padding: 30px; color: var(--dark-500);">Belum ada data santri untuk dinilai.</td></tr>`;
    return;
  }

  tbody.innerHTML = list.map((s, index) => {
    return `
      <tr data-santri-id="${s.id}">
        <td class="text-center font-bold">${index + 1}</td>
        <td>
          <div style="font-weight: 700; color: var(--dark-900);">${escapeHtml(s.nama)}</div>
          <div style="font-size: 0.75rem; color: var(--dark-500);">No. Ujian: ${escapeHtml(s.noUjian || '-')}</div>
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.tartil ?? 0}" onchange="updateScore(${s.id}, 'tartil', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.ghorib ?? 0}" onchange="updateScore(${s.id}, 'ghorib', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.tajwid ?? 0}" onchange="updateScore(${s.id}, 'tajwid', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.tauhid ?? 0}" onchange="updateScore(${s.id}, 'tauhid', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.wudlu ?? 0}" onchange="updateScore(${s.id}, 'wudlu', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.sholat ?? 0}" onchange="updateScore(${s.id}, 'sholat', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.surat ?? 0}" onchange="updateScore(${s.id}, 'surat', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.doa ?? 0}" onchange="updateScore(${s.id}, 'doa', this.value)" />
        </td>
        <td class="text-center">
          <input type="number" step="0.5" min="0" max="100" class="score-input" value="${s.scores?.kitabah ?? 0}" onchange="updateScore(${s.id}, 'kitabah', this.value)" />
        </td>
        <td class="text-center font-bold" style="color: var(--primary-800); font-size: 0.95rem;">
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

function updateScore(santriId, subjectKey, val) {
  const s = appData.santri.find(x => x.id === santriId);
  if (!s) return;
  if (!s.scores) s.scores = {};
  
  let numVal = parseFloat(val);
  if (isNaN(numVal) || numVal < 0) numVal = 0;
  if (numVal > 100) numVal = 100;
  
  s.scores[subjectKey] = numVal;
  saveData();
  
  // Re-calculate and update tables
  renderPenilaianTable();
  renderRekapTable();
  renderDashboard();
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

  tbody.innerHTML = sorted.map((s, index) => {
    const rankBadge = s.rank === 1 ? '🥇 1' : s.rank === 2 ? '🥈 2' : s.rank === 3 ? '🥉 3' : s.rank;
    return `
      <tr>
        <td class="text-center font-bold">${index + 1}</td>
        <td><strong>${escapeHtml(s.nama)}</strong></td>
        <td>${escapeHtml(s.rtq || '-')}</td>
        <td class="text-center">${s.scores?.tartil ?? 0}</td>
        <td class="text-center">${s.scores?.ghorib ?? 0}</td>
        <td class="text-center">${s.scores?.tajwid ?? 0}</td>
        <td class="text-center">${s.scores?.tauhid ?? 0}</td>
        <td class="text-center">${s.scores?.wudlu ?? 0}</td>
        <td class="text-center">${s.scores?.sholat ?? 0}</td>
        <td class="text-center">${s.scores?.surat ?? 0}</td>
        <td class="text-center">${s.scores?.doa ?? 0}</td>
        <td class="text-center">${s.scores?.kitabah ?? 0}</td>
        <td class="text-center font-bold" style="color: var(--primary-900); font-size: 1rem;">${s.totalNilai}</td>
        <td class="text-center font-bold">${s.rataRata}</td>
        <td class="text-center">
          <span class="badge ${s.rank <= 3 ? 'badge-gold' : 'badge-primary'}">${rankBadge}</span>
        </td>
      </tr>
    `;
  }).join('');
}

// Export Rekap to Excel (.xlsx) using SheetJS
function exportRekapExcel() {
  if (typeof XLSX === 'undefined') {
    alert('SheetJS belum siap. Membuka opsi export CSV...');
    exportRekapCSV();
    return;
  }

  const { sorted } = calculateSantriStats();
  const dataRows = sorted.map((s, idx) => ({
    'No': idx + 1,
    'No Ujian': s.noUjian || '',
    'Nama Santri': s.nama || '',
    'Asal RTQ': s.rtq || '',
    'Tartil & Fashohah': s.scores?.tartil ?? 0,
    'Ghoroibul Quran': s.scores?.ghorib ?? 0,
    'Ilmu Tajwid': s.scores?.tajwid ?? 0,
    'Tauhid Dasar': s.scores?.tauhid ?? 0,
    'Praktik Wudlu': s.scores?.wudlu ?? 0,
    'Praktik Sholat': s.scores?.sholat ?? 0,
    'Hafalan Surat Pendek': s.scores?.surat ?? 0,
    'Hafalan Doa Harian': s.scores?.doa ?? 0,
    'Kitabah': s.scores?.kitabah ?? 0,
    'Total Nilai': s.totalNilai,
    'Rata-Rata': s.rataRata,
    'Peringkat': s.rank,
    'Keterangan': s.isLulus ? 'LULUS' : 'TIDAK LULUS'
  }));

  const ws = XLSX.utils.json_to_sheet(dataRows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, "Rekap Nilai Munaqosyah");
  XLSX.writeFile(wb, `Rekap_Munaqosyah_${(appData.config.rtqName || 'RTQ').replace(/\s+/g, '_')}.xlsx`);
}

function exportRekapCSV() {
  const { sorted } = calculateSantriStats();
  let csv = 'No,No Ujian,Nama,Asal RTQ,Tartil,Ghorib,Tajwid,Tauhid,Wudlu,Sholat,Surat,Doa,Kitabah,Total,Rata-rata,Peringkat,Keterangan\n';
  sorted.forEach((s, idx) => {
    csv += `"${idx + 1}","${s.noUjian || ''}","${s.nama || ''}","${s.rtq || ''}",${s.scores?.tartil || 0},${s.scores?.ghorib || 0},${s.scores?.tajwid || 0},${s.scores?.tauhid || 0},${s.scores?.wudlu || 0},${s.scores?.sholat || 0},${s.scores?.surat || 0},${s.scores?.doa || 0},${s.scores?.kitabah || 0},${s.totalNilai},${s.rataRata},${s.rank},"${s.isLulus ? 'LULUS' : 'TIDAK LULUS'}"\n`;
  });

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'rekap_nilai_munaqosyah.csv';
  a.click();
}

// ==========================================
// 5. CETAK DOKUMEN RESMI (SYAHADAH & TRANSKRIP)
// ==========================================
function setupCetakTab() {
  const select = document.getElementById('cetakSantriSelect');
  if (!select) return;

  const currentVal = select.value;
  select.innerHTML = '<option value="">-- Pilih Santri untuk Dicetak --</option>';
  appData.santri.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id;
    opt.textContent = `${s.noUjian || '-'} - ${s.nama}`;
    select.appendChild(opt);
  });

  if (currentVal && appData.santri.some(s => s.id == currentVal)) {
    select.value = currentVal;
  } else if (appData.santri.length > 0) {
    select.value = appData.santri[0].id;
  }

  generateCetakPreview();
}

function generateCetakPreview() {
  const select = document.getElementById('cetakSantriSelect');
  const docType = document.getElementById('cetakDocTypeSelect')?.value || 'both';
  const container = document.getElementById('cetakPreviewArea');
  if (!container) return;

  const santriId = parseInt(select?.value, 10);
  if (!santriId) {
    container.innerHTML = '<div style="color: #fff; text-align: center; padding: 40px;">Pilih santri terlebih dahulu untuk menampilkan pratinjau cetak.</div>';
    return;
  }

  const { list } = calculateSantriStats();
  const s = list.find(x => x.id === santriId);
  if (!s) return;

  let html = '';

  if (docType === 'depan' || docType === 'both') {
    html += renderSyahadahDepan(s);
  }
  if (docType === 'belakang' || docType === 'both') {
    html += renderSyahadahBelakang(s);
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

// Syahadah Depan Template
function renderSyahadahDepan(s) {
  const cfg = appData.config;
  const binWord = s.gender === 'P' ? 'Binti' : 'Bin';
  const ayahNama = s.ayah || '-';

  return `
    <div class="a4-page syahadah-depan">
      <div class="syahadah-border"></div>

      <div class="syahadah-header">
        <div class="arabic-bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
        <img src="assets/images/logo-yanbua.jpg" alt="Logo Yanbua" class="syahadah-logo" />
        <h1 class="syahadah-title">SYAHADAH</h1>
        <div class="syahadah-subtitle">ROUDLOTU TARBIYATIL QUR'AN</div>
        <div class="syahadah-subtitle">${escapeHtml(cfg.lembaga || "LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR'AN")}</div>
        <div class="syahadah-subtitle">${escapeHtml(cfg.metode || "THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR'AN YANBU'A")}</div>
        <div class="syahadah-subtitle">${escapeHtml(cfg.wilayah || "KABUPATEN LAMPUNG BARAT")}</div>
        <div class="syahadah-nomor">Nomor : ${escapeHtml(s.noSyahadah || '-')}</div>
        <div class="syahadah-divider"></div>
      </div>

      <div class="syahadah-body">
        <p>
          Setelah mengadakan ujian akhir RTQ tingkat dasar pada tanggal <strong>${escapeHtml(cfg.tanggalMunaqosyahH || "14 Rabi'ul Awwal 1447 H")} / ${escapeHtml(cfg.tanggalMunaqosyahM || "07 September 2025 M")}</strong> yang diselenggarakan di <strong>${escapeHtml(cfg.rtqName || "RTQ UTSMANIYYAH III")}</strong> ${escapeHtml(cfg.rtqAlamat || "")}, maka kami menyatakan bahwa anak yang bernama:
        </p>

        <table class="santri-bio-table">
          <tr>
            <td class="label-col">Nama Lengkap</td>
            <td class="colon-col">:</td>
            <td class="value-col syahadah-santri-nama">${escapeHtml(s.nama)}</td>
          </tr>
          <tr>
            <td class="label-col">${binWord}</td>
            <td class="colon-col">:</td>
            <td class="value-col">${escapeHtml(ayahNama)}</td>
          </tr>
          <tr>
            <td class="label-col">Tempat, Tanggal Lahir</td>
            <td class="colon-col">:</td>
            <td class="value-col">${escapeHtml(s.ttl || '-')}</td>
          </tr>
          <tr>
            <td class="label-col">Nomor Induk Santri</td>
            <td class="colon-col">:</td>
            <td class="value-col">${escapeHtml(s.noInduk || '-')}</td>
          </tr>
          <tr>
            <td class="label-col">Alamat</td>
            <td class="colon-col">:</td>
            <td class="value-col">${escapeHtml(s.alamat || '-')}</td>
          </tr>
        </table>

        <p>
          Telah <strong>LULUS</strong> dalam menempuh evaluasi pembelajaran Al-Qur'an tingkat dasar di lembaga Roudlotu Tarbiyatil Qur'an dan sebagai bukti kepadanya diberikan <strong>Syahadah</strong>. Semoga Allah Subhanahu Wa Ta'ala memberinya ilmu yang bermanfaat dan barokah. Aamiin.
        </p>
      </div>

      <div class="syahadah-footer">
        <div class="signature-block">
          <div class="signature-date">Lampung Barat, ${escapeHtml(cfg.tanggalSyahadah || "21 September 2025")}</div>
          <div class="signature-role">Lajnah Muroqobah Yanbu'a</div>
          <div class="signature-name">${escapeHtml(cfg.pimpinanLajnah || "SYA'BUL MUBAROK")}</div>
        </div>
      </div>
    </div>
  `;
}

// Syahadah Belakang (Transkrip Nilai) Template
function renderSyahadahBelakang(s) {
  const cfg = appData.config;
  const scores = s.scores || {};

  const rows = [
    { no: 1, materi: 'Tartil Fashohah', val: scores.tartil ?? 0 },
    { no: 2, materi: 'Ghoroibul Qur\'an', val: scores.ghorib ?? 0 },
    { no: 3, materi: 'Ilmu Tajwid', val: scores.tajwid ?? 0 },
    { no: 4, materi: 'Tauhid Dasar', val: scores.tauhid ?? 0 },
    { no: 5, materi: 'Praktik Wudlu', val: scores.wudlu ?? 0 },
    { no: 6, materi: 'Praktik Sholat', val: scores.sholat ?? 0 },
    { no: 7, materi: 'Hafalan Surat Pendek', val: scores.surat ?? 0 },
    { no: 8, materi: 'Hafalan Do\'a Harian', val: scores.doa ?? 0 },
    { no: 9, materi: 'Kitabah', val: scores.kitabah ?? 0 }
  ];

  let total = 0;
  rows.forEach(r => { total += Number(r.val); });
  const rata = (total / rows.length).toFixed(1);

  return `
    <div class="a4-page syahadah-belakang">
      <div>
        <div class="transkrip-header">
          <h2>DAFTAR NILAI UJIAN AKHIR RTQ</h2>
          <h3>TAHUN PELAJARAN ${escapeHtml(cfg.tahunPelajaran || "2025 M / 1447 H")}</h3>
        </div>

        <table class="transkrip-bio">
          <tr>
            <td style="width: 28%; font-weight: bold;">Nama</td>
            <td style="width: 2%;">:</td>
            <td style="width: 70%; font-weight: bold; text-transform: uppercase;">${escapeHtml(s.nama)}</td>
          </tr>
          <tr>
            <td>Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>${escapeHtml(s.ttl || '-')}</td>
          </tr>
          <tr>
            <td>Asal RTQ</td>
            <td>:</td>
            <td>${escapeHtml(s.rtq || cfg.rtqName || 'RTQ Utsmaniyyah III')}</td>
          </tr>
          <tr>
            <td>Nomor Induk Santri</td>
            <td>:</td>
            <td>${escapeHtml(s.noInduk || '-')}</td>
          </tr>
          <tr>
            <td>Nomor Ujian</td>
            <td>:</td>
            <td>${escapeHtml(s.noUjian || '-')}</td>
          </tr>
        </table>

        <table class="transkrip-table">
          <thead>
            <tr>
              <th rowspan="2" style="width: 8%;">NO</th>
              <th rowspan="2" style="width: 42%;">MATA UJIAN</th>
              <th colspan="2" style="width: 50%;">NILAI</th>
            </tr>
            <tr>
              <th style="width: 18%;">ANGKA</th>
              <th style="width: 32%;">HURUF</th>
            </tr>
          </thead>
          <tbody>
            ${rows.map(r => `
              <tr>
                <td class="text-center">${r.no}</td>
                <td>${r.materi}</td>
                <td class="text-center font-bold">${r.val}</td>
                <td style="font-size: 8.5pt;">${angkaKeTerbilang(r.val)}</td>
              </tr>
            `).join('')}
            <tr class="total-row">
              <td colspan="2" style="text-align: center; font-weight: bold;">JUMLAH</td>
              <td class="text-center font-bold">${total}</td>
              <td style="font-size: 8.5pt; font-weight: bold;">${angkaKeTerbilang(total)}</td>
            </tr>
            <tr class="total-row">
              <td colspan="2" style="text-align: center; font-weight: bold;">RATA - RATA</td>
              <td class="text-center font-bold">${rata}</td>
              <td style="font-size: 8.5pt; font-weight: bold;">${angkaKeTerbilang(rata)}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div>
        <div style="text-align: right; margin-bottom: 8px; font-size: 10pt;">
          ${escapeHtml(cfg.tempatTanggal || "Tambak Jaya, 07 September 2025")}
        </div>
        <div class="transkrip-signatures">
          <div class="sig-box">
            <div>Kepala ${escapeHtml(cfg.rtqName || "RTQ Utsmaniyyah III")}</div>
            <div class="sig-space"></div>
            <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">
              ${escapeHtml(cfg.kepalaRtq || "ABU HANIFAN")}
            </div>
          </div>
          <div class="sig-box">
            <div>Ketua Tim Penguji</div>
            <div class="sig-space"></div>
            <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">
              ${escapeHtml(cfg.ketuaPenguji || "IKHWANUL KIROM")}
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

// Formulir Santri Template
function renderFormulirSantri(s) {
  const cfg = appData.config;
  return `
    <div class="a4-page" style="padding: 18mm 20mm;">
      <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
        <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">FORMULIR UJIAN AKHIR RTQ</h2>
        <h3 style="font-size: 11pt; font-weight: normal;">THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR'AN YANBU'A</h3>
        <p style="font-size: 10pt; font-weight: bold;">PERIODE ${escapeHtml(cfg.tahunPelajaran || "2025 M / 1447 H")}</p>
      </div>

      <table style="width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 30px;">
        <tr><td style="width: 32%; padding: 7px 4px;">Nama Lengkap</td><td style="width: 3%;">:</td><td style="font-weight: bold; text-transform: uppercase;">${escapeHtml(s.nama)}</td></tr>
        <tr><td style="padding: 7px 4px;">Jenis Kelamin</td><td>:</td><td>${s.gender === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Ayah Kandung</td><td>:</td><td>${escapeHtml(s.ayah || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Ibu Kandung</td><td>:</td><td>${escapeHtml(s.ibu || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Tempat, Tanggal Lahir</td><td>:</td><td>${escapeHtml(s.ttl || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Alamat Lengkap</td><td>:</td><td>${escapeHtml(s.alamat || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nomor Induk Santri</td><td>:</td><td>${escapeHtml(s.noInduk || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nomor Ujian</td><td>:</td><td>${escapeHtml(s.noUjian || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Lembaga / RTQ</td><td>:</td><td>${escapeHtml(cfg.rtqName || 'RTQ Utsmaniyyah III')}</td></tr>
        <tr><td style="padding: 7px 4px;">Alamat RTQ</td><td>:</td><td>${escapeHtml(cfg.rtqAlamat || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">No. WhatsApp RTQ</td><td>:</td><td>${escapeHtml(cfg.rtqWa || '-')}</td></tr>
        <tr><td style="padding: 7px 4px;">Nama Kepala RTQ</td><td>:</td><td>${escapeHtml(cfg.kepalaRtq || 'Abu Hanifan')}</td></tr>
      </table>

      <div style="display: flex; justify-content: space-between; margin-top: 40px; font-size: 11pt;">
        <div style="text-align: center; width: 200px;">
          <div>Orang Tua / Wali Santri</div>
          <div style="height: 70px;"></div>
          <div style="border-bottom: 1px dashed #333;"></div>
        </div>
        <div style="text-align: center; width: 220px;">
          <div>${escapeHtml(cfg.tempatTanggal || 'Tambak Jaya, Agustus 2025')}</div>
          <div>Kepala ${escapeHtml(cfg.rtqName || 'RTQ Utsmaniyyah III')}</div>
          <div style="height: 55px;"></div>
          <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">${escapeHtml(cfg.kepalaRtq || 'ABU HANIFAN')}</div>
        </div>
      </div>
    </div>
  `;
}

// Print All Santri (Batch)
function printAllSyahadah(docType = 'both') {
  const container = document.getElementById('cetakPreviewArea');
  if (!container) return;

  const { list } = calculateSantriStats();
  if (list.length === 0) {
    alert('Tidak ada data santri untuk dicetak.');
    return;
  }

  let html = '';
  list.forEach(s => {
    if (docType === 'depan' || docType === 'both') {
      html += renderSyahadahDepan(s);
    }
    if (docType === 'belakang' || docType === 'both') {
      html += renderSyahadahBelakang(s);
    }
    if (docType === 'formulir') {
      html += renderFormulirSantri(s);
    }
  });

  container.innerHTML = html;
  setTimeout(() => {
    window.print();
  }, 200);
}

// Print Rekapitulasi Landscape
function printRekapitulasiLandscape() {
  const { sorted } = calculateSantriStats();
  const cfg = appData.config;

  const win = window.open('', '_blank');
  win.document.write(`
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <title>Rekapitulasi Nilai Munaqosyah - ${escapeHtml(cfg.rtqName)}</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #111; }
        .rekap-title { text-align: center; margin-bottom: 16px; }
        .rekap-title h2 { margin: 0 0 4px; font-size: 16pt; text-transform: uppercase; }
        .rekap-title h3 { margin: 0; font-size: 12pt; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 5px 6px; }
        th { background: #eef4f0; text-align: center; font-weight: bold; }
        td.center { text-align: center; }
        td.bold { font-weight: bold; }
        .sig-container { display: flex; justify-content: flex-end; margin-top: 25px; }
        .sig-box { text-align: center; width: 240px; font-size: 10pt; }
        @page { size: A4 landscape; margin: 10mm; }
        @media print { .no-print { display: none; } }
      </style>
    </head>
    <body>
      <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; font-size: 11pt; cursor: pointer; background: #0f5132; color: #fff; border: none; border-radius: 4px;">🖨️ Cetak Dokumen</button>
      </div>
      <div class="rekap-title">
        <h2>DAFTAR NILAI UJIAN AKHIR MUNAQOSYAH</h2>
        <h3>${escapeHtml(cfg.lembaga)} - ${escapeHtml(cfg.wilayah)}</h3>
        <div style="font-size: 10pt; margin-top: 4px;">Unit: <strong>${escapeHtml(cfg.rtqName)}</strong> | Tahun: ${escapeHtml(cfg.tahunPelajaran)}</div>
      </div>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>No Ujian</th>
            <th>Nama Peserta</th>
            <th>Asal RTQ</th>
            <th>Tartil</th>
            <th>Ghorib</th>
            <th>Tajwid</th>
            <th>Tauhid</th>
            <th>Wudlu</th>
            <th>Sholat</th>
            <th>Surat</th>
            <th>Do'a</th>
            <th>Kitabah</th>
            <th>Total</th>
            <th>Rata</th>
            <th>Rank</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          ${sorted.map((s, idx) => `
            <tr>
              <td class="center">${idx + 1}</td>
              <td class="center">${escapeHtml(s.noUjian || '-')}</td>
              <td class="bold">${escapeHtml(s.nama)}</td>
              <td>${escapeHtml(s.rtq || '-')}</td>
              <td class="center">${s.scores?.tartil ?? 0}</td>
              <td class="center">${s.scores?.ghorib ?? 0}</td>
              <td class="center">${s.scores?.tajwid ?? 0}</td>
              <td class="center">${s.scores?.tauhid ?? 0}</td>
              <td class="center">${s.scores?.wudlu ?? 0}</td>
              <td class="center">${s.scores?.sholat ?? 0}</td>
              <td class="center">${s.scores?.surat ?? 0}</td>
              <td class="center">${s.scores?.doa ?? 0}</td>
              <td class="center">${s.scores?.kitabah ?? 0}</td>
              <td class="center bold">${s.totalNilai}</td>
              <td class="center bold">${s.rataRata}</td>
              <td class="center bold">${s.rank}</td>
              <td class="center ${s.isLulus ? '' : 'style="color:red;"'}">${s.isLulus ? 'LULUS' : 'TIDAK LULUS'}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
      <div class="sig-container">
        <div class="sig-box">
          <div>${escapeHtml(cfg.tempatTanggal)}</div>
          <div style="margin-top: 4px; font-weight: bold;">Ketua Panitia Ujian</div>
          <div style="height: 60px;"></div>
          <div style="font-weight: bold; text-decoration: underline;">${escapeHtml(cfg.ketuaPanitia || 'A. Muslih Abdurrahman')}</div>
        </div>
      </div>
    </body>
    </html>
  `);
  win.document.close();
}

// ==========================================
// 6. PENGATURAN LEMBAGA & BACKUP
// ==========================================
function populateConfigForm() {
  const cfg = appData.config || {};
  document.getElementById('cfgLembaga').value = cfg.lembaga || '';
  document.getElementById('cfgMetode').value = cfg.metode || '';
  document.getElementById('cfgWilayah').value = cfg.wilayah || '';
  document.getElementById('cfgRtqName').value = cfg.rtqName || '';
  document.getElementById('cfgRtqAlamat').value = cfg.rtqAlamat || '';
  document.getElementById('cfgRtqWa').value = cfg.rtqWa || '';
  document.getElementById('cfgTahunPelajaran').value = cfg.tahunPelajaran || '';
  document.getElementById('cfgTanggalH').value = cfg.tanggalMunaqosyahH || '';
  document.getElementById('cfgTanggalM').value = cfg.tanggalMunaqosyahM || '';
  document.getElementById('cfgTanggalSyahadah').value = cfg.tanggalSyahadah || '';
  document.getElementById('cfgTempatTanggal').value = cfg.tempatTanggal || '';
  document.getElementById('cfgKepalaRtq').value = cfg.kepalaRtq || '';
  document.getElementById('cfgKetuaPenguji').value = cfg.ketuaPenguji || '';
  document.getElementById('cfgKetuaPanitia').value = cfg.ketuaPanitia || '';
  document.getElementById('cfgPimpinanLajnah').value = cfg.pimpinanLajnah || '';
  document.getElementById('cfgKkm').value = cfg.kkm || 65;
}

function handleSaveConfig(e) {
  e.preventDefault();
  appData.config = {
    ...appData.config,
    lembaga: document.getElementById('cfgLembaga').value.trim(),
    metode: document.getElementById('cfgMetode').value.trim(),
    wilayah: document.getElementById('cfgWilayah').value.trim(),
    rtqName: document.getElementById('cfgRtqName').value.trim(),
    rtqAlamat: document.getElementById('cfgRtqAlamat').value.trim(),
    rtqWa: document.getElementById('cfgRtqWa').value.trim(),
    tahunPelajaran: document.getElementById('cfgTahunPelajaran').value.trim(),
    tanggalMunaqosyahH: document.getElementById('cfgTanggalH').value.trim(),
    tanggalMunaqosyahM: document.getElementById('cfgTanggalM').value.trim(),
    tanggalSyahadah: document.getElementById('cfgTanggalSyahadah').value.trim(),
    tempatTanggal: document.getElementById('cfgTempatTanggal').value.trim(),
    kepalaRtq: document.getElementById('cfgKepalaRtq').value.trim(),
    ketuaPenguji: document.getElementById('cfgKetuaPenguji').value.trim(),
    ketuaPanitia: document.getElementById('cfgKetuaPanitia').value.trim(),
    pimpinanLajnah: document.getElementById('cfgPimpinanLajnah').value.trim(),
    kkm: Number(document.getElementById('cfgKkm').value) || 65
  };

  saveData();
  alert('Pengaturan lembaga berhasil disimpan!');
  renderAll();
}

// Backup & Restore
function backupDataJson() {
  const str = JSON.stringify(appData, null, 2);
  const blob = new Blob([str], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `backup_munaqosyah_utsmaniyyah_${new Date().toISOString().split('T')[0]}.json`;
  a.click();
}

function restoreDataJson(e) {
  const file = e.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(evt) {
    try {
      const parsed = JSON.parse(evt.target.result);
      if (parsed.santri && parsed.config) {
        if (confirm('Apakah Anda yakin ingin memulihkan data dari file cadangan ini? Data saat ini akan ditimpa.')) {
          appData = parsed;
          saveData();
          renderAll();
          alert('Data berhasil dipulihkan!');
        }
      } else {
        alert('Format file JSON tidak valid!');
      }
    } catch (err) {
      alert('Gagal membaca file JSON: ' + err.message);
    }
  };
  reader.readAsText(file);
}

function resetToDefaultData() {
  if (confirm('Peringatan: Seluruh data akan dikembalikan ke data awal bawaan Excel aplikasi.xlsx. Lanjutkan?')) {
    localStorage.removeItem('utsmaniyyah_munaqosyah_data');
    loadData().then(() => {
      renderAll();
      alert('Data berhasil di-reset ke data bawaan Excel.');
    });
  }
}

// ==========================================
// UTILITY FUNCTIONS & EVENT LISTENERS
// ==========================================
function setupEventListeners() {
  // Santri search input
  const searchInput = document.getElementById('searchSantriInput');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      renderSantriTable();
    });
  }

  // Santri form
  const santriForm = document.getElementById('santriForm');
  if (santriForm) {
    santriForm.addEventListener('submit', handleSaveSantri);
  }

  // Config form
  const configForm = document.getElementById('configForm');
  if (configForm) {
    configForm.addEventListener('submit', handleSaveConfig);
  }

  // Cetak selector change
  const cetakSelect = document.getElementById('cetakSantriSelect');
  if (cetakSelect) {
    cetakSelect.addEventListener('change', generateCetakPreview);
  }

  const docTypeSelect = document.getElementById('cetakDocTypeSelect');
  if (docTypeSelect) {
    docTypeSelect.addEventListener('change', generateCetakPreview);
  }
}

function openModal(modalId) {
  const m = document.getElementById(modalId);
  if (m) m.classList.add('show');
}

function closeModal(modalId) {
  const m = document.getElementById(modalId);
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
