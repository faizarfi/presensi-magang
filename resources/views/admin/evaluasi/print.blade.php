<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Penilaian Evaluasi Magang - {{ $intern->name }} - BPS Karanganyar</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm 2cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .kop-logo {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
        }

        .kop-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .kop-text h3 {
            margin: 2px 0 0 0;
            font-size: 15pt;
            font-weight: bold;
        }

        .kop-text p {
            margin: 4px 0 0 0;
            font-size: 9.5pt;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .title-block {
            text-align: center;
            margin-bottom: 20px;
        }

        .title-block h4 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .title-block p {
            margin: 3px 0 0 0;
            font-size: 10.5pt;
        }

        /* Tabel Biodata */
        .bio-table {
            width: 100%;
            margin-bottom: 18px;
            font-size: 11pt;
        }

        .bio-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .bio-label {
            width: 190px;
        }

        .bio-colon {
            width: 15px;
        }

        /* Tabel Nilai */
        .score-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 11pt;
        }

        .score-table th, .score-table td {
            border: 1px solid #000;
            padding: 7px 10px;
        }

        .score-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Notes Box */
        .notes-box {
            border: 1px solid #777;
            padding: 8px 12px;
            margin-bottom: 24px;
            font-size: 10.5pt;
            border-radius: 4px;
        }

        .notes-box strong {
            display: block;
            margin-bottom: 4px;
        }

        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }

        .signature-table td {
            vertical-align: top;
            width: 50%;
        }

        .no-print {
            background: #1e3a8a;
            color: white;
            padding: 12px 20px;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 13px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .btn-print {
            background: #fff;
            color: #1e3a8a;
            border: none;
            padding: 6px 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <span>Lembar Penilaian Evaluasi Magang BPS Karanganyar siap dicetak.</span>
        <button class="btn-print" onclick="window.print()">Cetak Dokumen (Print / PDF)</button>
    </div>

    <!-- Kop Surat BPS Resmi -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" style="width: 75px; height: auto;">
            </td>
            <td class="kop-text">
                <h2>BADAN PUSAT STATISTIK</h2>
                <h3>KABUPATEN KARANGANYAR</h3>
                <p>Komplek Perkantoran Cangakan, Jl. Majapahit No. 11 B, Karanganyar 57712<br>
                Telepon: (0271) 495047 | Email: bps3313@bps.go.id | Laman: karanganyarkab.bps.go.id</p>
            </td>
        </tr>
    </table>

    <div class="title-block">
        <h4>LEMBAR EVALUASI & PENILAIAN AKHIR MAGANG</h4>
        <p>Nomor: B-{{ str_pad($evaluation->id, 4, '0', STR_PAD_LEFT) }}/3313/MAGANG/{{ date('Y') }}</p>
    </div>

    <p style="margin-bottom: 10px; font-size: 11pt;">
        Pembimbing Lapangan Badan Pusat Statistik Kabupaten Karanganyar menerangkan bahwa peserta magang di bawah ini:
    </p>

    <table class="bio-table">
        <tr>
            <td class="bio-label">Nama Peserta Magang</td>
            <td class="bio-colon">:</td>
            <td class="font-bold">{{ $intern->name }}</td>
        </tr>
        <tr>
            <td class="bio-label">Nomor Induk (NIM / NISN)</td>
            <td class="bio-colon">:</td>
            <td>{{ $intern->internProfile->nim_nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bio-label">Asal Universitas / Sekolah</td>
            <td class="bio-colon">:</td>
            <td>{{ $intern->internProfile->institution ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bio-label">Program Studi / Jurusan</td>
            <td class="bio-colon">:</td>
            <td>{{ $intern->internProfile->major ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bio-label">Unit Kerja Penempatan</td>
            <td class="bio-colon">:</td>
            <td>{{ $intern->internProfile->division ?? 'BPS Kabupaten Karanganyar' }}</td>
        </tr>
        <tr>
            <td class="bio-label">Periode Praktik Kerja</td>
            <td class="bio-colon">:</td>
            <td>
                {{ $intern->internProfile->start_date?->translatedFormat('d F Y') }} s.d.
                {{ $intern->internProfile->end_date?->translatedFormat('d F Y') }}
                ({{ $intern->internProfile->duration_months ?? 1 }} Bulan)
            </td>
        </tr>
    </table>

    <p style="margin-bottom: 8px; font-size: 11pt;">
        Telah menyelesaikan seluruh rangkaian kegiatan praktik kerja/magang dengan capaian rekam evaluasi sebagai berikut:
    </p>

    <!-- Tabel Rincian Nilai -->
    <table class="score-table">
        <thead>
            <tr>
                <th style="width: 35px;">No.</th>
                <th>Komponen Aspek Penilaian</th>
                <th style="width: 70px;">Bobot</th>
                <th style="width: 90px;">Nilai (0-100)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1.</td>
                <td>
                    <strong>Kedisiplinan & Ketepatan Waktu</strong><br>
                    <span style="font-size: 9.5pt; color: #444;">(Ketaatan jam kerja kantor 07:30-16:00, presensi GPS, dan tata tertib kantor)</span>
                </td>
                <td class="text-center">20%</td>
                <td class="text-center font-bold">{{ number_format($evaluation->discipline_score, 1) }}</td>
            </tr>
            <tr>
                <td class="text-center">2.</td>
                <td>
                    <strong>Tanggung Jawab & Integritas Kerja</strong><br>
                    <span style="font-size: 9.5pt; color: #444;">(Penyelesaian tugas tepat waktu, menjaga kerahasiaan data statistik, dan kejujuran)</span>
                </td>
                <td class="text-center">20%</td>
                <td class="text-center font-bold">{{ number_format($evaluation->responsibility_score, 1) }}</td>
            </tr>
            <tr>
                <td class="text-center">3.</td>
                <td>
                    <strong>Inisiatif & Kreativitas</strong><br>
                    <span style="font-size: 9.5pt; color: #444;">(Keaktifan berdiskusi, pemecahan masalah operasional, dan kontribusi ide inovatif)</span>
                </td>
                <td class="text-center">20%</td>
                <td class="text-center font-bold">{{ number_format($evaluation->initiative_score, 1) }}</td>
            </tr>
            <tr>
                <td class="text-center">4.</td>
                <td>
                    <strong>Kerjasama Tim & Komunikasi</strong><br>
                    <span style="font-size: 9.5pt; color: #444;">(Koordinasi dengan staf BPS, etika komunikasi birokrasi, dan hubungan interpersonal)</span>
                </td>
                <td class="text-center">20%</td>
                <td class="text-center font-bold">{{ number_format($evaluation->teamwork_score, 1) }}</td>
            </tr>
            <tr>
                <td class="text-center">5.</td>
                <td>
                    <strong>Kualitas Capaian Tugas & Laporan Akhir</strong><br>
                    <span style="font-size: 9.5pt; color: #444;">(Akurasi pengolahan statistik, kerapian dokumentasi logbook, dan laporan magang)</span>
                </td>
                <td class="text-center">20%</td>
                <td class="text-center font-bold">{{ number_format($evaluation->work_quality_score, 1) }}</td>
            </tr>
            <tr style="background-color: #fafafa;">
                <td colspan="2" class="text-right font-bold" style="padding-right: 15px;">TOTAL NILAI AKHIR (RATA-RATA):</td>
                <td class="text-center font-bold">100%</td>
                <td class="text-center font-bold" style="font-size: 13pt; color: #002855;">
                    {{ number_format($evaluation->final_score, 1) }}
                </td>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <td colspan="2" class="text-right font-bold" style="padding-right: 15px;">PREDIKAT HASIL AKHIR:</td>
                <td colspan="2" class="text-center font-bold" style="font-size: 12pt;">
                    {{ $evaluation->predicate }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Skala Nilai Referensi -->
    <p style="font-size: 9pt; color: #555; margin-top: -10px; margin-bottom: 12px;">
        *Skala Predikat: A (85.0 - 100 : Sangat Baik) | B+ (75.0 - 84.9 : Memuaskan) | B (65.0 - 74.9 : Baik) | C (55.0 - 64.9 : Cukup) | D (&lt; 55.0 : Kurang)
    </p>

    <!-- Catatan Pembimbing -->
    <div class="notes-box">
        <strong>Catatan & Evaluasi Kualitatif Pembimbing Lapangan:</strong>
        <p style="margin: 0; font-style: italic;">
            "{{ $evaluation->notes ?: 'Peserta magang telah menyelesaikan program dengan baik, disiplin, dan menunjukkan dedikasi yang tinggi selama bertugas di Badan Pusat Statistik Kabupaten Karanganyar.' }}"
        </p>
    </div>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="text-align: center;">
                Mengetahui,<br>
                Peserta Magang,
                <br><br><br><br>
                <strong><u>{{ $intern->name }}</u></strong><br>
                <span>NIM/NISN. {{ $intern->internProfile->nim_nisn ?? '-' }}</span>
            </td>
            <td style="text-align: center;">
                Karanganyar, {{ $evaluation->evaluation_date->translatedFormat('d F Y') }}<br>
                Pembimbing Lapangan BPS Karanganyar,
                <br><br><br><br>
                <strong><u>{{ $evaluation->evaluator->name ?? 'Pembimbing Lapangan' }}</u></strong><br>
                <span>NIP. {{ $evaluation->evaluator->phone ? substr($evaluation->evaluator->phone, 0, 18) : '198501012010011001' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
