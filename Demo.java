import java.util.ArrayList;

public class Demo {
    public static void main(String[] args) {
        // Membuat objek Dosen
        Dosen dosen1 = new Dosen("19940201", "Widia, S.Kom. M.Kom", "199402");
        Dosen dosen2 = new Dosen("19700105", "Muhammad, S.T, M.T", "197001");

        // Membuat objek Tenaga Kependidikan
        TenagaKependidikan tendik1 = new TenagaKependidikan("19750301", "Aida, A.Md.", "Tenaga Administrasi");
        TenagaKependidikan tendik2 = new TenagaKependidikan("19650304", "Rika, S.T.", "Tenaga Laboratorium");

        // Membuat ArrayList dari objek Pegawai
        ArrayList<Pegawai> daftarPegawai = new ArrayList<>();

        // Menambahkan objek Dosen dan Tenaga Kependidikan ke dalam daftarPegawai
        daftarPegawai.add(dosen1);
        daftarPegawai.add(dosen2);
        daftarPegawai.add(tendik1);
        daftarPegawai.add(tendik2);

        // Menampilkan informasi setiap pegawai di dalam daftarPegawai
        System.out.println("Informasi Pegawai:");
        for (Pegawai pegawai : daftarPegawai) {
            pegawai.displayInfo();
            System.out.println(); // Menambahkan baris kosong untuk pemisah
        }

        // Menampilkan jumlah pegawai dalam daftarPegawai
        System.out.println("Jumlah Pegawai: " + daftarPegawai.size());
    }
}
