public class Demo {
    public static void main(String[] args) {
        // Membuat objek Dosen
        Dosen dosen1 = new Dosen("19940201", "Widia, S.Kom. M.Kom", "199402");
        Dosen dosen2 = new Dosen("19700105", "Muhammad, S.T, M.T", "197001");

        // Membuat objek Tenaga Kependidikan
        TenagaKependidikan tendik1 = new TenagaKependidikan("19750301", "Aida, A.Md.", "Tenaga Administrasi");
        TenagaKependidikan tendik2 = new TenagaKependidikan("19650304", "Rika, S.T.", "Tenaga Laboratorium");

        // Menampilkan informasi objek Dosen
        System.out.println("Informasi Dosen:");
        dosen1.displayInfo();
        dosen2.displayInfo();

        // Menampilkan informasi objek Tenaga Kependidikan
        System.out.println("\nInformasi Tenaga Kependidikan:");
        tendik1.displayInfo();
        tendik2.displayInfo();
    }
}
