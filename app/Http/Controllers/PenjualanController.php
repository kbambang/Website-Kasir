<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use PDF;

class PenjualanController extends Controller
{
    public function index()
    {
        
        return view('penjualan.index');
    }

    public function data()
    {
        $penjualan = Penjualan::where('bayar', '>', 0)->orderBy('id_penjualan', 'desc')->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('no_transaksi', function ($penjualan) {
                return str_pad($penjualan->id_penjualan, 10 , '0', STR_PAD_LEFT); // Format dengan padding 00001
            })
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->editColumn('total_diskon', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->total_diskon);
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`'. route('penjualan.show', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    
                    <button onclick="deleteData(`'. route('penjualan.destroy', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    

    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->id_user = auth()->id();
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {

        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = str_replace('.','',$request->diterima);

        // Hitung total diskon
        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        $totalDiskon = 0;

        foreach ($detail as $item) {
            $diskonItem = ($item->diskon / 100) * $item->harga_jual * $item->jumlah;
            $totalDiskon += $diskonItem;
        }

        // Simpan total diskon ke dalam Penjualan
        $penjualan->total_diskon = $totalDiskon; // Pastikan field ini ada di tabel penjualans

        $penjualan->update();

       

        return redirect()->route('transaksi.selesai');
    }

    public function show($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">'. $detail->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. '. format_uang($detail->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. '. format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }


    public function edit($id)
{
    $penjualan = Penjualan::findOrFail($id);
    $penjualanDetails = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
    $produk = Produk::all(); 
    
    return view('penjualan.edit', compact('penjualan', 'produk', 'penjualanDetails'));
}


public function update(Request $request, $id)
{

    $detail = PenjualanDetail::find($id);
    $produk = Produk::find($detail->id_produk);

    $jumlahLama = $detail->jumlah;
    $jumlahBaru = $request->jumlah;
    $selisih = $request->selisih;

    // Update jumlah pada transaksi
    $detail->jumlah = $jumlahBaru;
    $detail->subtotal = $jumlahBaru * $detail->harga_jual;
    $detail->save();

    // Update stok produk
    if ($selisih > 0) {
        // Jika jumlah dikurangi, kembalikan stok
        $produk->stok += $selisih;
    } else if ($selisih < 0) {
        // Jika jumlah ditambah, kurangi stok
        $produk->stok += $selisih; // selisih negatif, artinya stok dikurangi
    }
    $produk->save();

    return response()->json('Data berhasil disimpan', 200);
}



    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }


    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();
        
        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    }

    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

            $pdf = FacadePdf::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
            $pdf->setPaper(0,0,609,440, 'potrait');
            return $pdf->stream('Transaksi-'. date('Y-m-d-his') .'.pdf');
    }

}