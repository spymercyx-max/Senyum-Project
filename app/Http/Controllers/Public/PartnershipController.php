<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Territory;
use App\Services\WhatsAppService;
use Illuminate\View\View;

class PartnershipController extends Controller
{
    public function index(WhatsAppService $wa): View
    {
        $waConsult = $wa->partnershipUrl(
            'Halo, saya ingin berkonsultasi mengenai kemitraan Kretek Senyum.'
        );

        $territories = Territory::count();

        $steps = [
            ['no' => '01', 'title' => 'Baca', 'desc' => 'Pahami halaman kemitraan ini: apa itu distributor, apa yang tersedia, dan bagaimana wilayah kerja disepakati.'],
            ['no' => '02', 'title' => 'Konsultasi', 'desc' => 'Hubungi admin via WhatsApp untuk bertanya soal wilayah, ketentuan harga distributor, dan kesiapan stok.'],
            ['no' => '03', 'title' => 'Daftar', 'desc' => 'Isi formulir pendaftaran distributor dengan data kota, kecamatan, dan kontak WhatsApp yang aktif.'],
            ['no' => '04', 'title' => 'Pending', 'desc' => 'Akun baru berstatus pending. Kamu bisa masuk tetapi workspace penuh belum terbuka.'],
            ['no' => '05', 'title' => 'Review', 'desc' => 'Tim kami meninjau data pendaftaran dan ketersediaan wilayah distribusimu.'],
            ['no' => '06', 'title' => 'Approved', 'desc' => 'Jika disetujui, status menjadi approved dan wilayah distribusi ditetapkan.'],
            ['no' => '07', 'title' => 'Workspace', 'desc' => 'Kelola outlet, transaksi, inventory, visit, dan monitoring dari workspace distributor.'],
        ];

        return view('public.partnership', [
            'waConsult' => $waConsult,
            'territories' => $territories,
            'steps' => $steps,
        ]);
    }
}
