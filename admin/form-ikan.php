<input type="text" name="nama" placeholder="Nama ikan" value="<?= e($i['nama'] ?? '') ?>" required>

<input type="text" name="nama_latin" placeholder="Nama latin" value="<?= e($i['nama_latin'] ?? '') ?>">

<select name="kategori_air" required>
    <?php foreach(['Laut','Tawar','Payau'] as $v): ?>
        <option value="<?= $v ?>" <?= (($i['kategori_air'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="kategori_sifat" required>
    <?php foreach(['Predator','Non-Predator'] as $v): ?>
        <option value="<?= $v ?>" <?= (($i['kategori_sifat'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="kategori_jenis" required>
    <?php foreach(['Hias','Konsumsi','Langka'] as $v): ?>
        <option value="<?= $v ?>" <?= (($i['kategori_jenis'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<input type="number" name="harga" placeholder="Harga" value="<?= e($i['harga'] ?? '') ?>" required>

<input type="number" name="stok" placeholder="Stok" value="<?= e($i['stok'] ?? '') ?>" required>

<input type="number" step="0.1" name="ukuran_cm" placeholder="Ukuran cm" value="<?= e($i['ukuran_cm'] ?? '') ?>">

<select name="tingkat_perawatan" required>
    <?php foreach(['Mudah','Sedang','Sulit'] as $v): ?>
        <option value="<?= $v ?>" <?= (($i['tingkat_perawatan'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="status" required>
    <?php foreach(['Tersedia','Habis','Pre-order'] as $v): ?>
        <option value="<?= $v ?>" <?= (($i['status'] ?? '') === $v) ? 'selected' : '' ?>>
            <?= $v ?>
        </option>
    <?php endforeach; ?>
</select>

<div class="upload-box">
    <label class="upload-area">
        <div class="upload-preview-wrapper">
            <?php if (!empty($i['foto'])): ?>
                <img src="../uploads/ikan/<?= e($i['foto']) ?>?v=<?= time() ?>" class="preview-image">
            <?php else: ?>
                <div class="upload-placeholder">
                    <span>📸</span>
                    <h3>Upload Foto Ikan</h3>
                    <p>Klik untuk pilih gambar JPG, PNG, atau WEBP</p>
                </div>
                <img class="preview-image" style="display:none;">
            <?php endif; ?>
        </div>

        <input 
            type="file" 
            name="foto" 
            accept=".jpg,.jpeg,.png,.webp"
            onchange="previewFoto(this)"
        >
    </label>
</div>

<textarea name="deskripsi" placeholder="Deskripsi ikan"><?= e($i['deskripsi'] ?? '') ?></textarea>

<textarea name="tips_perawatan" placeholder="Tips perawatan ikan"><?= e($i['tips_perawatan'] ?? '') ?></textarea>