<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../action/competition_action.php';
require_once __DIR__ . '/../Model/CompetitionModel.php';

try {
    $competitions = getCompetitions();
    $tingkat_lomba = getCompetitionLevels();
} catch (Exception $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Daftar Perlombaan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Competitions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo CompetitionModel::safeEcho($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo CompetitionModel::safeEcho($_SESSION['success']);
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo CompetitionModel::safeEcho($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cari Perlombaan Yang Kalian Inginkan!</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Jadilah juara dan buat diri kalian bangga</p>

                <div class="competition-grid">
                    <?php if (isset($competitions) && !empty($competitions)): ?>
                        <?php foreach ($competitions as $competition): ?>
                        <div class="competition-card">
                            <img src="<?= CompetitionModel::getImageUrl($competition['gambar']) ?>" 
                                 class="competition-image"
                                 alt="<?= CompetitionModel::safeEcho($competition['nama_lomba']) ?>"
                                 onerror="this.src='<?= CompetitionModel::getDefaultImagePath() ?>'">
                            <div class="competition-content">
                                <h5 class="competition-title"><?= CompetitionModel::safeEcho($competition['nama_lomba']) ?></h5>
                                <div class="competition-date"><?= CompetitionModel::formatDate($competition['tanggal']) ?></div>
                                <div class="competition-level"><?= CompetitionModel::safeEcho($competition['nama_tingkat']) ?></div>
                                <div class="competition-detail"><?= CompetitionModel::safeEcho($competition['detail_lomba']) ?></div>
                                <button class="btn-detail" data-bs-toggle="modal" data-bs-target="#modal<?= $competition['id_lomba'] ?>">
                                    Detail Lomba
                                </button>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="modal<?= $competition['id_lomba'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= CompetitionModel::safeEcho($competition['nama_lomba']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="<?= CompetitionModel::getImageUrl($competition['gambar']) ?>" 
                                             class="img-fluid mb-3"
                                             alt="<?= CompetitionModel::safeEcho($competition['nama_lomba']) ?>"
                                             onerror="this.src='<?= CompetitionModel::getDefaultImagePath() ?>'">
                                        <p><strong>Tingkat Lomba:</strong><br><?= CompetitionModel::safeEcho($competition['nama_tingkat']) ?></p>
                                        <p><strong>Tanggal:</strong><br><?= CompetitionModel::formatDate($competition['tanggal']) ?></p>
                                        <p><strong>Detail Lomba:</strong><br><?= CompetitionModel::safeEcho($competition['detail_lomba']) ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Tidak ada perlombaan yang tersedia saat ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
