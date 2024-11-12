# Group04PBL
<<<<<<< HEAD
1.	Apakah upcasting dapat dilakukan dari suatu class terhadap class lain yang tidak memiliki relasi inheritance?
-> tidak, karena upcatsing hanya bisa dilakukan pada class yang memiliki hubungan inheritance. Upcasting berarti melihat objek dari subkelas sebagai objek dari superclass-nya, maka Upcasting adalah mustahil untuk mengalami karena mereka tidak memiliki tipe yang sama dalam hierarki pewarisan.

2.	Dari 2 baris kode program berikut, manakan proses upcasting yang tepat? Jelaskan!
Pegawai pegawai1 = new Dosen();
-> objek Dosen secara otomatis di-cast ke tipe Pegawai karena Dosen adalah subclass dari Pegawai. Upcasting ini tidak memerlukan cast eksplisit ( '(Pegawai)' ), karena Java secara otomatis memahami bahwa Dosen adalah tipe Pegawai.

Pegawai pegawai1 = (Pegawai) new Dosen();
-> kode berikut eksplisit, tetapi tidak diperlukan.

3.	Apa fungsi dari keyword instanceOf?
4.	Apa yang dimaksud heterogenous collection?
5.	Sebuah object diinstansiasi dari class Pegawai. Kemudian dilakukan downcasting menjadi object bertipe Dosen. Apakah hal ini dapat dilakukan? Lakukan percobaan untuk membuktikannya.
=======
Jawaban Soal

1. Upcasting tidak dapat dilakukan antara dua kelas yang tidak memiliki relasi inheritance (pewarisan). Upcasting hanya dapat dilakukan jika kelas yang ingin di-cast memiliki hubungan inheritance dengan kelas yang menjadi target casting. Dalam konsep pewarisan, upcasting adalah proses mengubah objek yang bertipe subclass (kelas turunan) menjadi tipe superclass (kelas induk)

2. 
3. Keyword instanceof dalam Java digunakan untuk memeriksa apakah suatu objek adalah instance (contoh) dari kelas tertentu atau implementasi dari interface tertentu.
4.Heterogeneous collection adalah kumpulan data atau objek yang bisa berisi berbagai jenis data atau objek dari kelas yang berbeda. Dengan kata lain, dalam satu koleksi, kita bisa menyimpan objek yang berasal dari tipe data atau kelas yang berbeda-beda.
