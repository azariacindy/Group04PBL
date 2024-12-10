<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar Prestasi</h1>
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
            <h3 class="card-title">Daftar Prestasi</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-md btn-primary" onclick="tambahData()">
                    Tambah Prestasi
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered table-striped" id="table_data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>NIP</th>
                        <th>Nama Lomba</th>
                        <th>Tanggal</th>
                        <th>Detail Lomba</th>
                        <th>Berkas</th>
                        <th>Peringkat</th>
                        <th>Status</th>
                        <th>Aksi</th>
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
    <form action="admin/action/prestasiAction.php?act=save" method="post" id="tambahdata" enctype="multipart/form-data">
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
                        <label>Nama Lomba</label>
                        <input type="text" class="form-control" name="nama_lomba" id="nama_lomba">
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
                        <input type="text" class="form-control" name="berkas" id="berkas">
                    </div>
                    <div class="form-group">
                        <label>Peringkat</label>
                        <input type="text" class="form-control" name="peringkat" id="peringkat">
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
        $('#tambahdata').attr('action', 'action/prestasiAction.php?act=save');
        $('#id_prestasi').val('');
        $('#nim').val('');
        $('#nip').val('');
        $('#nama_lomba').val('');
        $('#tanggal').val('');
        $('#detail_lomba').val('');
        $('#berkas').val('');
        $('#peringkat').val('');
    }

    function editData(id) {
        $.ajax({
            url: 'action/prestasiAction.php?act=get&id=' + id,
            method: 'post',
            success: function(response) {
                var data = JSON.parse(response);
                $('#form-data').modal('show');
                $('#tambahdata').attr('action', 'action/prestasiAction.php?act=update&id=' + id);
                $('#nim').val(data.nim);
                $('#nip').val(data.nip);
                $('#nama_lomba').val(data.nama_lomba);
                $('#tanggal').val(data.tanggal);
                $('#detail_lomba').val(data.detail_lomba);
                $('#berkas').val(data.berkas); // Kosongkan input file
                $('#peringkat').val(data.peringkat);
            }
        });
    }

    function deleteData(id) {
        if (confirm('Apakah anda yakin?')) {
            $.ajax({
                url: 'action/prestasiAction.php?act=delete&id=' + id,
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
            ajax: 'action/prestasiAction.php?act=load',
        });
        $('#tambahdata').validate({
            rules: {
                nim: { required: true },
                nip: { required: true },
                nama_lomba: { required: true },
                tanggal: { required: true },
                detail_lomba: { required: true },
                berkas: { required: true },
                peringkat: { required: true }
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
