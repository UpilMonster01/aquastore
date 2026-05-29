<input type="text" name="nama" placeholder="Nama perlengkapan" value="<?= e($item['nama'] ?? '') ?>" required>

<select name="kategori" required>
    <?php foreach(['Pakan','Filter','Aerator','Heater','Obat','Lampu','Substrate','Dekorasi','Lainnya'] as $v): ?>
        <option value="<?= $v ?>" <?= (($item['kategori'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<input type="number" name="harga" placeholder="Harga" value="<?= e($item['harga'] ?? '') ?>" required>

<input type="number" name="stok" placeholder="Stok" value="<?= e($item['stok'] ?? '') ?>" required>

<select name="status" required>
    <?php foreach(['Tersedia','Habis'] as $v): ?>
        <option value="<?= $v ?>" <?= (($item['status'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<div class="upload-box">
    <label class="upload-area">
        <div class="upload-preview-wrapper">
            <?php if (!empty($item['foto'])): ?>
                <img src="../uploads/perlengkapan/<?= e($item['foto']) ?>?v=<?= time() ?>" class="preview-image">
            <?php else: ?>
                <div class="upload-placeholder">
                    <span>📸</span>
                    <h3>Upload Foto</h3>
                    <p>Klik untuk pilih gambar</p>
                </div>
                <img class="preview-image" style="display:none;">
            <?php endif; ?>
        </div>

        <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" onchange="previewFoto(this)">
    </label>
</div>

<textarea name="deskripsi" placeholder="Deskripsi perlengkapan"><?= e($item['deskripsi'] ?? '') ?></textarea>