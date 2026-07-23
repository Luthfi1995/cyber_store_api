@extends('admin.layouts.app')

@section('title', 'Pengumuman & Push Notification')
@section('page-title', 'Pengumuman & Push Notification')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Pengumuman</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px;">
            <span class="card-title" style="font-weight:bold; font-size:16px; display:flex; align-items:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Daftar Pengumuman & Broadcast Notifikasi
            </span>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('modalCreateAnnouncement').style.display='flex'">
                ＋ Buat Pengumuman Baru
            </button>
        </div>

        <div style="padding:12px 20px; border-bottom:1px solid var(--border, #eee); background:#f8fafc; font-size:13px; color:#475569;">
            💡 Setiap pengumuman yang dibuat akan otomatis masuk ke inbox notifikasi semua aplikasi pengguna (Flutter) dan mengirim Push Notification ke HP pengguna jika Token FCM terkonfigurasi.
        </div>

        <div class="card-body" style="padding:0;">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f1f5f9; text-align:left; font-size:13px;">
                        <th style="padding:12px 16px;">Judul</th>
                        <th style="padding:12px 16px;">Isi Pesan</th>
                        <th style="padding:12px 16px;">Tipe</th>
                        <th style="padding:12px 16px;">Penerima</th>
                        <th style="padding:12px 16px;">Tanggal Kirim</th>
                        <th style="padding:12px 16px; text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr style="border-bottom:1px solid #f1f5f9; font-size:13px;">
                            <td style="padding:12px 16px; font-weight:bold;">{{ $announcement->title }}</td>
                            <td style="padding:12px 16px; max-width:300px; color:#475569;">{{ Str::limit($announcement->content, 100) }}</td>
                            <td style="padding:12px 16px;">
                                @if($announcement->type === 'promo')
                                    <span style="background:#fef3c7; color:#b45309; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:bold;">Promo</span>
                                @elseif($announcement->type === 'system')
                                    <span style="background:#fee2e2; color:#b91c1c; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:bold;">Sistem</span>
                                @else
                                    <span style="background:#e0f2fe; color:#0369a1; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:bold;">Informasi</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px; font-weight:600;">{{ $announcement->user_notifications_count }} Pengguna</td>
                            <td style="padding:12px 16px; color:#64748b;">{{ $announcement->created_at->format('d M Y, H:i') }}</td>
                            <td style="padding:12px 16px; text-align:right;">
                                <form action="{{ route('admin.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:#ef4444; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer;">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:32px; color:#94a3b8;">
                                Belum ada pengumuman yang dikirim. Klik "Buat Pengumuman Baru" untuk mengirim broadcast.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div style="padding:16px;">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Create Announcement --}}
    <div id="modalCreateAnnouncement" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:white; width:100%; max-width:550px; border-radius:12px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #eee; padding-bottom:12px;">
                <h3 style="margin:0; font-size:18px;">📢 Buat Pengumuman Baru</h3>
                <button type="button" onclick="document.getElementById('modalCreateAnnouncement').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:4px;">Judul Pengumuman</label>
                    <input type="text" name="title" required class="form-control" placeholder="Contoh: Diskon Ormik Mahasiswa Baru 20%" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:4px;">Tipe</label>
                    <select name="type" required class="form-control" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                        <option value="info">Informasi Umum</option>
                        <option value="promo">Promo & Diskon</option>
                        <option value="system">Pemberitahuan Sistem</option>
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:4px;">Isi Pesan Pengumuman</label>
                    <textarea name="content" rows="4" required class="form-control" placeholder="Tuliskan isi pengumuman lengkap..." style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;"></textarea>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:4px;">Link / Action URL (Opsional)</label>
                    <input type="url" name="action_url" class="form-control" placeholder="https://..." style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" onclick="document.getElementById('modalCreateAnnouncement').style.display='none'" class="btn" style="padding:8px 16px; background:#e2e8f0; border:none; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding:8px 16px; background:#0B023E; color:white; border:none; border-radius:6px; cursor:pointer;">Kirim Pengumuman</button>
                </div>
            </form>
        </div>
    </div>
@endsection
