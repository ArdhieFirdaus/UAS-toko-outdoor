<?php
session_start();

// Pengecekan jika belum login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login.php');
    exit();
}

// Hanya admin dan owner yang bisa akses halaman ini
if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../dashboard.php');
    exit();
}

require_once '../Config/koneksi.php';

// Filter tanggal untuk laporan
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Hitung filter laporan
$filter_transaksi = count_rows($conn, "
    SELECT * FROM transaksi 
    WHERE status = 'selesai' 
    AND DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
");

$filter_revenue = fetch_one($conn, "
    SELECT SUM(total_harga) as total FROM transaksi 
    WHERE status = 'selesai' 
    AND DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
");
$filter_revenue = $filter_revenue['total'] ?? 0;

// Ambil 10 produk terlaris
$produk_terlaris = fetch_all($conn, "
    SELECT p.id_produk, p.nama_produk, p.kategori, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_penjualan
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
    WHERE t.status = 'selesai'
    AND DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 10
");

// Ambil 10 transaksi terakhir
$transaksi_terakhir = fetch_all($conn, "
    SELECT t.*, u.nama_lengkap 
    FROM transaksi t 
    JOIN user u ON t.id_user = u.id_user 
    WHERE t.status = 'selesai'
    AND DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
    ORDER BY t.id_transaksi DESC 
    LIMIT 10
");

// Set header untuk download file Excel dengan format yang kompatibel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Toko_Outdoor_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start XML Excel format
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?mso-application progid="Excel.Sheet"?>';
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 
 <Styles>
  <Style ss:ID="HeaderTitle">
   <Font ss:FontName="Calibri" ss:Size="18" ss:Bold="1" ss:Color="#667eea"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="SectionTitle">
   <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#667eea" ss:Pattern="Solid"/>
   <Alignment ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="TableHeader">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#667eea" ss:Pattern="Solid"/>
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="TableCell">
   <Font ss:FontName="Calibri" ss:Size="11"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="TableCellBold">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="TableCellCenter">
   <Font ss:FontName="Calibri" ss:Size="11"/>
   <Alignment ss:Horizontal="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="TableCellRight">
   <Font ss:FontName="Calibri" ss:Size="11"/>
   <Alignment ss:Horizontal="Right"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="TableCellRightBold">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/>
   <Alignment ss:Horizontal="Right"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="InfoLabel">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/>
  </Style>
  <Style ss:ID="InfoValue">
   <Font ss:FontName="Calibri" ss:Size="11"/>
  </Style>
  <Style ss:ID="SummaryLabel">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/>
   <Interior ss:Color="#f0f0f0" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
  <Style ss:ID="SummaryValue">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#dee2e6"/>
   </Borders>
  </Style>
 </Styles>
 
 <Worksheet ss:Name="Laporan">
  <Table>
   <Column ss:Width="30"/>
   <Column ss:Width="200"/>
   <Column ss:Width="120"/>
   <Column ss:Width="120"/>
   <Column ss:Width="150"/>
   
   <!-- Header Title -->
   <Row ss:Height="30">
    <Cell ss:MergeAcross="4" ss:StyleID="HeaderTitle">
     <Data ss:Type="String">LAPORAN TOKO OUTDOOR</Data>
    </Cell>
   </Row>
   
   <Row/>
   
   <!-- Info Section -->
   <Row>
    <Cell ss:StyleID="InfoLabel"><Data ss:Type="String">Periode Laporan:</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="InfoValue">
     <Data ss:Type="String"><?php echo date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)); ?></Data>
    </Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="InfoLabel"><Data ss:Type="String">Tanggal Cetak:</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="InfoValue">
     <Data ss:Type="String"><?php echo date('d/m/Y H:i:s'); ?></Data>
    </Cell>
   </Row>
   
   <Row/>
   <Row/>
   
   <!-- Ringkasan Section -->
   <Row>
    <Cell ss:MergeAcross="4" ss:StyleID="SectionTitle">
     <Data ss:Type="String">RINGKASAN LAPORAN</Data>
    </Cell>
   </Row>
   
   <Row/>
   
   <Row>
    <Cell ss:MergeAcross="1" ss:StyleID="SummaryLabel">
     <Data ss:Type="String">Total Transaksi</Data>
    </Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="SummaryValue">
     <Data ss:Type="String"><?php echo $filter_transaksi; ?> Transaksi</Data>
    </Cell>
   </Row>
   <Row>
    <Cell ss:MergeAcross="1" ss:StyleID="SummaryLabel">
     <Data ss:Type="String">Total Pendapatan</Data>
    </Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="SummaryValue">
     <Data ss:Type="String">Rp <?php echo number_format($filter_revenue, 0, ',', '.'); ?></Data>
    </Cell>
   </Row>
   
   <Row/>
   <Row/>
   
   <!-- Produk Terlaris Section -->
   <Row>
    <Cell ss:MergeAcross="4" ss:StyleID="SectionTitle">
     <Data ss:Type="String">10 PRODUK TERLARIS</Data>
    </Cell>
   </Row>
   
   <Row/>
   
   <!-- Table Header Produk -->
   <Row>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Nama Produk</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Kategori</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Terjual</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Total Penjualan</Data></Cell>
   </Row>
   
   <!-- Table Body Produk -->
   <?php if (count($produk_terlaris) > 0): ?>
    <?php foreach ($produk_terlaris as $index => $item): ?>
     <Row>
      <Cell ss:StyleID="TableCellCenter"><Data ss:Type="Number"><?php echo $index + 1; ?></Data></Cell>
      <Cell ss:StyleID="TableCellBold"><Data ss:Type="String"><?php echo htmlspecialchars($item['nama_produk']); ?></Data></Cell>
      <Cell ss:StyleID="TableCell"><Data ss:Type="String"><?php echo htmlspecialchars($item['kategori']); ?></Data></Cell>
      <Cell ss:StyleID="TableCellRight"><Data ss:Type="String"><?php echo $item['total_terjual']; ?> pcs</Data></Cell>
      <Cell ss:StyleID="TableCellRightBold"><Data ss:Type="String">Rp <?php echo number_format($item['total_penjualan'], 0, ',', '.'); ?></Data></Cell>
     </Row>
    <?php endforeach; ?>
   <?php else: ?>
    <Row>
     <Cell ss:MergeAcross="4" ss:StyleID="TableCellCenter">
      <Data ss:Type="String">Tidak ada data produk terjual</Data>
     </Cell>
    </Row>
   <?php endif; ?>
   
   <Row/>
   <Row/>
   
   <!-- Transaksi Terakhir Section -->
   <Row>
    <Cell ss:MergeAcross="4" ss:StyleID="SectionTitle">
     <Data ss:Type="String">10 TRANSAKSI TERAKHIR</Data>
    </Cell>
   </Row>
   
   <Row/>
   
   <!-- Table Header Transaksi -->
   <Row>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Tanggal</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Kasir</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Total</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Metode Pembayaran</Data></Cell>
   </Row>
   
   <!-- Table Body Transaksi -->
   <?php if (count($transaksi_terakhir) > 0): ?>
    <?php foreach ($transaksi_terakhir as $index => $item): ?>
     <?php $dateTime = new DateTime($item['tanggal_transaksi']); ?>
     <Row>
      <Cell ss:StyleID="TableCellCenter"><Data ss:Type="Number"><?php echo $index + 1; ?></Data></Cell>
      <Cell ss:StyleID="TableCell"><Data ss:Type="String"><?php echo $dateTime->format('d/m/Y H:i'); ?></Data></Cell>
      <Cell ss:StyleID="TableCell"><Data ss:Type="String"><?php echo htmlspecialchars($item['nama_lengkap']); ?></Data></Cell>
      <Cell ss:StyleID="TableCellBold"><Data ss:Type="String">Rp <?php echo number_format($item['total_harga'], 0, ',', '.'); ?></Data></Cell>
      <Cell ss:StyleID="TableCell"><Data ss:Type="String"><?php echo ucfirst(str_replace('_', ' ', $item['metode_pembayaran'])); ?></Data></Cell>
     </Row>
    <?php endforeach; ?>
   <?php else: ?>
    <Row>
     <Cell ss:MergeAcross="4" ss:StyleID="TableCellCenter">
      <Data ss:Type="String">Tidak ada transaksi</Data>
     </Cell>
    </Row>
   <?php endif; ?>
   
   <Row/>
   <Row/>
   
   <!-- Footer -->
   <Row>
    <Cell ss:MergeAcross="4" ss:StyleID="InfoValue">
     <Data ss:Type="String">Laporan ini digenerate secara otomatis oleh Sistem Informasi Toko Outdoor</Data>
    </Cell>
   </Row>
   
  </Table>
 </Worksheet>
</Workbook>
