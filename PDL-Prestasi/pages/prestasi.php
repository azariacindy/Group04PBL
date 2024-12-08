<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar Prestasi Mahasiswa</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Prestasi</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Prestasi Mahasiswa</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-md btn-primary" onclick="tambahData()">Tambah</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered table-striped" id="table-data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Prestasi</th>
                        <th>NIM</th>
                        <th>NIP</th>
                        <th>ID Lomba</th>
                        <th>Tanggal</th>
                        <th>Detail Lomba</th>
                        <th>Berkas</th>
                        <th>Peringkat</th>
                        <th>Status Lomba</th>
                        <th>Status Validasi</th>
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
    <form action="action/prestasiAction.php?act=save" method="post" id="form-tambah" enctype="multipart/form-data">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Prestasi</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" class="form-control" name="nim" id="nim">
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" class="form-control" name="nip" id="nip">
                    </div>
                    <div class="form-group">
                        <label>ID Lomba</label>
                        <input type="text" class="form-control" name="id_lomba" id="id_lomba">
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
                        <label>Berkas</label>
                        <input type="file" class="form-control" name="berkas" id="berkas">
                    </div>
                    <div class="form-group">
                        <label>Peringkat</label>
                        <input type="text" class="form-control" name="peringkat" id="peringkat">
                    </div>
                    <div class="form-group">
                        <label>Status Lomba</label>
                        <select class="form-control" name="status_lomba" id="status_lomba">
                            <option value="in progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status Validasi</label>
                        <select class="form-control" name="status_validasi" id="status_validasi">
                            <option value="skkm point 1">SKKM Point 1</option>
                            <option value="skkm point 2">SKKM Point 2</option>
                            <option value="skkm point 3">SKKM Point 3</option>
                        </select>
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
    $('#form-tambah').attr('action', 'action/prestasiAction.php?act=save');
    $('#nim').val('');
    $('#nip').val('');
    $('#id_lomba').val('');
    $('#tanggal').val('');
    $('#detail_lomba').val('');
    $('#berkas').val('');
    $('#peringkat').val('');
    $('#status_lomba').val('');
    $('#status_validasi').val('');
}

function editData(id) {
    $.ajax({
        url: 'action/prestasiAction.php?act=get&id=' + id,
        method: 'POST',
        success: function(response) {
            console.log("Raw response:", response);  // Log raw response

            try {
                var data = JSON.parse(response);  // Parse JSON
                $('#form-data').modal('show');
                $('#form-tambah').attr('action', 'action/prestasiAction.php?act=update&id=' + id);
                $('#nim').val(data.nim);
                $('#nip').val(data.nip);
                $('#id_lomba').val(data.id_lomba);
                $('#tanggal').val(data.tanggal);
                $('#detail_lomba').val(data.detail_lomba);
                $('#peringkat').val(data.peringkat);
                $('#status_lomba').val(data.status_lomba);
                $('#status_validasi').val(data.status_validasi);
            } catch (e) {
                console.error("Error parsing JSON:", e);
                alert("Error: The response is not valid JSON.");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            alert("An error occurred while fetching data.");
        }
    });
}

function deleteData(id) {
    if (confirm('Apakah anda yakin?')) {
        $.ajax({
            url: 'action/prestasiAction.php?act=delete&id=' + id,
            method: 'POST',
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
        ajax: 'action/prestasiAction.php?act=load',
    });

    $('#form-tambah').validate({
        rules: {
            nim: { required: true },
            nip: { required: true },
            id_lomba: { required: true },
            tanggal: { required: true },
            status_lomba: { required: true },
            status_validasi: { required: true }
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
                method: 'POST',
                data: new FormData(form),
                processData: false,
                contentType: false,
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.status) {
                        $('#form-data').modal('hide');
                        tabelData.ajax.reload();
                    } else {
                        alert(result.message);
                    }
                }
            });
        }
    });
});
</script>
