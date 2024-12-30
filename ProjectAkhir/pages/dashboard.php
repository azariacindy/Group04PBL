<?php
include_once(__DIR__ . '/../lib/Session.php');
require_once __DIR__ . '/../Model/PrestasiModel.php';

$session = new Session();
$prestasiModel = new PrestasiModel();

if ($session->get('is_login') !== true) {
    header('Location: login.php');
    exit;
}

$role = $session->get('role');
$username = $session->get('user');
$total_prestasi = $prestasiModel->countPrestasiByUser($session->get('id_user'));
$total_validated = $prestasiModel->countValidatedPrestasi();

include_once(__DIR__ . '/../Model/GlobalModel.php');
$global = new GlobalModel();

if ($role == 'admin') {
?>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="welcome-title">Selamat Datang Kembali di Dashboard!</h1>
                    <p class="text-muted">Selamat datang di Dashboard Admin</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Card Rekap Data -->
                <div class="col-md-4">
                    <div class="admin-card">
                        <img src="assets/image/Tropy2.png" alt="Trophy">
                        <h3>Rekap data prestasi Mahasiswa dan Dosen</h3>
                        <p><?php echo $total_validated; ?> rekap prestasi terunggah</p>
                        <button type="button" class="btn btn-primary" id="showChartBtn">
                            <i class="fas fa-chart-bar"></i> Rekap Prestasi 
                        </button>
                    </div>
                </div>

                <!-- Card Input Lomba -->
                <div class="col-md-4">
                    <div class="admin-card">
                        <img src="assets/image/Input.png" alt="Form">
                        <h3>Masukan Informasi Lomba</h3>
                        <p>Masukan dan upload informasi lomba yang ingin anda tambahkan</p>
                        <a href="index.php?page=daftarlomba" class="admin-btn">Masukan Lomba</a>
                    </div>
                </div>

                <!-- Card Verifikasi -->
                <div class="col-md-4">
                    <div class="admin-card">
                        <img src="assets/image/Hp.png" alt="Verify">
                        <h3>Verifikasi Prestasi Lomba</h3>
                        <p>Menyortir hasil Prestasi Lomba Mahasiswa</p>
                        <a href="index.php?page=input_prestasi" class="admin-btn">Verifikasi Lomba</a>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="admin-stats" id="chartSection" style="display: none;">
                <h2>Data Statistik Prestasi Mahasiswa</h2>
                <div class="year">2024 - 2025</div>
                <div class="chart-container" style="position: relative; height:400px;">
                    <canvas id="achievementChart"></canvas>
                </div>
            </div>
        </div>
        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const showChartBtn = document.getElementById('showChartBtn');
        const chartSection = document.getElementById('chartSection');
        let myChart = null;

        showChartBtn.addEventListener('click', function() {
            // Toggle chart visibility
            if (chartSection.style.display === 'none') {
                chartSection.style.display = 'block';
                // Only load chart if it hasn't been loaded yet
                if (!myChart) {
                    loadChart();
                }
            } else {
                chartSection.style.display = 'none';
            }
        });

        function loadChart() {
            fetch('action/dashboardAction.php?act=get_achievement_stats')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('achievementChart').getContext('2d');
                    if (myChart) {
                        myChart.destroy();
                    }
                    myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Jumlah Prestasi',
                                data: data.values,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                tension: 0.1,
                                fill: true,
                                borderWidth: 2,
                                pointBackgroundColor: 'rgb(75, 192, 192)',
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Statistik Prestasi Mahasiswa per Bulan',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    padding: 20
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 12
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12
                                        }
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            hover: {
                                mode: 'nearest',
                                intersect: true
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading chart data');
                });
        }
    });
    </script>
    </section>

