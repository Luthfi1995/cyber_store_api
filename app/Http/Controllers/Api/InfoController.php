<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class InfoController extends Controller
{
    public function about(): JsonResponse
    {
        return response()->json([
            'app_name' => 'Aplikasi Mahasiswa Baru Universitas Bina Sarana Informatika',
            'version' => 'v1.2.0',
            'description' => 'Aplikasi Resmi Mahasiswa Baru Universitas Bina Sarana Informatika. Untuk pembelian atribut dan keperluan Semot dan Ormik mahasiswa baru Universitas Bina Sarana Infromatika.',
            'terms_and_conditions' => [
                'title' => 'Syarat & Ketentuan',
                'content' => '1. Penggunaan aplikasi ini ditujukan khusus untuk mahasiswa baru Universitas Bina Sarana Informatika. 2. Transaksi pembelian produk tunduk pada kebijakan pengiriman dan pengembalian barang yang berlaku. 3. Pengguna bertanggung jawab penuh atas kerahasiaan akun dan kata sandi masing-masing.',
            ],
            'privacy_policy' => [
                'title' => 'Kebijakan Privasi',
                'content' => 'Kami menghargai privasi Anda. Informasi pribadi Anda seperti nama, email, dan nomor telepon digunakan hanya untuk kepentingan transaksi di aplikasi. Kami tidak membagikan data Anda kepada pihak ketiga tanpa persetujuan Anda.',
            ],
            'credits_and_contributors' => [
                'title' => 'Kredit & Kontributor',
                'content' => 'Dikembangkan oleh tim BTI Universitas Bina Sarana Informatika bekerjasama dengan Divisi Kemahasiswaan Universitas Bina Sarana Informatika.',
            ],
        ]);
    }

    public function help(): JsonResponse
    {
        return response()->json([
            'faqs' => [
                [
                    'question' => 'Bagaimana cara mendaftar akun?',
                    'answer' => 'Anda dapat mendaftar melalui Halaman Register dengan mengisi nama lengkap, email, nomor telepon, dan membuat kata sandi baru.',
                ],
                [
                    'question' => 'Apakah pembelian merchandise dapat dikirim ke luar kota?',
                    'answer' => 'Ya, kami bekerjasama dengan ekspedisi resmi (JNE, J&T, SiCepat, Anteraja) untuk pengiriman ke seluruh wilayah Indonesia.',
                ],
                [
                    'question' => 'Bagaimana cara melacak pesanan saya?',
                    'answer' => 'Anda dapat melihat status pelacakan pesanan Anda di tab "Riwayat Transaksi" dan memilih pesanan yang ingin dilacak.',
                ],
                [
                    'question' => 'Bagaimana jika barang yang saya terima cacat/tidak sesuai?',
                    'answer' => 'Silakan hubungi customer service kami melalui kontak WhatsApp/Email yang tersedia di menu bantuan dengan melampirkan video unboxing.',
                ],
            ],
            'contacts' => [
                'chat' => [
                    'label' => 'WhatsApp Chat',
                    'value' => 'https://wa.me/6282212345678',
                    'icon' => 'whatsapp',
                ],
                'email' => [
                    'label' => 'Email Support',
                    'value' => 'support@bsi.ac.id',
                    'icon' => 'email',
                ],
                'telephone' => [
                    'label' => 'Customer Call Center',
                    'value' => '+62-21-800-1234',
                    'icon' => 'phone',
                ],
            ],
            'app_guide' => [
                'title' => 'Panduan Aplikasi',
                'url' => 'https://ubsistore.test/guides/app-manual.pdf',
            ],
        ]);
    }

    public function storeInfo(): JsonResponse
    {
        $logo = \App\Models\Setting::get('store_logo');
        
        return response()->json([
            'store_name' => \App\Models\Setting::get('store_name', 'UBSI Store'),
            'store_address' => \App\Models\Setting::get('store_address', 'Jl. Kramat Raya No.98, Senen, Jakarta Pusat'),
            'store_email' => \App\Models\Setting::get('store_email', 'support@bsi.ac.id'),
            'store_phone' => \App\Models\Setting::get('store_phone', '(021) 7867868'),
            'store_logo' => $logo ? asset('storage/' . $logo) : asset('assets/img/logo.png'),
        ]);
    }
}

