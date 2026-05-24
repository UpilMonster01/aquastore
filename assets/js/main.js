function openLogin() {
    document.getElementById('loginModal')?.classList.add('show');
}

function closeLogin() {
    document.getElementById('loginModal')?.classList.remove('show');
}

function openModal(id) {
    document.getElementById(id)?.classList.add('show');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('show');
}

window.addEventListener('click', function (e) {
    const login = document.getElementById('loginModal');

    if (e.target === login) {
        closeLogin();
    }

    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }
});

function hitungTotal() {
    const pengiriman = document.getElementById('pengiriman');
    const subtotal = document.getElementById('subtotal');
    const ongkir = document.getElementById('ongkir');
    const grandTotal = document.getElementById('grandTotal');

    if (!pengiriman || !subtotal || !ongkir || !grandTotal) return;

    const sub = parseInt(subtotal.dataset.total || '0');
    const ong = pengiriman.value === 'Kurir' ? 15000 : 0;

    ongkir.innerText = formatRupiah(ong);
    grandTotal.innerText = formatRupiah(sub + ong);
}

function formatRupiah(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function previewFoto(input) {
    const file = input.files[0];
    if (!file) return;

    const area = input.closest('.upload-area');
    let preview = area.querySelector('.preview-image');
    const placeholder = area.querySelector('.upload-placeholder');

    if (!preview) {
        preview = document.createElement('img');
        preview.className = 'preview-image';
        area.querySelector('.upload-preview-wrapper')?.appendChild(preview);
    }

    const reader = new FileReader();

    reader.onload = function (e) {
        preview.src = e.target.result;
        preview.style.display = 'block';

        if (placeholder) {
            placeholder.style.display = 'none';
        }
    };

    reader.readAsDataURL(file);
}
