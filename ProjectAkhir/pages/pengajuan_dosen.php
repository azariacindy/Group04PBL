<?php
require_once('lib/Session.php');
$session = new Session();
if (!$session->get('is_login')) {
    header('Location: login.php');
    exit();
}
$role = $session->get('role');
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Pengajuan Dosen Pembimbing</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-header">
            <?php if ($role == 'mahasiswa'): ?>
            <div class="card-tools">
                <button type="button" class="btn btn-primary" onclick="tambahPengajuan()">
                    Ajukan Dosen Pembimbing
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table id="table_data" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <?php if ($role == 'dosen'): ?>
                            <th>Nama Mahasiswa</th>
                            <th>Nama Dosen</th>
                        <?php else: ?>
                            <th>Nama Dosen</th>
                            <th>Nama Mahasiswa</th>
                        <?php endif; ?>
                        <th>Tanggal Pengajuan</th>
                        <th>Status</th>
                        <?php if ($role == 'dosen'): ?>
                        <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Form Pengajuan -->
<div class="modal fade" id="modal-pengajuan">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-pengajuan">
                <div class="modal-header">
                    <h4 class="modal-title">Pengajuan Dosen Pembimbing</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Dosen Pembimbing</label>
                        <select class="form-control" name="nip" id="nip" required>
                            <option value="">Pilih Dosen</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Validasi -->
<div class="modal fade" id="modal-validasi">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-validasi">
                <div class="modal-header">
                    <h4 class="modal-title">Validasi Pengajuan</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_pengajuan" name="id_pengajuan">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="status" required>
                            <option value="">Pilih Status</option>
                            <option value="Disetujui">Setujui</option>
                            <option value="Ditolak">Tolak</option>
                        </select>
                    </div>
                    <div class="form-group" id="alasan-group" style="display: none;">
                        <label>Alasan Penolakan</label>
                        <textarea class="form-control" name="alasan" id="alasan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var tabledata;
    var isAdmin = <?php echo json_encode($role == 'admin'); ?>;
    var isDosen = <?php echo json_encode($role == 'dosen'); ?>;

    function loadDosen() {
        $.ajax({
            url: 'action/pengajuanDosenAction.php?act=get_dosen',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var options = '<option value="">Pilih Dosen</option>';
                    response.data.forEach(function(item) {
                        options += '<option value="' + item.nip + '">' + item.nama_dosen + '</option>';
                    });
                    $('#nip').html(options);
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    function tambahPengajuan() {
        $('#modal-pengajuan').modal('show');
        $('#form-pengajuan')[0].reset();
    }

    function validasi(id) {
        $('#modal-validasi').modal('show');
        $('#form-validasi')[0].reset();
        $('#id_pengajuan').val(id);
    }

    $(document).ready(function() {
        // Initialize DataTable
        tabledata = $('#table_data').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "action/pengajuanDosenAction.php?act=load",
                "type": "GET"
            },
            "columns": [
                { "data": "no" },
                <?php if ($role == 'dosen'): ?>
                    { "data": "nama_mhs" },
                    { "data": "nama_dosen" },
                <?php else: ?>
                    { "data": "nama_dosen" },
                    { "data": "nama_mhs" },
                <?php endif; ?>
                { "data": "tanggal" },
                { "data": "status" },
                <?php if ($role == 'dosen'): ?>
                { "data": "action" }
                <?php endif; ?>
            ],
            "order": [[3, "desc"]],
            "language": {
                "emptyTable": "Tidak ada data yang tersedia",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "search": "Cari:",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });

        // Load dosen data for dropdown
        loadDosen();

        // Handle pengajuan form submission
        $('#form-pengajuan').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'action/pengajuanDosenAction.php?act=save',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#modal-pengajuan').modal('hide');
                        tabledata.ajax.reload();
                        Swal.fire('Sukses', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                }
            });
        });

        // Handle validasi form submission
        $('#form-validasi').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'action/pengajuanDosenAction.php?act=validasi',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#modal-validasi').modal('hide');
                        tabledata.ajax.reload();
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Terjadi kesalahan saat memvalidasi pengajuan');
                }
            });
        });

        // Show/hide alasan field based on status selection
        $('#status').on('change', function() {
            if ($(this).val() == 'Ditolak') {
                $('#alasan-group').show();
                $('#alasan').prop('required', true);
            } else {
                $('#alasan-group').hide();
                $('#alasan').prop('required', false);
            }
        });
    });
</script>
