<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        return view('supplier.index');
    }

    public function data()
    {
        $supplier = Supplier::orderBy('id_supplier', 'desc')->get();

        return datatables()
            ->of($supplier)
            ->addIndexColumn()
            ->addColumn('aksi', function ($supplier) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('supplier.update', $supplier->id_supplier) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`'. route('supplier.destroy', $supplier->id_supplier) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
{
    // Validasi input tanpa aturan unik
    $request->validate([
        'nama' => 'required',
        'telepon' => 'required',
        'alamat' => 'required',
    ]);

    // Cek apakah supplier dengan nama atau telepon yang sama sudah ada
    $existingSupplier = Supplier::where('nama', $request->nama)
                                ->orWhere('telepon', $request->telepon)
                                ->first();

    if ($existingSupplier) {
        // Kembalikan respons error jika ada data yang sama
        return response()->json([
            'message' => 'Supplier dengan nama atau telepon yang sama sudah ada.'
        ], 422);
    }

    // Simpan data baru jika tidak ada data duplikat
    $supplier = Supplier::create($request->all());

    return response()->json([
        'message' => 'Data berhasil disimpan'
    ], 200);
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);

        return response()->json($supplier);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id)->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

             // Cek apakah supplier sudah digunakan di pembelian
        if ($supplier->pembelian()->exists()) {
            return response()->json(['message' => 'Supplier tidak dapat dihapus karena sudah digunakan di pembelian'], 400);
        }

            $supplier->delete();
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Tidak dapat menghapus data'], 500);
        }
    }
}