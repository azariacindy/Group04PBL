<?php
require_once __DIR__ . '.../../../lib/Connection.php';

// $kategori = getTingkat();
?>

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
            <table class="table table-sm table-bordered table-striped" id="table-data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lomba</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Tingkat</th>
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
    <form action="action/daftarlombaAction.php?act=save" method="post" id="form-tambah" enctype="multipart/form-data">
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
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="tanggal">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" class="form-control" name="kategori" id="kategori">
                    </div>
                    <div class="form-group">
                        <label>tingkat</label>
                        <select id="id_tingkat" name="nama_tingkat" class="form-control">
                            <?php if (!empty($kategori)): ?>
                                <?php foreach ($kategori as $k): ?>
                                    <option value="<?= htmlspecialchars($k['id_tingkat']); ?>">
                                        <?= htmlspecialchars($k['nama_tingkat']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Tingkat Tidak Di Temukan</option>
                            <?php endif; ?>
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
        $('#form-tambah').attr('action', 'action/daftarlombaAction.php?act=save');
        $('#nama_lomba').val('');
        $('#tanggal').val('');
        $('#kategori').val('');
        $('#id_tingkat').val('');
        
    }

    function editData(id) {
        $.ajax({
            url: 'action/daftarlombaAction.php?act=get&id=' + id,
            method: 'post',
            success: function(response) {
                var data = JSON.parse(response);
                $('#form-data').modal('show');
                $('#form-tambah').attr('action', 'action/daftarlombaAction.php?act=update&id=' + id);
                $('#nama_lomba').val(data.nama_lomba);
                $('#tanggal').val(data.tanggal);
                $('#kategori').val(data.kategori);
                $('#id_tingkat').val(data.id_tingkat).trigger('change');
                
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
            ajax: 'action/LombaAction.php?act=load',
        });
        $('#form-tambah').validate({
            rules: {
                nama_lomba: {
                    required: true
                },
                tanggal: {
                    required: true
                },
                kategori: {
                    required: true
                },
                id_tingkat: {
                    required: true
                },
            },
            messages: {
                id_tingkat: {
                    required: "Tingkat harus dipilih."
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