# Validasi `total` dengan aturan `numeric`: cukup atau tidak?

**Pertanyaan:** Kalau field `total` tetap dikirim dari form dan divalidasi dengan aturan `numeric`,
apakah itu cukup untuk mencegah manipulasi total?

## Jawaban singkat: **Tidak.**

Aturan `numeric` (atau `integer`, `min:0`, dan sejenisnya) hanya memeriksa **format** nilai:
"apakah ini angka?". Aturan ini tidak memeriksa **kebenaran** nilai: "apakah ini total yang benar?".

Penyerang tidak perlu mengirim teks seperti `"abc"` untuk memanipulasi total. Ia cukup mengirim
angka yang valid, misalnya `1` atau `0.01`, dan validasi `numeric` akan meloloskannya. Jika server
mempercayai `total` dari klien dan tidak menghitung ulang dari item transaksi, manipulasi sangat mudah
dilakukan lewat browser DevTools atau `curl`.

## Solusi yang benar

Total harus dihitung ulang di server dari harga yang tersimpan di database, bukan dari input klien.
Pada project kita, `TransactionController::store()` sudah melakukan ini: harga diambil dengan
`Product::findOrFail()`, subtotal dihitung di server, lalu total dijumlahkan dari hasil itu. Nilai
`total` dari klien tidak pernah dipakai sama sekali, jadi walaupun dikirim, nilainya diabaikan.

Prinsipnya: **validasi memastikan format input, sedangkan perhitungan di server memastikan kebenaran nilai.**
Keduanya perlu, tetapi tidak bisa saling menggantikan.