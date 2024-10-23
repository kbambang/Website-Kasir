<?php

namespace App\Http\Controllers;

use App\Models\produk;
use App\Models\kategori;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch counts for categories, products, suppliers, and purchases
        $kategori = kategori::count();
        $produk   = produk::count();
        $supplier = Supplier::count();
        $pembelian = Pembelian::count();

        // Set date range
        $tanggal_awal = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');

        // Initialize arrays for storing data
        $data_tanggal = array();
        $data_pendapatan = array();
        $totalRevenue = 0; // Initialize total revenue

        // Loop through each day in the range
        while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
            $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

            // Calculate total sales, purchases, and expenses for each day
            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pembelian = Pembelian::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('nominal');

            // Calculate revenue for the day
            $pendapatan = $total_penjualan - $total_pembelian - $total_pengeluaran;

            // Add daily revenue to the array
            $data_pendapatan[] = $pendapatan;

            // Add daily revenue to total revenue
            $totalRevenue += $pendapatan;

            // Move to the next day
            $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
        }

        // If the user is an admin, pass all data to the admin dashboard view
        if (auth()->user()->level == 1) {
            return view('admin.dashboard', compact('kategori', 'produk', 'supplier', 'pembelian', 'tanggal_awal', 'tanggal_akhir', 'data_tanggal', 'data_pendapatan', 'totalRevenue'));
        } else {
            return view('kasir.dashboard');
        }
    }

    
}
