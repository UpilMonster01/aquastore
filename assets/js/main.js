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
    if (!area) return;

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

function openAuthDrawer(tab = 'login') {
    document.getElementById('authDrawer')?.classList.add('show');
    document.getElementById('authOverlay')?.classList.add('show');
    document.body.classList.add('drawer-open');

    showAuthTab(tab);
}

function closeAuthDrawer() {
    document.getElementById('authDrawer')?.classList.remove('show');
    document.getElementById('authOverlay')?.classList.remove('show');
    document.body.classList.remove('drawer-open');
}

function showAuthTab(tab) {
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginPanel = document.getElementById('loginPanel');
    const registerPanel = document.getElementById('registerPanel');

    if (!loginTab || !registerTab || !loginPanel || !registerPanel) return;

    loginTab.classList.remove('active');
    registerTab.classList.remove('active');
    loginPanel.classList.remove('active');
    registerPanel.classList.remove('active');

    if (tab === 'register') {
        registerTab.classList.add('active');
        registerPanel.classList.add('active');
    } else {
        loginTab.classList.add('active');
        loginPanel.classList.add('active');
    }
}

function toggleAccountMenu(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    document.getElementById('accountDropdown')?.classList.toggle('show');
}

function konfirmasiBatal(e) {
    e.preventDefault();

    if (confirm("Apakah Anda yakin ingin membatalkan checkout?")) {
        window.location.href = "keranjang.php";
    }
}

document.addEventListener('DOMContentLoaded', function () {
    hitungTotal();

    const params = new URLSearchParams(window.location.search);

    if (params.get('auth') === 'login') {
        openAuthDrawer('login');
    }

    if (params.get('auth') === 'register') {
        openAuthDrawer('register');
    }
});

window.addEventListener('click', function (e) {
    const login = document.getElementById('loginModal');

    if (e.target === login) {
        closeLogin();
    }

    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }

    const trigger = document.querySelector('.account-pill');
    const dropdown = document.getElementById('accountDropdown');

    if (
        dropdown &&
        trigger &&
        !trigger.contains(e.target) &&
        !dropdown.contains(e.target)
    ) {
        dropdown.classList.remove('show');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAuthDrawer();

        const dropdown = document.getElementById('accountDropdown');
        dropdown?.classList.remove('show');
    }
});

/* =========================
   ACTIVE MENU HEADER
========================= */

document.addEventListener("DOMContentLoaded", function () {
    const currentFile = window.location.pathname.split("/").pop() || "index.php";

    const activeGroups = {
        "index.php": ["index.php", ""],
        "katalog.php": ["katalog.php", "detail.php"],
        "perawatan.php": ["perawatan.php"],
        "cek-pesanan.php": ["cek-pesanan.php"],
        "keranjang.php": ["keranjang.php", "checkout.php"],
        "profil.php": ["profil.php"],
        "pesanan-saya.php": ["pesanan-saya.php"]
    };

    const menuLinks = document.querySelectorAll(
        ".nav-menu a, .nav-links a, .navbar-menu a, .navbar nav a, header nav a, .account-menu a"
    );

    menuLinks.forEach(function (link) {
        const href = link.getAttribute("href");

        if (!href || href.startsWith("#") || href.startsWith("javascript:")) {
            return;
        }

        let linkFile = href.split("?")[0].split("/").pop();

        if (linkFile === "") {
            linkFile = "index.php";
        }

        Object.keys(activeGroups).forEach(function (mainFile) {
            const group = activeGroups[mainFile];

            if (group.includes(currentFile) && group.includes(linkFile)) {
                link.classList.add("active-page");
            }
        });
    });
});