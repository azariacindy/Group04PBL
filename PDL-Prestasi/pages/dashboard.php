<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Lomba</h1>
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
                <button type="button" class="btn btn-md btn-primary" onclick="tambahData()">Tambah</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered table-striped" id="table-data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lomba</th>
                        <th>Tingkat</th>
                        <th>Tanggal</th>
                        <th>Detail</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Tambah/Edit Data -->
<div class="modal fade" id="form-data" style="display: none;" aria-hidden="true">
    <form action="action/lombaAction.php?act=save" method="post" id="form-tambah">
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
                        <select class="form-control" name="id_tingkat" id="id_tingkat">
                            <!-- Isi tingkat dari database -->
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
                        <input type="file" class="form-control" name="gambar" id="gambar">
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
    $('#form-tambah').attr('action', 'action/lombaAction.php?act=save');
    $('#nama_lomba').val('');
    $('#id_tingkat').val('');
    $('#tanggal').val('');
    $('#detail_lomba').val('');
    $('#gambar').val('');
}

function editData(id) {
    $.ajax({
        url: 'action/lombaAction.php?act=get&id=' + id,
        method: 'post',
        success: function(response) {
            var data = JSON.parse(response);
            $('#form-data').modal('show');
            $('#form-tambah').attr('action', 'action/lombaAction.php?act=update&id=' + id);
            $('#nama_lomba').val(data.nama_lomba);
            $('#id_tingkat').val(data.id_tingkat);
            $('#tanggal').val(data.tanggal);
            $('#detail_lomba').val(data.detail_lomba);
            $('#gambar').val(data.gambar);  // Gambar perlu penanganan khusus
        }
    });
}

function deleteData(id) {
    if (confirm('Apakah anda yakin?')) {
        $.ajax({
            url: 'action/lombaAction.php?act=delete&id=' + id,
            method: 'post',
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status) {
                    tabelData.ajax.reload();
                } else {
                    alert(result.message);
                }
            }
        });
    }
}

var tabelData;
$(document).ready(function() {
    tabelData = $('#table-data').DataTable({
        ajax: 'action/lombaAction.php?act=load',
    });

    $('#form-tambah').validate({
        rules: {
            nama_lomba: {
                required: true,
                minlength: 3
            },
            id_tingkat: {
                required: true
            },
            tanggal: {
                required: true
            },
            detail_lomba: {
                required: true
            }
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
                contentType: false,
                processData: false,
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.status) {
                        $('#form-data').modal('hide');
                        tabelData.ajax.reload(); // Reload data tabel
                    } else {
                        alert(result.message);
                    }
                }
            });
        }
    });
});
</script>
