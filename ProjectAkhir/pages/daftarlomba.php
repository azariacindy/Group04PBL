<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar Lomba</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Lomba</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Lomba</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-md btn-primary" onclick="tambahData()">
                    Tambah Lomba
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered table-striped" id="table_data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lomba</th>
                        <th>Tingkat</th>
                        <th>Tanggal</th>
                        <th>Detail Lomba</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                        <th>Status</th>
                        <th>Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Form -->
<div class="modal fade" id="form-data" style="display: none;" aria-hidden="true">
    <form action="admin/action/daftarlombaAction.php?act=save" method="post" id="tambahdata" enctype="multipart/form-data">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Lomba</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lomba</label>
                        <input type="text" class="form-control" name="nama_lomba" id="nama_lomba">
                    </div>
                    <div class="form-group">
                        <label>Tingkat</label>
                        <select class="form-control" name="id_tingkat" id="id_tingkat" required>
                            <option value="" disabled selected>Pilih Tingkat Lomba</option>
                            <option value="1">Nasional</option>
                            <option value="2">Internasional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="tanggal">
                    </div>
                    <div class="form-group">
                        <label>Detail Lomba</label>
                        <textarea class="form-control" name="detail_lomba" id="detail_lomba"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Gambar</label>
                        <input type="url" class="form-control" name="gambar" id="gambar" placeholder="masukkan URL Gambar">
                    </div>
                    
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function tambahData() {
        $('#form-data').modal('show');
        $('#tambahdata').attr('action', 'action/daftarlombaAction.php?act=save');
        $('#nama_lomba').val('');
        $('#id_tingkat').val('');
        $('#tanggal').val('');
        $('#detail_lomba').val('');
        $('#gambar').val('');
    }

    function updateStatus(idLomba, status) {
    if (!confirm("Apakah Anda yakin ingin mengubah status?")) {
        return;
    }

    $.ajax({
        url: 'action/daftarlombaAction.php?act=update_status',
        type: 'POST',
        data: {
            act: 'update_status',
            id_lomba: idLomba,
            status_input_lomba: status
        },
        success: function(response) {
            const result = JSON.parse(response);
            if (result.status) {
                alert(result.message);
                location.reload(); // Muat ulang halaman setelah update
            } else {
                alert(result.message);
            }
        },
        error: function() {
            alert("Terjadi kesalahan saat mengubah status.");
        }
    });
}



    function editData(id) {
        $.ajax({
            url: 'action/daftarlombaAction.php?act=get&id=' + id,
            method: 'post',
            success: function(response) {
                var data = JSON.parse(response);
                $('#form-data').modal('show');
                $('#tambahdata').attr('action', 'action/daftarlombaAction.php?act=update&id=' + id);
                $('#nama_lomba').val(data.nama_lomba);
                $('#tingkat_id').val(data.tingkat_id).trigger('change');
                $('#tanggal').val(data.tanggal);
                $('#detail_lomba').val(data.detail_lomba);
                $('#gambar').val(data.gambar);
            }
        });
    }

    function deleteData(id) {
        if (confirm('Apakah anda yakin?')) {
            $.ajax({
                url: 'action/daftarlombaAction.php?act=delete&id=' + id,
                method: 'post',
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.status) {
                        tabledata.ajax.reload();
                    } else {
                        alert(result.message);
                    }
                }
            });
        }
    }

    var tabledata;
    $(document).ready(function() {
        tabledata = $('#table_data').DataTable({
            ajax: 'action/daftarlombaAction.php?act=load',
        });
        $('#tambahdata').validate({
            rules: {
                nama_lomba: {
                    required: true
                },
                id_tingkat: {
                    required: true
                },
                tanggal: {
                    required: true
                },
                detail_lomba: {
                    required: true
                },
                gambar: {
                    required: true
                },
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
                        } else {
                            alert(result.message);
                        }
                    }
                });
            }
        });
    });
</script>