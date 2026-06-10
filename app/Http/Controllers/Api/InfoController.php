<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class InfoController extends Controller
{
    public function about(): JsonResponse
    {
        return response()->json([
            'app_name' => 'Alumni BSI University App',
            'version' => 'v1.2.0',
            'description' => 'Aplikasi Resmi Toko Merchandise dan Jejaring Alumni BSI University. Menghubungkan seluruh alumni dan menyediakan berbagai merchandise resmi berkualitas tinggi.',
            'terms_and_conditions' => [
                'title' => 'Syarat & Ketentuan',
                'content' => '1. Penggunaan aplikasi ini ditujukan khusus untuk alumni, civitas akademika, dan mitra resmi BSI University. 2. Transaksi pembelian merchandise tunduk pada kebijakan pengiriman dan pengembalian barang yang berlaku. 3. Pengguna bertanggung jawab penuh atas kerahasiaan akun dan kata sandi masing-masing.',
            ],
            'privacy_policy' => [
                'title' => 'Kebijakan Privasi',
                'content' => 'Kami menghargai privasi Anda. Informasi pribadi Anda seperti nama, email, nomor telepon, dan nomor alumni (NIM) digunakan hanya untuk kepentingan transaksi di aplikasi dan autentikasi keanggotaan alumni. Kami tidak membagikan data Anda kepada pihak ketiga tanpa persetujuan Anda.',
            ],
            'credits_and_contributors' => [
                'title' => 'Kredit & Kontributor',
                'content' => 'Dikembangkan oleh tim IT Alumni BSI University bekerjasama dengan Divisi Merchandise & Kemahasiswaan BSI.',
            ],
        ]);
    }

    public function help(): JsonResponse
    {
        return response()->json([
            'faqs' => [
                [
                    'question' => 'Bagaimana cara mendaftar akun?',
                    'answer' => 'Anda dapat mendaftar melalui Halaman Register dengan mengisi nama lengkap, email, nomor telepon, NIM/nomor alumni, dan membuat kata sandi baru.',
                ],
                [
                    'question' => 'Apakah pembelian merchandise dapat dikirim ke luar kota?',
                    'answer' => 'Ya, kami bekerjasama dengan ekspedisi resmi (JNE, J&T, SiCepat, Anteraja) untuk pengiriman ke seluruh wilayah Indonesia.',
                ],
                [
                    'question' => 'Bagaimana cara melacak pesanan saya?',
                    'answer' => 'Anda dapat melihat status pelacakan pesanan Anda di tab "Riwayat" pesanan dan memilih pesanan yang ingin dilacak.',
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
                    'value' => 'support@ubsistore.test',
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
}
