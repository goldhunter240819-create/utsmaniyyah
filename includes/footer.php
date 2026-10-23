<?php
/**
 * Shared Application Footer & Modals
 */
?>
      </main>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT SANTRI -->
  <div class="modal-backdrop" id="santriModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="santriModalTitle">Tambah Santri Baru</h3>
        <button class="modal-close" onclick="closeModal('santriModal')">✕</button>
      </div>
      <form id="santriForm">
        <div class="modal-body">
          <input type="hidden" id="santriIdInput">
          <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Nama Lengkap Santri *</label>
              <input type="text" id="inputNama" class="form-control" placeholder="Contoh: Ahmad Alhabib Ramadhan" required>
            </div>
            <div class="form-group">
              <label class="form-label">Jenis Kelamin *</label>
              <select id="inputGender" class="form-control" required>
                <option value="L">Laki-laki (Bin)</option>
                <option value="P">Perempuan (Binti)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Asal Unit RTQ *</label>
              <select id="inputRtq" class="form-control" required></select>
            </div>
            <div class="form-group">
              <label class="form-label">Tahun Pelajaran *</label>
              <select id="inputPeriode" class="form-control" required></select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Tempat, Tanggal Lahir</label>
              <input type="text" id="inputTtl" class="form-control" placeholder="Contoh: Tambak Jaya, 12 Agustus 2012">
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ayah Kandung</label>
              <input type="text" id="inputAyah" class="form-control" placeholder="Nama ayah">
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ibu Kandung</label>
              <input type="text" id="inputIbu" class="form-control" placeholder="Nama ibu">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Alamat Lengkap</label>
              <input type="text" id="inputAlamat" class="form-control" placeholder="Dusun, Desa, Kecamatan, Kabupaten">
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Induk Santri</label>
              <input type="text" id="inputNoInduk" class="form-control" placeholder="0001">
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Ujian</label>
              <input type="text" id="inputNoUjian" class="form-control" placeholder="0001">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Nomor Syahadah</label>
              <input type="text" id="inputNoSyahadah" class="form-control" placeholder="LMY-LB/RTQ-U3/001/09/10/2025">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('santriModal')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan ke Database</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT UNIT RTQ -->
  <div class="modal-backdrop" id="rtqModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="rtqModalTitle">Tambah Unit RTQ</h3>
        <button class="modal-close" onclick="closeModal('rtqModal')">✕</button>
      </div>
      <form id="rtqForm">
        <div class="modal-body">
          <input type="hidden" id="rtqIdInput">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Kode Singkat RTQ *</label>
              <input type="text" id="inputRtqKode" class="form-control" placeholder="Contoh: RTQ-U1" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Lengkap Unit RTQ *</label>
              <input type="text" id="inputRtqNamaModal" class="form-control" placeholder="Contoh: RTQ Utsmaniyyah I" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Alamat Lengkap Unit RTQ *</label>
              <input type="text" id="inputRtqAlamatModal" class="form-control" placeholder="Alamat dusun/desa, kecamatan" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Kepala RTQ *</label>
              <input type="text" id="inputRtqKepalaModal" class="form-control" placeholder="Nama Kepala RTQ" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ketua Tim Penguji *</label>
              <input type="text" id="inputRtqPengujiModal" class="form-control" placeholder="Nama Ketua Tim Penguji" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">No. WhatsApp RTQ</label>
              <input type="text" id="inputRtqWaModal" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
            <div id="rtqAccountSection" style="grid-column: 1 / -1; margin-top: 10px; padding: 14px; background: #f0fdf4; border: 1px dashed #86efac; border-radius: 8px;">
              <div style="font-weight: 700; font-size: 0.86rem; color: #166534; margin-bottom: 6px;">🔐 Buat Akun Login Pengurus RTQ (Opsional)</div>
              <div style="font-size: 0.78rem; color: #15803d; margin-bottom: 10px;">Jika diisi, akun login pengurus untuk unit RTQ ini akan dibuat secara otomatis.</div>
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label" style="font-size: 0.8rem;">Username Login</label>
                  <input type="text" id="inputRtqUsername" class="form-control" placeholder="Contoh: nurulyasin">
                </div>
                <div class="form-group">
                  <label class="form-label" style="font-size: 0.8rem;">Password Login</label>
                  <input type="password" id="inputRtqPassword" class="form-control" placeholder="Minimal 3 karakter">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
          <button type="button" id="btnDeleteRtqModal" class="btn btn-danger btn-sm" style="display: none;" onclick="deleteRtqFromModal()">Hapus RTQ</button>
          <div style="display: flex; gap: 8px; margin-left: auto;">
            <button type="button" class="btn btn-outline" onclick="closeModal('rtqModal')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Unit RTQ</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT AKUN PENGGUNA -->
  <div class="modal-backdrop" id="userModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="userModalTitle">Tambah Akun Pengurus RTQ</h3>
        <button class="modal-close" onclick="closeModal('userModal')">✕</button>
      </div>
      <form id="userForm">
        <div class="modal-body">
          <input type="hidden" id="userIdInput">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Username Login *</label>
              <input type="text" id="inputUserUsername" class="form-control" placeholder="Contoh: u1, nurulyasin" required>
            </div>
            <div class="form-group">
              <label class="form-label" id="labelUserPassword">Password Login *</label>
              <input type="text" id="inputUserPassword" class="form-control" placeholder="Password akun">
              <small id="helpUserPassword" style="display: none; color: var(--dark-500); font-size: 0.75rem; margin-top: 4px;">Kosongkan jika tidak ingin mengubah password.</small>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Nama Pengurus / Akun *</label>
              <input type="text" id="inputUserNama" class="form-control" placeholder="Contoh: Admin RTQ Utsmaniyyah I" required>
            </div>
            <div class="form-group">
              <label class="form-label">Hak Akses (Role) *</label>
              <select id="inputUserRole" class="form-control" required onchange="handleUserRoleChange()">
                <option value="PENGURUS_RTQ">PENGURUS_RTQ (Hanya Unit RTQ Tertentu)</option>
                <option value="DEVELOPER">DEVELOPER (Akses Penuh Semua RTQ)</option>
              </select>
            </div>
            <div class="form-group" id="groupUserRtq">
              <label class="form-label">Unit RTQ yang Dikelola *</label>
              <select id="inputUserRtq" class="form-control">
                <!-- Diisi via JavaScript -->
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
          <button type="button" class="btn btn-outline" onclick="closeModal('userModal')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Akun</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT PERIODE UJIAN -->
  <div class="modal-backdrop" id="periodeModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="periodeModalTitle">Tambah Tahun / Periode Ujian</h3>
        <button class="modal-close" onclick="closeModal('periodeModal')">✕</button>
      </div>
      <form id="periodeForm">
        <div class="modal-body">
          <input type="hidden" id="periodeIdInput">
          <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Tahun Pelajaran *</label>
              <input type="text" id="inputPeriodeTahun" class="form-control" placeholder="Contoh: 2025 M / 1447 H" required>
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Ujian (Hijriyah)</label>
              <input type="text" id="inputPeriodeH" class="form-control" placeholder="Contoh: 14 Rabi'ul Awwal 1447 H">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Ujian (Masehi)</label>
              <input type="text" id="inputPeriodeM" class="form-control" placeholder="Contoh: 07 September 2025 M">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Terbit Syahadah</label>
              <input type="text" id="inputPeriodeSyahadah" class="form-control" placeholder="Contoh: 21 September 2025">
            </div>
            <div class="form-group">
              <label class="form-label">Titimangsa Transkrip Nilai</label>
              <input type="text" id="inputPeriodeTitimangsa" class="form-control" placeholder="Contoh: Tambak Jaya, 07 September 2025">
            </div>
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
          <button type="button" id="btnDeletePeriodeModal" class="btn btn-danger btn-sm" style="display: none;" onclick="deletePeriodeFromModal()">Hapus Periode</button>
          <div style="display: flex; gap: 8px; margin-left: auto;">
            <button type="button" class="btn btn-outline" onclick="closeModal('periodeModal')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Periode</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Main JavaScript Logic with Auto Cache Buster -->
  <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
