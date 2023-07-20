<?php
ob_start();
require_once('../fpdf186/fpdf.php');
require_once('../koneksi.php');

class PDF extends FPDF
{
    function Header()
    {
        // Implementasikan kode header di sini (jika diperlukan)
    }

    function Footer()
    {
        // Implementasikan kode footer di sini (jika diperlukan)
    }
}

$pdf = new PDF();
$pdf->AddPage('P', 'A4');
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(0, 10, 'LAPORAN Sistem Informasi Keuangan', 0, 1, 'C');
$pdf->Ln();

// Tambahkan tabel ke PDF
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(10, 10, 'NO', 1, 0, 'C');
$pdf->Cell(25, 10, 'TANGGAL', 1, 0, 'C');
$pdf->Cell(45, 10, 'KATEGORI', 1, 0, 'C');
$pdf->Cell(60, 10, 'KETERANGAN', 1, 0, 'C');
$pdf->Cell(30, 10, 'PEMASUKAN', 1, 0, 'C'); // Perubahan lebar kolom
$pdf->Cell(30, 10, 'PENGELUARAN', 1, 1, 'C'); // Perubahan lebar kolom

$no = 1;
$total_pemasukan = 0;
$total_pengeluaran = 0;
if (isset($_GET['tanggal_sampai']) && isset($_GET['tanggal_dari']) && isset($_GET['kategori'])) {
    $tgl_dari = $_GET['tanggal_dari'];
    $tgl_sampai = $_GET['tanggal_sampai'];
    $kategori = $_GET['kategori'];

    if ($kategori == "semua") {
        $data = mysqli_query($koneksi, "SELECT * FROM transaksi,kategori where kategori_id=transaksi_kategori and date(transaksi_tanggal)>='$tgl_dari' and date(transaksi_tanggal)<='$tgl_sampai'");
    } else {
        $data = mysqli_query($koneksi, "SELECT * FROM transaksi,kategori where kategori_id=transaksi_kategori and kategori_id='$kategori' and date(transaksi_tanggal)>='$tgl_dari' and date(transaksi_tanggal)<='$tgl_sampai'");
    }
    
    while ($d = mysqli_fetch_array($data)) {
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(10, 10, $no++, 1, 0, 'C');
        $pdf->Cell(25, 10, date('d-m-Y', strtotime($d['transaksi_tanggal'])), 1, 0, 'C');
        $pdf->Cell(45, 10, $d['kategori'], 1, 0, 'L');
        $pdf->Cell(60, 10, $d['transaksi_keterangan'], 1, 0, 'L');
        if ($d['transaksi_jenis'] == "Pemasukan") {
            $pdf->Cell(30, 10, "Rp. " . number_format($d['transaksi_nominal']) . " ,-", 1, 0, 'R');
            $pdf->Cell(30, 10, "", 1, 1, 'C');
            $total_pemasukan += $d['transaksi_nominal'];
        } elseif ($d['transaksi_jenis'] == "Pengeluaran") {
            $pdf->Cell(30, 10, "", 1, 0, 'C');
            $pdf->Cell(30, 10, "Rp. " . number_format($d['transaksi_nominal']) . " ,-", 1, 1, 'R');
            $total_pengeluaran += $d['transaksi_nominal'];
        }
    }

    // Total dan Saldo
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(140, 10, 'TOTAL', 1, 0, 'C');
    $pdf->Cell(30, 10, "Rp. " . number_format($total_pemasukan) . " ,-", 1, 0, 'R');
    $pdf->Cell(30, 10, "Rp. " . number_format($total_pengeluaran) . " ,-", 1, 1, 'R');

    $pdf->Cell(140, 10, 'SALDO', 1, 0, 'C');
    $saldo = $total_pemasukan - $total_pengeluaran;
    $pdf->Cell(60, 10, "Rp. " . number_format($saldo) . " ,-", 1, 1, 'R');

} else {
    // Tampilkan pesan jika tidak ada data yang diproses
    $pdf->Cell(190, 10, 'Silahkan Filter Laporan Terlebih Dulu.', 1, 1, 'C');
}

$pdf->Output('Laporan.pdf', 'I');
?>
