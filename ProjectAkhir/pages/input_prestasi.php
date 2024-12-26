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
                <h1>Daftar Prestasi</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-header">
            <div class="card-tools">
                <button type="button" class="btn btn-primary" onclick="tambahData()">
                    Tambah Prestasi
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="table_data" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Mahasiswa</th>
                        <th>Nama Dosen</th>
                        <th>Nama Lomba</th>
                        <th>Tingkat</th>
                        <th>Tanggal</th>
                        <th>Detail Lomba</th>
                        <th>Berkas</th>
                        <th>Peringkat</th>
                        <th>Aksi</th>
                        <th>Status Lomba</th>
                        <th>Status Validasi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Form -->
<div class="modal fade" id="form-data">
    <form action="action/prestasiAction.php?act=save" method="post" id="tambahdata" enctype="multipart/form-data">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Prestasi</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIM</label>
                        <select class="form-control" name="nim" id="nim" required>
                            <option value="">Pilih Mahasiswa</option>
                        </select>
                        <input type="text" class="form-control" id="nim_text" readonly style="display: none;">
                    </div>
                    <div class="form-group">
                        <label>NIP Dosen</label>
                        <select class="form-control" name="nip" id="nip" required>
                            <option value="">Pilih Dosen</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Lomba</label>
                        <select class="form-control" name="id_lomba" id="id_lomba" required>
                            <option value="">Pilih Lomba</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tingkat Lomba</label>
                        <input type="text" class="form-control" id="tingkat_lomba" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                    </div>
                    <div class="form-group">
                        <label>Detail Lomba</label>
                        <textarea class="form-control" name="detail_lomba" id="detail_lomba" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Berkas</label>
                        <input type="file" class="form-control" name="berkas" id="berkas" onchange="checkBerkas()" required>
                    </div>
                    <div class="form-group">
                        <label>Peringkat</label>
                        <input type="text" class="form-control" name="peringkat" id="peringkat" placeholder="Contoh: Juara 1, Juara Harapan 1" required>
                    </div>
                    <div class="form-group">
                        <label>Status Lomba</label>
                        <select class="form-control" name="status_lomba" id="status_lomba" required>
                            <option value="in progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <?php if ($role == 'admin'): ?>
                    <div class="form-group">
                        <label>Status Validasi (Poin SKKM)</label>
                        <select class="form-control" name="status_validasi" id="status_validasi" required onchange="toggleAlasanValidasi()">
                            <option value="0">Belum Tervalidasi</option>
                            <option value="1">1 Poin SKKM</option>
                            <option value="2">2 Poin SKKM</option>
                            <option value="3">3 Poin SKKM</option>
                        </select>
                    </div>
                    <div class="form-group" id="alasan_validasi_group" style="display: none;">
                        <label>Alasan (Wajib diisi jika tidak divalidasi)</label>
                        <textarea class="form-control" name="alasan_validasi" id="alasan_validasi" rows="3"></textarea>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    var tabledata;
    var isAdmin = <?php echo json_encode($role == 'admin'); ?>;
    
    $(document).ready(function() {
        // Inisialisasi DataTable
        tabledata = $('#table_data').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "action/prestasiAction.php?act=load",
                "type": "POST",
                "dataSrc": function(json) {
                    if (json.error) {
                        alert(json.error);
                        return [];
                    }
                    return json.data;
                }
            },
            "columns": [
                { "data": "no" },
                { "data": "nama_mhs" },
                { "data": "nama_dosen" },
                { "data": "nama_lomba" },
                { "data": "nama_tingkat" },
                { "data": "tanggal" },
                { "data": "detail_lomba" },
                { 
                    "data": "berkas",
                    "render": function(data, type, row) {
                        if (data) {
                            return '<a href="uploads/' + data + '" target="_blank">' + data + '</a>';
                        }
                        return '';
                    }
                },
                { "data": "peringkat" },
                { "data": "action_buttons" },
                { 
                    "data": "status_lomba",
                    "render": function(data, type, row) {
                        if (data === 'completed') {
                            return '<span class="badge badge-success">Completed</span>';
                        } else if (data === 'in progress') {
                            return '<span class="badge badge-warning">In Progress</span>';
                        }
                        return data;
                    }
                },
                { 
                    "data": null,
                    "render": function(data, type, row) { 
                        var status = '';
                        switch(parseInt(data.status_validasi)) {
                            case 0:
                                status = '<span class="badge badge-warning">Belum Divalidasi</span>';
                                break;
                            case 1:
                                status = '<span class="badge badge-success">SKKM Point 1</span>';
                                break;
                            case 2:
                                status = '<span class="badge badge-success">SKKM Point 2</span>';
                                break;
                            case 3:
                                status = '<span class="badge badge-success">SKKM Point 3</span>';
                                break;
                            default:
                                status = '<span class="badge badge-secondary">Unknown</span>';
                        }
                        if (data.status_validasi == 0 && data.alasan_validasi) {
                            status += '<br><small class="text-danger">' + data.alasan_validasi + '</small>';
                        }
                        return status;
                    }
                }
            ],
            "order": [[0, 'asc']],
            "language": {
                "emptyTable": "Tidak ada data yang tersedia",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "loadingRecords": "Memuat...",
                "processing": "Memproses...",
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

        // Load daftar dosen dan lomba
        loadDosen();
        loadLomba();

        // Event listener untuk perubahan lomba
        $('#id_lomba').change(function() {
            var selectedOption = $(this).find('option:selected');
            $('#tingkat_lomba').val(selectedOption.data('tingkat') || '');
        });

        // Event listener untuk status validasi
        $('#status_validasi').change(function() {
            toggleAlasanValidasi();
        });

        // Form validation
        $('#tambahdata').validate({
            rules: {
                nip: { required: true },
                id_lomba: { required: true },
                tanggal: { required: true },
                detail_lomba: { required: true },
                berkas: { required: true },
                peringkat: { required: true },
                alasan_validasi: {
                    required: function() {
                        return $('#status_validasi').val() === '0';
                    }
                }
            },
            messages: {
                nip: "NIP dosen harus diisi",
                id_lomba: "Nama lomba harus diisi",
                tanggal: "Tanggal harus diisi",
                detail_lomba: "Detail lomba harus diisi",
                berkas: "Berkas harus diupload",
                peringkat: "Peringkat harus diisi",
                alasan_validasi: "Alasan harus diisi jika prestasi tidak divalidasi"
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                $.ajax({
                    url: $(form).attr('action'),
                    method: 'post',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.status) {
                            $('#form-data').modal('hide');
                            tabledata.ajax.reload();
                        }
                        alert(result.message);
                    }
                });
            }
        });
    });

    // Fungsi untuk memuat daftar dosen
    function loadDosen() {
        $.ajax({
            url: 'action/prestasiAction.php?act=get_dosen',
            method: 'get',
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.status) {
                        var html = '<option value="">Pilih Dosen</option>';
                        result.data.forEach(function(item) {
                            html += '<option value="' + item.nip + '">' + item.nama_dosen + '</option>';
                        });
                        $('#nip').html(html);
                    } else {
                        console.error('Error loading dosen:', result.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e, response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    // Fungsi untuk memuat daftar lomba
    function loadLomba() {
        $.ajax({
            url: 'action/prestasiAction.php?act=get_lomba',
            method: 'get',
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.status) {
                        var html = '<option value="">Pilih Lomba</option>';
                        result.data.forEach(function(item) {
                            html += '<option value="' + item.id_lomba + '">' + item.nama_lomba + '</option>';
                        });
                        $('#id_lomba').html(html);
                    } else {
                        console.error('Error loading lomba:', result.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e, response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    // Fungsi mengecek berkas dan mengatur status lomba
    function checkBerkas() {
        var berkas = $('#berkas').val();
        if (!berkas) {
            $('#status_lomba').val('in progress');
        }
    }

    // Fungsi edit data
    function editData(id) {
        $.ajax({
            url: 'action/prestasiAction.php?act=get&id=' + id,
            method: 'get',
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.status) {
                        var data = result.data;
                        $('#form-data').modal('show');
                        $('#tambahdata').attr('action', 'action/prestasiAction.php?act=update&id=' + id);
                        
                        if (isAdmin) {
                            // Admin hanya bisa edit status validasi dan alasan
                            $('.modal-title').text('Validasi Prestasi');
                            $('#nim, #nip, #id_lomba, #tanggal, #detail_lomba, #berkas, #peringkat, #status_lomba').prop('disabled', true);
                            $('#status_validasi').prop('disabled', false);
                            $('#alasan_validasi').prop('disabled', false);
                        } else {
                            // Mahasiswa bisa edit semua kecuali status validasi
                            $('.modal-title').text('Edit Prestasi');
                            $('#nim').prop('disabled', true);
                            $('#nip, #id_lomba, #tanggal, #detail_lomba, #berkas, #peringkat, #status_lomba').prop('disabled', false);
                            $('#status_validasi, #alasan_validasi').prop('disabled', true);
                        }

                        // Load daftar dosen dan lomba terlebih dahulu
                        loadDosen();
                        loadLomba();
                        
                        // Isi form dengan data
                        $('#nim').val(data.nim);
                        setTimeout(function() {
                            $('#nip').val(data.nip);
                            $('#id_lomba').val(data.id_lomba);
                            $('#tingkat_lomba').val(data.nama_tingkat);
                        }, 500);
                        $('#tanggal').val(data.tanggal);
                        $('#detail_lomba').val(data.detail_lomba);
                        $('#peringkat').val(data.peringkat);
                        $('#status_lomba').val(data.status_lomba);
                        if (isAdmin) {
                            $('#status_validasi').val(data.status_validasi || 0);
                            $('#alasan_validasi').val(data.alasan_validasi || '');
                            toggleAlasanValidasi();
                        }
                    } else {
                        alert(result.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e, response);
                    alert('Terjadi kesalahan saat memproses data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data');
            }
        });
    }

    // Fungsi delete data
    function deleteData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: 'action/prestasiAction.php?act=delete&id=' + id,
                method: 'POST',
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        alert(result.message);
                        if (result.status) {
                            tabledata.ajax.reload();
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e, response);
                        alert('Terjadi kesalahan saat memproses data');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    }

    // Fungsi toggle alasan validasi
    function toggleAlasanValidasi() {
        var status = $('#status_validasi').val();
        if (status == '0') {  // Belum divalidasi
            $('#alasan_validasi').prop('disabled', false).show();
            $('#alasan_validasi').attr('required', true);
        } else {
            $('#alasan_validasi').prop('disabled', true).hide();
            $('#alasan_validasi').attr('required', false);
        }
    }

    // Fungsi tambah data
    function tambahData() {
        $('#form-data').modal('show');
        $('.modal-title').text('Tambah Prestasi');
        $('#tambahdata').attr('action', 'action/prestasiAction.php?act=save');
        
        // Reset form
        $('#tambahdata')[0].reset();
        $('#status_lomba').val('in progress');
        $('#tingkat_lomba').val('');

        if (isAdmin) {
            // Admin bisa menambah prestasi baru
            $('#nim').prop('disabled', false).show();
            $('#nim_text').hide();
            $('#nip, #id_lomba, #tanggal, #detail_lomba, #berkas, #peringkat, #status_lomba').prop('disabled', false);
            $('#status_validasi').val(0);  // Default belum divalidasi
            $('#status_validasi, #alasan_validasi').prop('disabled', false);
            // Load NIM mahasiswa untuk admin
            $.ajax({
                url: 'action/prestasiAction.php?act=get_mahasiswa',
                method: 'get',
                success: function(response) {
                    var data = JSON.parse(response);
                    var select = $('#nim');
                    select.empty();
                    select.append('<option value="">Pilih Mahasiswa</option>');
                    data.forEach(function(item) {
                        select.append('<option value="' + item.nim + '">' + item.nama_mhs + ' (' + item.nim + ')</option>');
                    });
                }
            });
        } else {
            // Mahasiswa hanya bisa input prestasi sendiri
            $('#nim').prop('disabled', true).hide();
            $('#nim_text').val($('#nim').val()).show();
            $('#nip, #id_lomba, #tanggal, #detail_lomba, #berkas, #peringkat, #status_lomba').prop('disabled', false);
            $('#status_validasi').val(0);  // Default belum divalidasi
            $('#status_validasi, #alasan_validasi').prop('disabled', true);
        }
        
        // Load daftar dosen dan lomba
        loadDosen();
        loadLomba();
    }
</script>
