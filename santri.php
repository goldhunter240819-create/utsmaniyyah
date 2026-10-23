<?php
/**
 * Data Santri Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Data Santri Munaqosyah';
$currentPage = 'santri';

require_once __DIR__ . '/includes/header.php';
?>

<section id="tab-santri" class="tab-content active">
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>👥</i> <span>Daftar Santri Peserta Munaqosyah</span>
      </div>
      <button class="btn btn-gold btn-sm" onclick="openAddSantriModal()">
        ➕ Tambah Santri
      </button>
    </div>
    <div class="card-body">
      <div class="filter-toolbar">
        <div class="search-box">
          <i>🔍</i>
          <input type="text" id="searchSantriInput" class="search-input" placeholder="Cari nama, no. induk, atau ayah...">
        </div>
        <div>
          <button class="btn btn-outline btn-sm" onclick="exportRekapExcel()">📥 Ekspor Data ke Excel</button>
        </div>
      </div>

      <div class="table-responsive" style="overflow-x: auto; border-radius: 8px; border: 1px solid var(--gray-300);">
        <table class="table-custom table-excel" style="min-width: 1400px;">
          <thead>
            <tr>
              <th class="text-center th-sortable" style="width: 65px;" onclick="sortSantriTable('no')" title="Klik untuk mengurutkan Nomor">
                <div class="th-content center">
                  <span>No</span>
                  <span class="sort-arrows-group" data-col="no">
                    <button type="button" class="btn-sort-arrow arrow-up active" onclick="event.stopPropagation(); sortSantriTable('no', 'asc');" title="Urutkan 1 - 9">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('no', 'desc');" title="Urutkan 9 - 1">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 195px;" onclick="sortSantriTable('nama')" title="Klik untuk mengurutkan Nama Santri">
                <div class="th-content">
                  <span>Nama Lengkap Santri</span>
                  <span class="sort-arrows-group" data-col="nama">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('nama', 'asc');" title="Urutkan A ke Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('nama', 'desc');" title="Urutkan Z ke A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="text-center th-sortable" style="width: 90px;" onclick="sortSantriTable('gender')" title="Klik untuk mengurutkan Jenis Kelamin">
                <div class="th-content center">
                  <span>L / P</span>
                  <span class="sort-arrows-group" data-col="gender">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('gender', 'asc');" title="Urutkan L dulu">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('gender', 'desc');" title="Urutkan P dulu">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 140px;" onclick="sortSantriTable('rtq')" title="Klik untuk mengurutkan Unit RTQ">
                <div class="th-content">
                  <span>Unit RTQ</span>
                  <span class="sort-arrows-group" data-col="rtq">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('rtq', 'asc');" title="Urutkan RTQ A-Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('rtq', 'desc');" title="Urutkan RTQ Z-A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 170px;" onclick="sortSantriTable('ttl')" title="Klik untuk mengurutkan Tempat/Tanggal Lahir">
                <div class="th-content">
                  <span>Tempat, Tanggal Lahir</span>
                  <span class="sort-arrows-group" data-col="ttl">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('ttl', 'asc');" title="Urutkan A-Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('ttl', 'desc');" title="Urutkan Z-A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 140px;" onclick="sortSantriTable('ayah')" title="Klik untuk mengurutkan Nama Ayah">
                <div class="th-content">
                  <span>Nama Ayah</span>
                  <span class="sort-arrows-group" data-col="ayah">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('ayah', 'asc');" title="Urutkan A-Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('ayah', 'desc');" title="Urutkan Z-A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 140px;" onclick="sortSantriTable('ibu')" title="Klik untuk mengurutkan Nama Ibu">
                <div class="th-content">
                  <span>Nama Ibu</span>
                  <span class="sort-arrows-group" data-col="ibu">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('ibu', 'asc');" title="Urutkan A-Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('ibu', 'desc');" title="Urutkan Z-A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 230px;" onclick="sortSantriTable('alamat')" title="Klik untuk mengurutkan Alamat">
                <div class="th-content">
                  <span>Alamat Lengkap</span>
                  <span class="sort-arrows-group" data-col="alamat">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('alamat', 'asc');" title="Urutkan A-Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('alamat', 'desc');" title="Urutkan Z-A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 110px;" onclick="sortSantriTable('no_induk')" title="Klik untuk mengurutkan No. Induk">
                <div class="th-content">
                  <span>No. Induk</span>
                  <span class="sort-arrows-group" data-col="no_induk">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('no_induk', 'asc');" title="Urutkan Nomor Terkecil">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('no_induk', 'desc');" title="Urutkan Nomor Terbesar">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 110px;" onclick="sortSantriTable('no_ujian')" title="Klik untuk mengurutkan No. Ujian">
                <div class="th-content">
                  <span>No. Ujian</span>
                  <span class="sort-arrows-group" data-col="no_ujian">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('no_ujian', 'asc');" title="Urutkan Nomor Terkecil">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('no_ujian', 'desc');" title="Urutkan Nomor Terbesar">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="th-sortable" style="min-width: 170px;" onclick="sortSantriTable('no_syahadah')" title="Klik untuk mengurutkan No. Syahadah">
                <div class="th-content">
                  <span>No. Syahadah</span>
                  <span class="sort-arrows-group" data-col="no_syahadah">
                    <button type="button" class="btn-sort-arrow arrow-up" onclick="event.stopPropagation(); sortSantriTable('no_syahadah', 'asc');" title="Urutkan No. Syahadah A-Z">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 0L8 6H0z" fill="currentColor"/></svg>
                    </button>
                    <button type="button" class="btn-sort-arrow arrow-down" onclick="event.stopPropagation(); sortSantriTable('no_syahadah', 'desc');" title="Urutkan No. Syahadah Z-A">
                      <svg width="7" height="5" viewBox="0 0 8 6"><path d="M4 6L0 0h8z" fill="currentColor"/></svg>
                    </button>
                  </span>
                </div>
              </th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody id="santriTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
