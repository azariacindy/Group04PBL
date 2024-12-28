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
    <form action="action/prestasiAction.php?act=save" method="post" id="formTambah" enctype="multipart/form-data">
        <input type="hidden" name="id_prestasi" id="id_prestasi">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Prestasi</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIM</label>
                        <?php if ($role == 'admin'): ?>
                        <select class="form-control" name="nim" id="nim" required>
                            <option value="">Pilih Mahasiswa</option>
                        </select>
                        <?php else: ?>
                        <input type="text" class="form-control" id="nim_text" readonly>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>NIP Dosen</label>
                        <select class="form-control" name="nip" id="nip" required>
                            <option value="">Pilih Dosen</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Lomba</label>
                        <select class="form-control" id="id_lomba" name="id_lomba">
                            <option value="">Pilih Lomba</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="customLombaCheck">
                            <label class="custom-control-label" for="customLombaCheck">Lomba tidak ada dalam daftar?</label>
                        </div>
                    </div>
                    <div id="customLombaForm" style="display: none;">
                        <div class="form-group">
                            <label>Nama Lomba Baru</label>
                            <input type="text" class="form-control" id="custom_nama_lomba" name="custom_nama_lomba" placeholder="Masukkan nama lomba">
                        </div>
                        <div class="form-group">
                            <label>Tingkat Lomba</label>
                            <select class="form-control" id="custom_tingkat" name="custom_tingkat">
                                <option value="">Pilih Tingkat</option>
                                <option value="1">Nasional</option>
                                <option value="2">Regional</option>
                                <option value="3">Internasional</option>
                            </select>
                        </div>
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
                        <select class="form-control" name="status_validasi" id="status_validasi" required onchange="toggleAlasan()">
                            <option value="">Pilih Status</option>
                            <option value="1">SKKM 1</option>
                            <option value="2">SKKM 2</option>
                            <option value="3">SKKM 3</option>
                            <option value="0">Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group" id="alasan_group" style="display: none;">
                        <label>Alasan Penolakan (Wajib diisi jika ditolak)</label>
                        <textarea class="form-control" name="alasan" id="alasan" rows="3"></textarea>
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

    // Function to load tingkat lomba based on id_lomba
    function loadTingkatLomba(id_lomba) {
        if (!id_lomba) {
            $('#tingkat_lomba').val('');
            return;
        }

        $.ajax({
            url: 'action/prestasiAction.php?act=get_tingkat_lomba',
            method: 'GET',
            data: { id_lomba: id_lomba },
            dataType: 'json',
            success: function(response) {
                if (response.status && response.data) {
                    $('#tingkat_lomba').val(response.data.nama_tingkat);
                } else {
                    $('#tingkat_lomba').val('');
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#tingkat_lomba').val('');
                console.error('AJAX Error:', status, error);
                if (xhr.responseText) {
                    console.error('Server Response:', xhr.responseText);
                }
            }
        });
    }

    // Function to check berkas and update status_lomba
    function checkBerkas() {
        var berkas = $('#berkas').val();
        if (!berkas) {
            $('#status_lomba').val('in progress');
        }
    }

    function validasiPrestasi(id) {
        Swal.fire({
            title: 'Validasi Prestasi',
            html: `
                <div class="form-group">
                    <label>Status Validasi (Poin SKKM)</label>
                    <select class="form-control" id="swal-status_validasi" onchange="toggleSwalAlasan()">
                        <option value="">Pilih Status</option>
                        <option value="1">SKKM 1</option>
                        <option value="2">SKKM 2</option>
                        <option value="3">SKKM 3</option>
                        <option value="0">Ditolak</option>
                    </select>
                </div>
                <div class="form-group" id="swal-alasan_group" style="display: none;">
                    <label>Alasan Penolakan</label>
                    <textarea class="form-control" id="swal-alasan" rows="3"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            didOpen: () => {
                window.toggleSwalAlasan = function() {
                    var statusValidasi = document.getElementById('swal-status_validasi').value;
                    var alasanGroup = document.getElementById('swal-alasan_group');
                    if (statusValidasi === '0') {
                        alasanGroup.style.display = 'block';
                    } else {
                        alasanGroup.style.display = 'none';
                    }
                }
            },
            preConfirm: () => {
                const status = document.getElementById('swal-status_validasi').value;
                const alasan = document.getElementById('swal-alasan').value;
                
                if (!status) {
                    Swal.showValidationMessage('Silakan pilih status validasi');
                    return false;
                }
                
                if (status === '0' && !alasan) {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi');
                    return false;
                }
                
                return { status: status, alasan: alasan };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'action/prestasiAction.php?act=validasi',
                    method: 'POST',
                    data: {
                        id_prestasi: id,
                        status_validasi: result.value.status,
                        alasan: result.value.alasan
                    },
                    success: function(response) {
                        let data;
                        try {
                            data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.status) {
                                Swal.fire({
                                    title: 'Sukses',
                                    text: 'Status validasi berhasil diperbarui',
                                    icon: 'success'
                                }).then(() => {
                                    tabledata.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.message || 'Terjadi kesalahan',
                                    icon: 'error'
                                });
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            Swal.fire({
                                title: 'Error',
                                text: 'Terjadi kesalahan saat memproses response server',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menghubungi server',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    function editData(id) {
        $.ajax({
            url: 'action/prestasiAction.php?act=get&id=' + id,
            method: 'get',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#form-data').modal('show');
                    $('.modal-title').text('Edit Prestasi');
                    $('#formTambah').attr('action', 'action/prestasiAction.php?act=update');
                    
                    // Set the id_prestasi in hidden field
                    $('#id_prestasi').val(id);
                    
                    // Set other form values
                    $('#nim').val(response.data.nim);
                    $('#nip').val(response.data.nip);
                    $('#id_lomba').val(response.data.id_lomba);
                    $('#tanggal').val(response.data.tanggal);
                    $('#detail_lomba').val(response.data.detail_lomba);
                    $('#peringkat').val(response.data.peringkat);
                    $('#status_lomba').val(response.data.status_lomba);
                    
                    // Handle file field
                    if (response.data.berkas) {
                        $('#berkas').prop('required', false);
                    } else {
                        $('#berkas').prop('required', true);
                    }
                    
                    // Load tingkat lomba
                    loadTingkatLomba(response.data.id_lomba);

                    // Hide custom lomba form when editing
                    $('#customLombaCheck').prop('checked', false);
                    $('#customLombaForm').hide();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                Swal.fire('Error', 'Terjadi kesalahan saat mengambil data', 'error');
            }
        });
    }

    function deleteData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: 'action/prestasiAction.php?act=delete&id=' + id,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if (response.status) {
                        tabledata.ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    }

    function showAlasan(alasan) {
        Swal.fire({
            title: 'Alasan Penolakan',
            text: alasan || 'Tidak ada alasan yang tercatat',
            icon: 'info'
        });
    }

    function toggleAlasan() {
        var statusValidasi = $('#status_validasi').val();
        var alasanGroup = $('#alasan_group');
        var alasanInput = $('#alasan');
        
        if (statusValidasi === '0') {
            alasanGroup.show();
            alasanInput.prop('required', true);
        } else {
            alasanGroup.hide();
            alasanInput.prop('required', false);
            alasanInput.val('');
        }
    }

    function tambahData() {
        $('#form-data').modal('show');
        $('.modal-title').text('Tambah Prestasi');
        $('#formTambah').attr('action', 'action/prestasiAction.php?act=save');
        
        // Reset form
        $('#formTambah')[0].reset();
        $('#status_lomba').val('in progress');
        $('#tingkat_lomba').val('');

        if (isAdmin) {
            $('#nim').prop('disabled', false).show();
            $('#nim_text').hide();
            $('#nip, #id_lomba, #tanggal, #detail_lomba, #berkas, #peringkat, #status_lomba').prop('disabled', false);
            $('#status_validasi').val(0);
            $('#status_validasi, #alasan').prop('disabled', false);
            $.ajax({
                url: 'action/prestasiAction.php?act=get_mahasiswa',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var select = $('#nim');
                    select.empty();
                    select.append('<option value="">Pilih Mahasiswa</option>');
                    data.forEach(function(item) {
                        select.append('<option value="' + item.nim + '">' + item.nama_mhs + ' (' + item.nim + ')</option>');
                    });
                    select.show();
                    $('#nim_text').hide();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Gagal mengambil data mahasiswa');
                }
            });
        } else {
            $('#nim').prop('disabled', true).hide();
            $('#nim_text').val($('#nim').val()).show();
            $('#nip, #id_lomba, #tanggal, #detail_lomba, #berkas, #peringkat, #status_lomba').prop('disabled', false);
            $('#status_validasi').val(0);
            $('#status_validasi, #alasan').prop('disabled', true);
        }
        
        loadDosen();
        loadLomba();
    }

    function loadMahasiswa() {
        var isAdmin = <?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'true' : 'false'; ?>;
        
        if (isAdmin) {
            $.ajax({
                url: 'action/prestasiAction.php?act=get_mahasiswa',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var select = $('#nim');
                    select.empty();
                    select.append('<option value="">Pilih Mahasiswa</option>');
                    
                    if (Array.isArray(data)) {
                        data.forEach(function(item) {
                            select.append('<option value="' + item.nim + '">' + 
                                item.nama_mhs + ' (' + item.nim + ')</option>');
                        });
                    } else {
                        console.error('Invalid response format:', data);
                    }
                    select.show();
                    $('#nim_text').hide();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Gagal mengambil data mahasiswa');
                }
            });
        } else {
            // If not admin, hide select and show text input with user's NIM
            $('#nim').hide();
            var userNim = '<?php echo isset($_SESSION['nim']) ? $_SESSION['nim'] : ''; ?>';
            $('#nim_text').val(userNim).show();
        }
    }

    function resetForm() {
        $('#formTambah')[0].reset();
        $('#nim').val('').trigger('change');
        $('#nip').val('').trigger('change');
        $('#id_lomba').val('').trigger('change');
        $('#nama_lomba').val('');
        $('#tanggal').val('');
        $('#juara').val('');
        $('#berkas').val('');
        $('#status_validasi').val('0');
        $('#alasan').val('');
        $('#alasanGroup').hide();
    }

    function loadDosen() {
        $.ajax({
            url: 'action/prestasiAction.php?act=get_dosen',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var select = $('#nip');
                select.empty();
                select.append('<option value="">Pilih Dosen</option>');
                
                if (Array.isArray(data)) {
                    data.forEach(function(item) {
                        select.append('<option value="' + item.nip + '">' + 
                            item.nama_dosen + '</option>');
                    });
                } else {
                    console.error('Invalid response format:', data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Gagal mengambil data dosen');
            }
        });
    }

    function loadLomba() {
        $.ajax({
            url: 'action/prestasiAction.php?act=get_lomba',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var select = $('#id_lomba');
                select.empty();
                select.append('<option value="">Pilih Lomba</option>');
                
                if (Array.isArray(data)) {
                    data.forEach(function(item) {
                        select.append('<option value="' + item.id_lomba + '">' + 
                            item.nama_lomba + ' (' + item.nama_tingkat + ')</option>');
                    });
                } else {
                    console.error('Invalid response format:', data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Gagal mengambil data lomba');
            }
        });
    }
    
    $(document).ready(function() {
        // DataTable initialization
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
                { 
                    "data": null,
                    "render": function(data, type, row) {
                        var buttons = '';
                        
                        if (!isAdmin && row.status_validasi == 0) {
                            buttons += '<button onclick="editData(' + row.id_prestasi + ')" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i> Edit</button>';
                        }
                        
                        if (isAdmin || row.status_validasi == 0) {
                            buttons += '<button onclick="deleteData(' + row.id_prestasi + ')" class="btn btn-danger btn-sm mr-1"><i class="fas fa-trash"></i> Hapus</button>';
                        }
                        
                        if (isAdmin && row.status_validasi != 1) {
                            buttons += '<button onclick="validasiPrestasi(' + row.id_prestasi + ')" class="btn btn-info btn-sm"><i class="fas fa-check"></i> Validasi</button>';
                        }
                        
                        return buttons;
                    }
                },
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
                        var badgeClass = '';
                        var statusText = '';
                        
                        switch(parseInt(data.status_validasi)) {
                            case 0:
                                badgeClass = 'danger';
                                statusText = 'Ditolak';
                                break;
                            case 1:
                                badgeClass = 'success';
                                statusText = 'SKKM Point 1';
                                break;
                            case 2:
                                badgeClass = 'success';
                                statusText = 'SKKM Point 2';
                                break;
                            case 3:
                                badgeClass = 'success';
                                statusText = 'SKKM Point 3';
                                break;
                            default:
                                badgeClass = 'warning';
                                statusText = 'Belum Divalidasi';
                        }
                        
                        status = '<span class="badge badge-' + badgeClass + '">' + statusText + '</span>';
                        
                        if (parseInt(data.status_validasi) === 0 && data.alasan) {
                            var escapedAlasan = data.alasan.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                            status += '<br><button class="btn btn-sm btn-info mt-1 show-alasan" data-alasan="' + escapedAlasan + '">Lihat Alasan</button>';
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

        // Event handler for showing alasan
        $(document).on('click', '.show-alasan', function() {
            var alasan = $(this).data('alasan');
            showAlasan(alasan);
        });

        // Load initial data
        loadDosen();
        loadLomba();
        loadMahasiswa();

        // Event handlers
        $('#id_lomba').change(function() {
            loadTingkatLomba($(this).val());
        });

        $('#status_validasi').change(function() {
            var value = $(this).val();
            if (value == '2') { // Tidak Valid
                $('#alasanGroup').show();
            } else {
                $('#alasanGroup').hide();
                $('#alasan').val('');
            }
        });

        // Modal events
        $('#form-data').on('show.bs.modal', function() {
            resetForm();
            loadDosen();
            loadLomba();
            loadMahasiswa();
        });

        $('#customLombaCheck').change(function() {
            if ($(this).is(':checked')) {
                $('#id_lomba').prop('disabled', true);
                $('#customLombaForm').show();
            } else {
                $('#id_lomba').prop('disabled', false);
                $('#customLombaForm').hide();
                $('#custom_nama_lomba').val('');
                $('#custom_tingkat').val('');
            }
        });

        $('#formTambah').submit(function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            // Add custom lomba data if checkbox is checked
            if ($('#customLombaCheck').is(':checked')) {
                formData.append('is_custom_lomba', '1');
                formData.append('custom_nama_lomba', $('#custom_nama_lomba').val());
                formData.append('custom_tingkat', $('#custom_tingkat').val());
            }

            // Add required fields
            formData.append('id_lomba', $('#id_lomba').val());
            formData.append('tanggal', $('#tanggal').val());
            formData.append('peringkat', $('#peringkat').val());
            formData.append('status_validasi', $('#status_validasi').val() || '0'); // Default to 0 if not set
            formData.append('status_lomba', $('#status_lomba').val());
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#form-data').modal('hide');
                        tabledata.ajax.reload();
                        Swal.fire('Sukses', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data', 'error');
                }
            });
        });
    });
</script>
