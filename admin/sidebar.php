<aside class="admin-sidebar">
    <div class="side-brand">
        <div class="brand-icon">🐟</div>
        <h2>AquaStore</h2><small>Admin Panel</small>
    </div>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="ikan.php">🐠 Data Ikan</a>
    <a href="perlengkapan.php">🛠️ Perlengkapan</a>
    <a href="pesanan.php">🛒 Pesanan</a>
    <a href="perawatan.php">💧 Perawatan</a>
    <a href="keuangan.php">💰 Keuangan</a>
    <a href="../index.php">🏠 Website</a>
    <a href="../proses/logout.php">🚪 Logout</a>
</aside>

<style>
/* =========================
   ADMIN SIDEBAR ACTIVE MENU
========================= */

.admin-sidebar a.admin-active-page,
.sidebar a.admin-active-page,
.admin-menu a.admin-active-page,
.admin-nav a.admin-active-page {
    background: rgba(22, 119, 255, .12) !important;
    color: #1677ff !important;
    font-weight: 900 !important;
    border-radius: 14px;
    position: relative;
}

.admin-sidebar a.admin-active-page::before,
.sidebar a.admin-active-page::before,
.admin-menu a.admin-active-page::before,
.admin-nav a.admin-active-page::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 5px;
    height: 60%;
    border-radius: 999px;
    background: #1677ff;
}

.admin-sidebar a.admin-active-page span,
.sidebar a.admin-active-page span,
.admin-menu a.admin-active-page span,
.admin-nav a.admin-active-page span {
    color: #1677ff !important;
}
</style>

<script>
(function () {
    const pathParts = window.location.pathname.split("/");
    let currentFile = pathParts[pathParts.length - 1];

    if (!currentFile || currentFile === "") {
        currentFile = "index.php";
    }

    const activeAdminMap = {
        "index.php": ["index.php", "dashboard.php"],
        "dashboard.php": ["index.php", "dashboard.php"],

        "ikan.php": [
            "ikan.php",
            "tambah-ikan.php",
            "edit-ikan.php"
        ],

        "perlengkapan.php": [
            "perlengkapan.php",
            "tambah-perlengkapan.php",
            "edit-perlengkapan.php"
        ],

        "pesanan.php": [
            "pesanan.php"
        ],

        "perawatan.php": [
            "perawatan.php",
            "tambah-perawatan.php",
            "edit-perawatan.php"
        ],

        "pengeluaran.php": [
            "pengeluaran.php",
            "tambah-pengeluaran.php",
            "edit-pengeluaran.php"
        ],

        "logout.php": [
            "logout.php"
        ]
    };

    const sidebarLinks = document.querySelectorAll(
        ".admin-sidebar a[href], .sidebar a[href], .admin-menu a[href], .admin-nav a[href]"
    );

    sidebarLinks.forEach(function (link) {
        const href = link.getAttribute("href");

        if (!href || href.startsWith("#") || href.startsWith("javascript:")) {
            return;
        }

        let linkFile = "";

        try {
            const linkUrl = new URL(link.href);
            const linkParts = linkUrl.pathname.split("/");
            linkFile = linkParts[linkParts.length - 1];

            if (!linkFile || linkFile === "") {
                linkFile = "index.php";
            }
        } catch (e) {
            linkFile = href.split("?")[0].split("/").pop();

            if (!linkFile || linkFile === "") {
                linkFile = "index.php";
            }
        }

        Object.keys(activeAdminMap).forEach(function (mainPage) {
            const group = activeAdminMap[mainPage];

            if (group.includes(currentFile) && group.includes(linkFile)) {
                link.classList.add("admin-active-page");
            }
        });
    });
})();

</script>

<script>
(function () {
    /*
        FIX:
        Supaya link "Website" tidak ikut aktif saat Dashboard admin aktif.
        Yang boleh aktif hanya link yang URL-nya berada di folder /admin/.
    */

    const currentPath = window.location.pathname;
    const pathParts = currentPath.split("/");
    let currentFile = pathParts[pathParts.length - 1];

    if (!currentFile || currentFile === "") {
        currentFile = "index.php";
    }

    const activeAdminMap = {
        "dashboard": ["index.php", "dashboard.php"],
        "ikan": ["ikan.php", "tambah-ikan.php", "edit-ikan.php"],
        "perlengkapan": ["perlengkapan.php", "tambah-perlengkapan.php", "edit-perlengkapan.php"],
        "pesanan": ["pesanan.php"],
        "perawatan": ["perawatan.php", "tambah-perawatan.php", "edit-perawatan.php"],
        "pengeluaran": ["pengeluaran.php", "tambah-pengeluaran.php", "edit-pengeluaran.php"]
    };

    const sidebarLinks = document.querySelectorAll(
        ".admin-sidebar a[href], .sidebar a[href], .admin-menu a[href], .admin-nav a[href]"
    );

    /*
        Bersihkan class aktif lama dulu.
        Ini yang bikin Website tidak ikut nyala lagi.
    */
    sidebarLinks.forEach(function (link) {
        link.classList.remove("admin-active-page");
    });

    sidebarLinks.forEach(function (link) {
        const href = link.getAttribute("href");

        if (!href || href.startsWith("#") || href.startsWith("javascript:")) {
            return;
        }

        let linkUrl;
        let linkPath;
        let linkFile;

        try {
            linkUrl = new URL(link.href);
            linkPath = linkUrl.pathname;
            linkFile = linkPath.split("/").pop();

            if (!linkFile || linkFile === "") {
                linkFile = "index.php";
            }
        } catch (e) {
            return;
        }

        /*
            PENTING:
            Kalau link bukan ke folder admin, jangan dikasih aktif.
            Jadi link Website / Homepage tidak akan aktif.
        */
        if (!linkPath.includes("/admin/")) {
            return;
        }

        Object.keys(activeAdminMap).forEach(function (menuKey) {
            const group = activeAdminMap[menuKey];

            if (group.includes(currentFile) && group.includes(linkFile)) {
                link.classList.add("admin-active-page");
            }
        });
    });
})();
</script>