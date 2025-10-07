<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan {{ $period['label'] ?? '' }}</title>
    <style>
        :root { font-size: 14px; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 32px;
            background: #f8fafc;
        }
        .wrapper {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 40px -20px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }
        header {
            padding: 32px 36px 28px;
            background: linear-gradient(135deg, #047857, #10b981);
            color: #fff;
        }
        header h1 {
            margin: 0 0 8px;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        header .meta {
            font-size: 13px;
            margin: 2px 0;
            opacity: 0.9;
        }
        header .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            margin-top: 12px;
        }
        section {
            padding: 30px 36px;
            border-top: 1px solid #f1f5f9;
        }
        h2 {
            margin: 0 0 18px;
            font-size: 18px;
            color: #0f172a;
            letter-spacing: 0.01em;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        .summary-card {
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .summary-card h3 {
            margin: 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
        }
        .summary-card p {
            margin: 12px 0 0;
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }
        .summary-card span {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
        }
        .table-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        thead {
            background: #f1f5f9;
        }
        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #475569;
            font-size: 11px;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        .empty-state {
            padding: 24px;
            text-align: center;
            color: #94a3b8;
            font-style: italic;
        }
        .flex-between {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 12px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 600;
        }
        .badge-hadir { background: #dcfce7; color: #166534; }
        .badge-izin { background: #e0f2fe; color: #1d4ed8; }
        .badge-sakit { background: #fee2e2; color: #b91c1c; }
        .badge-alpa { background: #fef3c7; color: #b45309; }
        .section-note {
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }
        .teacher-summary {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .teacher-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            background: #fafcff;
        }
        .teacher-card h4 {
            margin: 0 0 6px;
            font-size: 14px;
        }
        @page {
            margin: 24px 28px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <header>
        <h1>Laporan Bulanan {{ config('app.name') }}</h1>
        <div class="meta">Periode: {{ $period['label'] ?? '-' }}</div>
        <div class="meta">Rentang tanggal: {{ $period['range'][0] ?? '-' }} s/d {{ $period['range'][1] ?? '-' }}</div>
        @if(($filters['classroom_name'] ?? null))
            <div class="tag">Kelas: <strong>{{ $filters['classroom_name'] }}</strong></div>
        @else
            <div class="tag">Menampilkan semua kelas</div>
        @endif
        @if(($filters['student_keyword'] ?? null))
            <div class="tag">Siswa: <strong>{{ $filters['student_keyword'] }}</strong></div>
        @endif
        @if(($filters['specific_date'] ?? null))
            <div class="tag">Tanggal: <strong>{{ \Illuminate\Support\Carbon::parse($filters['specific_date'])->translatedFormat('d F Y') }}</strong></div>
        @endif
    </header>

    <section>
        <h2>Ringkasan Absensi Siswa</h2>
        <div class="flex-between" style="margin-bottom:18px; align-items:flex-end;">
            <div>
                <div class="meta" style="color:#475569; font-weight:600;">Total Catatan: {{ $attendance['summary']['total_records'] ?? 0 }}</div>
                <div class="meta" style="color:#475569;">Total Siswa: {{ $attendance['summary']['total_students'] ?? 0 }}</div>
            </div>
            <div>
                <span class="badge badge-hadir">Hadir {{ $attendance['summary']['hadir'] ?? 0 }}</span>
                <span class="badge badge-izin">Izin {{ $attendance['summary']['izin'] ?? 0 }}</span>
                <span class="badge badge-sakit">Sakit {{ $attendance['summary']['sakit'] ?? 0 }}</span>
                <span class="badge badge-alpa">Alpa {{ $attendance['summary']['alpa'] ?? 0 }}</span>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:28%">Nama Siswa</th>
                        <th style="width:18%">Kelas</th>
                        <th style="width:12%">Hadir</th>
                        <th style="width:12%">Izin</th>
                        <th style="width:12%">Sakit</th>
                        <th style="width:12%">Alpa</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($attendance['by_students'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['student']['name'] ?? '-' }}</td>
                        <td>{{ $item['student']['classroom'] ?? '-' }}</td>
                        <td>{{ $item['totals']['hadir'] ?? 0 }}</td>
                        <td>{{ $item['totals']['izin'] ?? 0 }}</td>
                        <td>{{ $item['totals']['sakit'] ?? 0 }}</td>
                        <td>{{ $item['totals']['alpa'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Belum ada data absensi pada periode ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="section-note">* Rekapitulasi didasarkan pada catatan absensi harian siswa pada periode terpilih.</p>
    </section>

    <section>
        <h2>Ringkasan Jurnal Mengajar</h2>
        <div class="flex-between" style="margin-bottom:18px; align-items:flex-end;">
            <div>
                <div class="meta" style="color:#475569; font-weight:600;">Total Pertemuan: {{ $journals['summary']['total_pertemuan'] ?? 0 }}</div>
                <div class="meta" style="color:#475569;">Total Durasi: {{ $journals['summary']['total_durasi_menit'] ?? 0 }} menit</div>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:14%">Tanggal</th>
                        <th style="width:20%">Guru</th>
                        <th style="width:18%">Mata Pelajaran</th>
                        <th style="width:14%">Durasi</th>
                        <th style="width:18%">Topik</th>
                        <th style="width:16%">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($journals['records'] ?? [] as $record)
                    <tr>
                        <td>{{ $record['tanggal'] ?? '-' }}</td>
                        <td>{{ $record['guru']['name'] ?? '-' }}</td>
                        <td>{{ $record['mata_pelajaran'] ?? '-' }}</td>
                        <td>{{ $record['durasi_menit'] ?? 0 }} menit</td>
                        <td>{{ $record['topik'] ?? '-' }}</td>
                        <td>{{ $record['catatan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Belum ada jurnal mengajar pada periode ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(!empty($journals['by_teachers']))
            <div class="teacher-summary">
                @foreach($journals['by_teachers'] as $summary)
                    <div class="teacher-card">
                        <h4>{{ $summary['guru']['name'] ?? 'Guru' }}</h4>
                        <div class="meta" style="color:#475569;">Pertemuan: {{ $summary['total_pertemuan'] ?? 0 }}</div>
                        <div class="meta" style="color:#475569;">Total Durasi: {{ $summary['total_durasi_menit'] ?? 0 }} menit</div>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="section-note">* Data jurnal menghimpun catatan kegiatan mengajar guru pada bulan yang dipilih.</p>
    </section>
</div>
</body>
</html>
