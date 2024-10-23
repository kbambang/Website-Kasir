@extends('layouts.master')

@section('title')
    Transaksi Penjualan
@endsection

@push('css')
    <style>
        .tampil-bayar {
            font-size: 5em;
            text-align: center;
            height: 100px;
        }

        .tampil-terbilang {
            padding: 10px;
            background: #f0f0f0;
        }

        .table-penjualan tbody tr:last-child {
            display: none;
        }

        @media(max-width: 768px) {
            .tampil-bayar {
                font-size: 3em;
                height: 70px;
                padding-top: 5px;
            }
        }
    </style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Transaksi Penjualan</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-body">
                    <form class="form-produk">
                        @csrf
                        <div class="form-group row">
                            <label for="kode_produk" class="col-lg-2">Kode Produk</label>
                            <div class="col-lg-5">
                                <div class="input-group">
                                    <input type="hidden" name="id_penjualan" id="id_penjualan" value="{{ $id_penjualan }}">
                                    <!-- HIDDEN: Input ini berfungsi untuk menyimpan id_penjualan, yang merupakan ID unik untuk transaksi penjualan saat ini. -->
                                    <!-- {{ $id_penjualan }} akan diganti dengan nilai dari variabel $id_penjualan -->
                                    <input type="hidden" name="id_produk" id="id_produk">
                                    <input type="text" class="form-control" name="kode_produk" id="kode_produk">
                                    <span class="input-group-btn">
                                        <button onclick="tampilProduk()" class="btn btn-info btn-flat" type="button"><i
                                                class="fa fa-arrow-right"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table class="table table-striped table-bordered table-penjualan">
                        <thead>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th width="15%">Jumlah</th>
                            <th>Diskon</th>
                            <th>Subtotal</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="tampil-bayar bg-primary"></div>
                            <div class="tampil-terbilang"></div>
                        </div>
                        <div class="col-lg-4">
                            <form action="{{ route('transaksi.simpan') }}" class="form-penjualan" method="post">
                                @csrf
                                <input type="hidden" name="id_penjualan" value="{{ $id_penjualan }}">
                                <input type="hidden" name="total" id="total">
                                <input type="hidden" name="total_item" id="total_item">
                                <input type="hidden" name="bayar" id="bayar">


                                <div class="form-group row">
                                    <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="totalrp" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="diskon" class="col-lg-2 control-label">Total Diskon</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="diskonrp" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="bayarrp" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="diterima" class="col-lg-2 control-label">Diterima</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="diterima" class="form-control" name="diterima"
                                            value="{{ $penjualan->diterima ?? 0 }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="kembali" class="col-lg-2 control-label">Kembali</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="kembali" name="kembali" class="form-control"
                                            value="0" readonly>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i
                            class="fa fa-floppy-o"></i> Simpan Transaksi</button>
                </div>
            </div>
        </div>
    </div>

    @includeIf('penjualan_detail.produk')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let table, table2;
        let isStokValid = true; // Variabel untuk validasi stok

        $(function() {
            $('body').addClass('sidebar-collapse');

            table = $('.table-penjualan').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ajax: {
                        url: '{{ route('transaksi.data', $id_penjualan) }}',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            searchable: false,
                            sortable: false
                        },
                        {
                            data: 'kode_produk'
                        },
                        {
                            data: 'nama_produk'
                        },
                        {
                            data: 'harga_jual'
                        },
                        {
                            data: 'stok'
                        },
                        {
                            data: 'jumlah'
                        },
                        {
                            data: 'diskon'
                        },
                        {
                            data: 'subtotal'
                        },
                        {
                            data: 'aksi',
                            searchable: false,
                            sortable: false
                        },
                    ],
                    dom: 'Brt',
                    bSort: false,
                    paginate: false
                })
                .on('draw.dt', function() {
                    loadForm($('#diskon').val());
                    setTimeout(() => {
                        $('#diterima').trigger('input');
                    }, 300);
                });

            // Fungsi untuk memeriksa stok
            function checkStok() {
                isStokValid = true; // Reset nilai sebelum pengecekan stok

                // Cek stok untuk setiap produk dalam tabel
                $('.table-penjualan tbody tr').each(function() {
                    let stokTersedia = parseInt($(this).find('.stok').text()); // Stok yang tersedia
                    let jumlah = parseInt($(this).find('.quantity').val()) || 0; // Jumlah yang diinput

                    if (jumlah > stokTersedia || stokTersedia === 0) {
                        isStokValid = false; // Stok tidak cukup atau habis
                        return false; // Hentikan pengecekan jika ada stok yang habis
                    }
                });
            }


            $('#kode_produk').on('keypress', function(e) {
                if (e.which == 13) { // Kode 13 adalah kode untuk tombol Enter
                    e.preventDefault();
                    let kodeProduk = $(this).val().trim(); // Ambil nilai kode produk dari input

                    if (kodeProduk !== '') {
                        // Kirimkan request AJAX ke server untuk menambah produk berdasarkan kode
                        $.ajax({
                            url: '{{ route('transaksi.store') }}', // Route untuk menyimpan produk ke transaksi
                            type: 'POST',
                            data: {
                                kode_produk: kodeProduk,
                                id_penjualan: '{{ session('id_penjualan') }}', // Kirim ID Penjualan dari session
                                _token: '{{ csrf_token() }}' // Token CSRF
                            },
                            success: function(response) {
                                // Reset input setelah produk ditambahkan
                                $('#kode_produk').val('');
                                // Reload data tabel penjualan setelah produk ditambahkan
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Produk tidak ditemukan',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#kode_produk').val(''); // Reset input kode produk jika error
                            }
                        });
                    }
                }
            });


            $(document).on('input', '.quantity', function() {
                let id = $(this).data('id');
                let jumlah = parseInt($(this).val());
                let stok = parseInt($(this).parent().parent().find('.stok').text());

                if (jumlah < 1) {
                    $(this).val(1);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Jumlah Tidak Valid',
                        text: 'Jumlah tidak boleh kurang dari 1',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                if (jumlah > stok) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Tidak Valid',
                        text: 'Jumlah stok melebihi stok yang tersedia',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                $.post(`{{ url('/transaksi') }}/${id}`, {
                        '_token': $('[name=csrf-token]').attr('content'),
                        '_method': 'put',
                        'jumlah': jumlah
                    })
                    .done(response => {
                        table.ajax.reload(() => loadForm($('#diskon').val()));
                    })
                    .fail(errors => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Tidak dapat menyimpan data',
                            confirmButtonText: 'OK'
                        });
                        return;
                    });
            });



            $(document).on('input', '#diskon', function() {
                if ($(this).val() == "") {
                    $(this).val(0).select();
                }
                loadForm($(this).val());
            });

            $('#diterima').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value === "") {
                    $(this).val(0);
                    return;
                }
                $(this).val(new Intl.NumberFormat('id-ID').format(value));
                loadForm($('#diskon').val(), value);
            }).focus(function() {
                $(this).select();
            });

            $(document).on('click', '.btn-simpan', function(e) {
                e.preventDefault();

                checkStok(); // Cek stok sebelum menyimpan

                if (!isStokValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stok Tidak Cukup!',
                        text: 'Tidak dapat menyimpan transaksi karena stok habis atau tidak mencukupi.',
                        confirmButtonText: 'OK'
                    });
                    return; // Hentikan proses simpan jika stok tidak cukup
                }

                // Ambil nilai total dan uang diterima
                let totalBayar = parseFloat($('#bayar').val().replace(/[^0-9.-]+/g, ""));
                let uangDiterima = parseFloat($('#diterima').val().replace(/\./g, '').replace(',', '.'));

                // Validasi input produk
                if ($('#total_item').val() === '0') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Produk Belum Dipilih!',
                        text: 'Silakan tambahkan produk ke dalam transaksi sebelum menyimpan.',
                        confirmButtonText: 'OK'
                    });
                    return; // Hentikan proses simpan jika produk tidak dipilih
                }

                // Validasi input angka
                if (isNaN(uangDiterima) || isNaN(totalBayar)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Input Tidak Valid',
                        text: 'Silakan masukkan angka yang valid untuk bayar dan diterima.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (uangDiterima < totalBayar) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Uang diterima tidak mencukupi untuk membayar total transaksi!',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin menyimpan transaksi ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan!',
                    cancelButtonText: 'Tidak, batalkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('.form-penjualan').submit(); // Submit form jika konfirmasi "Ya"
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            showConfirmButton: false,
                        });
                    }
                });
            });


        });

        function tampilProduk() {
            $('#modal-produk').modal('show');
        }

        function hideProduk() {
            $('#modal-produk').modal('hide');
        }

        function pilihProduk(id, kode) {
            $('#id_produk').val(id);
            $('#kode_produk').val(kode);
            hideProduk();
            tambahProduk();
        }

        function tambahProduk() {
            $.post('{{ route('transaksi.store') }}', $('.form-produk').serialize())
                .done(response => {
                    $('#kode_produk').focus();
                    // table.ajax.reload(() => loadForm($('#diskon').val()));
                    table.ajax.reload(null, false)
                })
                .fail(errors => {
                    alert('Tidak dapat menyimpan data');
                    return;
                });
        }

        function deleteData(url) {
            Swal.fire({
                title: 'Yakin ingin menghapus data terpilih?',
                text: "Data yang dihapus tidak bisa dipulihkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(url, {
                            '_token': $('[name=csrf-token]').attr('content'),
                            '_method': 'delete'
                        })
                        .done((response) => {
                            table.ajax.reload(() => loadForm($('#diskon').val()));
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                        })
                        .fail((errors) => {
                            Swal.fire('Gagal!', 'Tidak dapat menghapus data.', 'error');
                        });
                }
            });
        }

        function loadForm(diskon = 0, diterima = 0) {

            $('#total').val($('.total').text());
            $('#total_item').val($('.total_item').text());

            $.get(`{{ url('/transaksi/loadform') }}/${diskon}/${$('.total').text()}/${diterima}`)
                .done(response => {
                    $('#totalrp').val('Rp. ' + response.totalrp);
                    $('#bayarrp').val('Rp. ' + response.bayarrp);
                    $('#bayar').val(response.bayar);
                    $('#diskonrp').val('Rp. ' + response.diskonrp);
                    $('.tampil-bayar').text('Bayar: Rp. ' + response.bayarrp);
                    $('.tampil-terbilang').text(response.terbilang);

                    $('#kembali').val('Rp.' + response.kembalirp);
                    if ($('#diterima').val() != 0) {
                        z
                        $('.tampil-bayar').text('Kembali: Rp. ' + response.kembalirp);
                        $('.tampil-terbilang').text(response.kembali_terbilang);
                    }
                })
                .fail(errors => {
                    alert('Tidak dapat menampilkan data');
                    return;
                });
        }
    </script>
@endpush
