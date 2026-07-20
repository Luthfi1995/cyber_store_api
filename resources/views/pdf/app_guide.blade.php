<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Penggunaan Aplikasi — {{ $storeName }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 10.5pt;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #0d47a1;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 18pt;
            font-weight: bold;
            color: #0d47a1;
            margin: 0;
        }
        .subtitle {
            font-size: 10pt;
            color: #64748b;
            margin-top: 4px;
        }
        .chapter-card {
            margin-bottom: 22px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .chapter-header {
            background-color: #0d47a1;
            color: #ffffff;
            padding: 8px 14px;
            font-weight: bold;
            font-size: 12pt;
        }
        .chapter-banner-box {
            width: 100%;
            height: 90px;
            overflow: hidden;
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            text-align: center;
        }
        .chapter-banner-img {
            width: 100%;
            height: 90px;
            object-fit: cover;
        }
        .chapter-desc {
            background-color: #f8fafc;
            padding: 8px 14px;
            font-size: 9.5pt;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            font-style: italic;
        }
        .steps-container {
            padding: 12px 14px;
        }
        .step-item {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .step-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .step-number {
            display: inline-block;
            background-color: #0d47a1;
            color: #ffffff;
            font-size: 9pt;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .step-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 10.5pt;
        }
        .step-desc {
            margin-top: 3px;
            font-size: 9.5pt;
            color: #334155;
            padding-left: 24px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    @php
        $bannerImages = [
            'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1556742049-0a67daf4005a?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534536281715-e28d76689b4d?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=600&auto=format&fit=crop&q=80',
        ];
    @endphp

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">Panduan Penggunaan Aplikasi</div>
                    <div class="subtitle">Buku Petunjuk Resmi Layanan {{ $storeName }}</div>
                </td>
                <td style="text-align: right; font-size: 8.5pt; color: #64748b;">
                    Dicetak: {{ $generatedAt }}
                </td>
            </tr>
        </table>
    </div>

    @foreach($helpGuides as $index => $guide)
        <div class="chapter-card">
            <div class="chapter-header">
                Bab {{ $index + 1 }}: {{ $guide['title'] ?? 'Panduan' }}
            </div>
            <div class="chapter-banner-box">
                @php
                    $chapterImg = !empty($guide['image']) ? $guide['image'] : $bannerImages[$index % count($bannerImages)];
                @endphp
                <img src="{{ $chapterImg }}" class="chapter-banner-img" alt="Banner Ilustrasi">
            </div>
            @if(!empty($guide['description']))
                <div class="chapter-desc">
                    {{ $guide['description'] }}
                </div>
            @endif
            <div class="steps-container">
                @foreach(($guide['steps'] ?? []) as $sIdx => $step)
                    <div class="step-item">
                        <div>
                            <span class="step-number">{{ $sIdx + 1 }}</span>
                            <span class="step-title">{{ $step['title'] ?? '' }}</span>
                        </div>
                        @if(!empty($step['desc']))
                            <div class="step-desc">
                                {{ $step['desc'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="footer">
        Hak Cipta &copy; {{ date('Y') }} {{ $storeName }}. Dokumen resmi panduan pengguna aplikasi mobile.
    </div>
</body>
</html>
