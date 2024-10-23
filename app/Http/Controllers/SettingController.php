<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        return view('setting.index');
    }

    public function show()
    {
        return Setting::first();
    }

    public function update(Request $request)
    {
        // Validasi data
        $request->validate([
            'nama_perusahaan' => 'required',
            'telepon' => 'required',
            'alamat' => 'required',
            'path_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        // Simpan data ke database
        $setting = Setting::first(); // Pastikan hanya ada satu record
        $setting->nama_perusahaan = $request->nama_perusahaan;
        $setting->telepon = $request->telepon;
        $setting->alamat = $request->alamat;
        
        if ($request->hasFile('path_logo')) {
            $file = $request->file('path_logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/logo'), $filename);
            $setting->path_logo = '/uploads/logo/' . $filename;
        }
    
        $setting->tipe_nota = $request->tipe_nota;
        $setting->save();
    
        return response()->json('Pengaturan berhasil disimpan', 200);
    }
}    