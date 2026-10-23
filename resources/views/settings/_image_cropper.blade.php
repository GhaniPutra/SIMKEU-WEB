<dialog class="image-cropper-modal" id="image-cropper-modal" aria-labelledby="image-cropper-title" aria-describedby="image-cropper-help">
    <section class="image-cropper-dialog">
        <header class="image-cropper-header">
            <button class="image-cropper-cancel" type="button" data-crop-cancel>Batal</button>
            <h2 id="image-cropper-title">Atur gambar</h2>
            <button class="image-cropper-select" type="button" data-crop-select disabled>Pilih</button>
        </header>
        <div class="image-cropper-stage">
            <canvas id="image-cropper-canvas" width="320" height="320" tabindex="0" role="img" aria-label="Area potong gambar persegi. Seret gambar untuk mengatur bagian yang dipakai, atau gunakan tombol panah."></canvas>
        </div>
        <div class="image-cropper-controls">
            <p id="image-cropper-help"><i class="fas fa-arrows-up-down-left-right" aria-hidden="true"></i> Geser gambar untuk mengatur potongan</p>
            <label for="image-cropper-zoom"><i class="fas fa-magnifying-glass-plus" aria-hidden="true"></i><span class="account-sr-only">Perbesar gambar</span></label>
            <input id="image-cropper-zoom" type="range" min="100" max="300" value="100" step="1" aria-label="Perbesar gambar">
            <output class="image-cropper-zoom-value" id="image-cropper-zoom-value" for="image-cropper-zoom">100%</output>
        </div>
    </section>
</dialog>
