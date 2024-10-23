@extends('layouts.master')

@section('title')
   Edit Profil
@endsection

@section('breadcumb')
    @parent
    <li class="active">Edit Profil</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <form action="{{ route('user.update_profil') }}" method="post" class="form-profil" data-toggle="validator" enctype="multipart/form-data">
                @csrf
                <div class="box-body">
                    <div class="alert alert-info alert-dismissible" style="display: none;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="icon fa fa-check"></i> Perubahan berhasil disimpan
                    </div> 
                    <div class="form-group row">
                        <label for="name" class="col-lg-2 col-lg-offset-2 control-label">Nama</label>
                        <div class="col-lg-6">
                            <input type="text" name="name" class="form-control" id="name" required autofocus value="{{ $profil->name }}">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="foto" class="col-lg-2 col-lg-offset-2 control-label">Profil</label>
                        <div class="col-lg-6">
                            <input type="file" name="foto" class="form-control" id="foto"
                                onchange="preview('.tampil-foto', this.files[0])">
                            <span class="help-block with-errors"></span>
                            <br>
                            <div class="tampil-foto">
                                <img src="{{ url($profil->foto ?? '/') }}" width="200">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="old_password" class="col-lg-2 col-lg-offset-2 control-label">Password Lama</label>
                        <div class="col-lg-6">
                            <input type="password" name="old_password" id="old_password" class="form-control" minlength="6">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password" class="col-lg-2 col-lg-offset-2 control-label">Password</label>
                        <div class="col-lg-6">
                            <input type="password" name="password" id="password" class="form-control" minlength="6">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password_confirmation" class="col-lg-2 col-lg-offset-2 control-label">Konfirmasi Password</label>
                        <div class="col-lg-6">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" data-match="#password">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button class="btn btn-sm btn-flat btn-success"><i class="fa fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            $('#old_password').on('keyup', function () {
                if ($(this).val() != "") $('#password, #password_confirmation').attr('required', true);
                else $('#password, #password_confirmation').attr('required', false);
            });

            // Event submit form dengan SweetAlert2 untuk alert sukses dan gagal
            $('.form-profil').validator().on('submit', function (e) {
                if (! e.preventDefault()) {
                    $.ajax({
                        url: $('.form-profil').attr('action'),
                        type: $('.form-profil').attr('method'),
                        data: new FormData($('.form-profil')[0]),
                        async: false,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            // Update data tampilan setelah berhasil
                            $('[name=name]').val(response.name);
                            $('.tampil-foto').html(`<img src="{{ url('/') }}${response.foto}" width="200">`);
                            $('.img-profil').attr('src', `{{ url('/') }}/${response.foto}`);

                            // Alert berhasil menggunakan SweetAlert2
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Profil berhasil diperbarui.',
                                confirmButtonText: 'Oke'
                            });
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == 422) {
                                // Tampilkan error validasi
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validasi Gagal',
                                    text: 'Cek kembali input yang Anda masukkan.',
                                    confirmButtonText: 'Oke'
                                });
                            } else {
                                // Alert error lainnya
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Tidak dapat menyimpan data!',
                                    confirmButtonText: 'Oke'
                                });
                            }
                        }
                    });
                }
            });
        });
        
    </script>
@endpush
