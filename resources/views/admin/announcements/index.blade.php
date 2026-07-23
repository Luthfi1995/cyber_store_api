@extends('admin.layouts.app')

@section('title', 'Pengumuman & Push Notification')
@section('page-title', 'Pengumuman & Push Notification')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Pengumuman</span>
@endsection

@section('content')
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-info .stat-value {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .stat-info .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-top: 2px;
        }

        .notice-banner {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-left: 4px solid #0B023E;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .notice-icon {
            width: 36px;
            height: 36px;
            background: #0B023E;
            color: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .table-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .badge-promo {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .badge-system {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .badge-info {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .table-row-custom {
            transition: background 0.15s ease;
        }

        .table-row-custom:hover {
            background: #f8fafc;
        }

        .btn-create-broadcast {
            background: linear-gradient(135deg, #0B023E 0%, #1E1B4B 100%);
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13.5px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(11, 2, 62, 0.2);
            transition: all 0.2s ease;
        }

        .btn-create-broadcast:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(11, 2, 62, 0.3);
        }

        .modal-backdrop-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 16px;
        }

        .modal-content-custom {
            background: #ffffff;
            width: 100%;
            max-width: 580px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header-custom {
            background: linear-gradient(135deg, #0B023E 0%, #1E1B4B 100%);
            padding: 20px 24px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-input-custom {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13.5px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            outline: none;
        }

        .form-input-custom:focus {
            border-color: #0B023E;
            box-shadow: 0 0 0 3px rgba(11, 2, 62, 0.1);
        }
    </style>

    <!-- Top Stats Summary -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">📢</div>
            <div class="stat-info">
                <div class="stat-value">{{ $announcements->total() }}</div>
                <div class="stat-label">Total Broadcast Sent</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fffbeb; color: #d97706;">🏷️</div>
            <div class="stat-info">
                <div class="stat-value">{{ $announcements->where('type', 'promo')->count() }}</div>
                <div class="stat-label">Promo & Diskon</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef2f2; color: #dc2626;">⚙️</div>
            <div class="stat-info">
                <div class="stat-value">{{ $announcements->where('type', 'system')->count() }}</div>
                <div class="stat-label">Notifikasi Sistem</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">👥</div>
            <div class="stat-info">
                <div class="stat-value">{{ $announcements->sum('user_notifications_count') }}</div>
                <div class="stat-label">Total Penerima Inbox</div>
            </div>
        </div>
    </div>

    <!-- Notice Banner -->
    <div class="notice-banner">
        <div class="notice-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>
        <div style="font-size: 13px; color: #334155; line-height: 1.5;">
            <strong>Info Broadcast:</strong> Setiap pengumuman yang dikirim akan otomatis muncul di inbox notifikasi aplikasi Flutter pengguna dan memicu Push Notification ke perangkat yang terhubung.
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="font-weight: 800; font-size: 16.5px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0B023E" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                Riwayat Broadcast Notifikasi
            </div>
            <button type="button" class="btn-create-broadcast" onclick="document.getElementById('modalCreateAnnouncement').style.display='flex'">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Buat Pengumuman Baru
            </button>
        </div>

        <div style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 12.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 14px 20px;">Judul Broadcast</th>
                        <th style="padding: 14px 20px;">Isi Pesan</th>
                        <th style="padding: 14px 20px;">Kategori</th>
                        <th style="padding: 14px 20px;">Jangkauan User</th>
                        <th style="padding: 14px 20px;">Waktu Kirim</th>
                        <th style="padding: 14px 20px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13.5px;">
                            <td style="padding: 16px 20px; font-weight: 700; color: #0f172a;">
                                {{ $announcement->title }}
                            </td>
                            <td style="padding: 16px 20px; max-width: 320px; color: #475569; line-height: 1.4;">
                                {{ Str::limit($announcement->content, 90) }}
                            </td>
                            <td style="padding: 16px 20px;">
                                @if($announcement->type === 'promo')
                                    <span class="badge-type badge-promo">🏷️ Promo</span>
                                @elseif($announcement->type === 'system')
                                    <span class="badge-type badge-system">⚙️ Sistem</span>
                                @else
                                    <span class="badge-type badge-info">📢 Informasi</span>
                                @endif
                            </td>
                            <td style="padding: 16px 20px;">
                                <span style="font-weight: 700; color: #0f172a; background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-size: 12px;">
                                    👥 {{ number_format($announcement->user_notifications_count) }} User
                                </span>
                            </td>
                            <td style="padding: 16px 20px; color: #64748b; font-size: 12.5px;">
                                📅 {{ $announcement->created_at->format('d M Y, H:i') }}
                            </td>
                            <td style="padding: 16px 20px; text-align: right;">
                                <form action="{{ route('admin.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.background='#dc2626'; this.style.color='#ffffff';" onmouseout="this.style.background='#fee2e2'; this.style.color='#dc2626';">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px 24px; color: #94a3b8;">
                                <div style="font-size: 40px; margin-bottom: 12px;">📢</div>
                                <div style="font-size: 16px; font-weight: 700; color: #475569; margin-bottom: 4px;">Belum Ada Pengumuman</div>
                                <div style="font-size: 13px;">Klik tombol "Buat Pengumuman Baru" di atas untuk mengirimkan broadcast pertama Anda.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div style="padding: 20px 24px; border-top: 1px solid #f1f5f9;">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>

    {{-- Glassmorphic Modal Create Announcement --}}
    <div id="modalCreateAnnouncement" class="modal-backdrop-custom">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <div style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">📢</span> Buat Pengumuman Baru
                </div>
                <button type="button" onclick="document.getElementById('modalCreateAnnouncement').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>
            <form action="{{ route('admin.announcements.store') }}" method="POST" style="padding: 24px;">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Judul Pengumuman</label>
                    <input type="text" name="title" required class="form-input-custom" placeholder="Contoh: Promo Flash Sale Ormik 30%">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Kategori Tipe</label>
                    <select name="type" required class="form-input-custom">
                        <option value="info">📢 Informasi Umum</option>
                        <option value="promo">🏷️ Promo & Diskon Special</option>
                        <option value="system">⚙️ Pemberitahuan Sistem</option>
                    </select>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Isi Pesan Pengumuman</label>
                    <textarea name="content" rows="4" required class="form-input-custom" placeholder="Tuliskan pesan lengkap yang akan tampil di aplikasi pengguna..."></textarea>
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Action Link URL (Opsional)</label>
                    <input type="url" name="action_url" class="form-input-custom" placeholder="https://cyberstore.co.id/promo">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('modalCreateAnnouncement').style.display='none'" style="padding: 10px 18px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" class="btn-create-broadcast">
                        🚀 Kirim Broadcast Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