<?php
} else if ($role == 'mahasiswa') {
?>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="welcome-title">Selamat Datang Kembali di Dashboard!</h1>
                    <p class="text-muted">Selamat datang, <?php echo htmlspecialchars($username); ?></p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Card Terakhir Mengunggah -->
                <div class="col-lg-4">
                    <div class="dashboard-box bg-mint">
                        <div class="box-icon">
                            <img src="assets/image/Tropy.png" alt="Trophy">
                        </div>
                        <div class="box-content">
                            <h4>Terakhir mengunggah</h4>
                            <p><?= $total_prestasi ?> Prestasi berhasil diunggah</p>
                        </div>
                        <button class="btn-add">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Card Lomba -->
                <div class="col-lg-4">
                    <div class="dashboard-box bg-sky">
                        <div class="box-content">
                            <h4>Ingin Mengikuti Perlombaan?</h4>
                            <p>Jelajah dan temukan perlombaan yang kamu minati</p>
                            <a href="index.php?page=competitions" class="box-btn">Cari Lomba</a>
                        </div>
                        <div class="box-icon">
                            <img src="assets/image/Medal.png" alt="Medal">
                        </div>
                    </div>
                </div>
                <!-- Card Bimbingan -->
                <div class="col-lg-4">
                    <div class="dashboard-box bg-lavender">
                        <div class="box-content">
                            <h4>Butuh Bimbingan Dari Dosen?</h4>
                            <p>Berkonsultasi pada ahli untuk mendapatkan bimbingan dan dukungan</p>
                            <a href="index.php?page=pengajuan_dosen" class="box-btn">Temukan</a>
                        </div>
                        <div class="box-icon">
                            <img src="assets/image/Ide.png" alt="Guidance">
                        </div>
                    </div>
                </div>
            </div>
    </section>
<?php
} else if ($role == 'dosen') {
?>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="admin-card">
                        <img src="assets/image/Input.png" alt="Form">
                        <h3>Masukan Informasi Lomba</h3>
                        <p>Masukan dan upload informasi lomba yang ingin anda tambahkan</p>
                        <a href="index.php?page=daftarlomba" class="admin-btn">Masukan Lomba</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="admin-card">
                        <img src="assets/image/Tropy2.png" alt="Trophy">
                        <h3>Rekap data prestasi Mahasiswa dan Dosen</h3>
                        <p><?php echo $total_validated; ?> rekap prestasi terunggah</p>
                        <button type="button" class="btn btn-primary" id="showChartBtn">
                            <i class="fas fa-chart-bar"></i> Rekap Prestasi 
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="admin-card">
                        <img src="assets/image/Hp.png" alt="Verify">
                        <h3>Verifikasi Mahasiswa</h3>
                        <p>Verufikasi Mahasiswa Yang Mengajukan Anda Sebagai Dosen Pembimbing</p>
                        <a href="index.php?page=pengajuan_dosen" class="admin-btn">Verifikasi Mahasiswa</a>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="admin-stats" id="chartSection" style="display: none;">
                <h2>Data Statistik Prestasi Mahasiswa</h2>
                <div class="year">2024 - 2025</div>
                <div class="chart-container" style="position: relative; height:400px;">
                    <canvas id="achievementChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const showChartBtn = document.getElementById('showChartBtn');
        const chartSection = document.getElementById('chartSection');
        let myChart = null;

        showChartBtn.addEventListener('click', function() {
            // Toggle chart visibility
            if (chartSection.style.display === 'none') {
                chartSection.style.display = 'block';
                // Only load chart if it hasn't been loaded yet
                if (!myChart) {
                    loadChart();
                }
            } else {
                chartSection.style.display = 'none';
            }
        });

        function loadChart() {
            fetch('action/dashboardAction.php?act=get_achievement_stats')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('achievementChart').getContext('2d');
                    if (myChart) {
                        myChart.destroy();
                    }
                    myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Jumlah Prestasi',
                                data: data.values,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                tension: 0.1,
                                fill: true,
                                borderWidth: 2,
                                pointBackgroundColor: 'rgb(75, 192, 192)',
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Statistik Prestasi Mahasiswa per Bulan',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    padding: 20
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 12
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12
                                        }
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            hover: {
                                mode: 'nearest',
                                intersect: true
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading chart data');
                });
        }
    });
    </script>
<?php
}
?>