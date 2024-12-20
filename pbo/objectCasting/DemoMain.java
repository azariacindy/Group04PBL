package objectCasting;
import java.util.ArrayList;

public class DemoMain {
    public static void main(String[] args) {
        Dosen dosen1 = new Dosen("19940201", "Widia, S.Kom., M.Kom.", "199402");
        Dosen dosen2 = new Dosen("19700105", "Muhammad, S.T., M.T.", "197001");

        System.out.println(dosen1.nip);
        System.out.println(dosen1.nama);
        System.out.println(dosen1.nidn);
        dosen1.mengajar();
        System.out.println("");

        Pegawai pegawai1 = dosen1;

        System.out.println(pegawai1.nip);
        System.out.println(pegawai1.nama);

        pegawai1.displayInfo();
        System.out.println("");

        TenagaKependidikan tendik1 = new TenagaKependidikan("19750301", "Aida, A.Md.", "Tenaga Administrasi");
        TenagaKependidikan tendik2 = new TenagaKependidikan("19650304", "Rika, S.T.", "Tenaga Laboratorium");

        TenagaKependidikan test = (TenagaKependidikan) tendik2;

        Dosen newDosen = (Dosen) pegawai1;

        System.out.println(newDosen.nama);
        System.out.println(newDosen.nidn);
        newDosen.mengajar();
        System.out.println("");

        ArrayList<Pegawai> daftarPegawai = new ArrayList<Pegawai>();
        daftarPegawai.add(dosen1);
        daftarPegawai.add(dosen2);
        daftarPegawai.add(tendik1);
        daftarPegawai.add(tendik2);

        printDaftarPegawai(daftarPegawai);
    }

    public static void printDaftarPegawai(ArrayList<Pegawai> daftarPegawai) {
        for (Pegawai pegawai : daftarPegawai) {
            pegawai.displayInfo();
            System.out.println();
        }
    }
}
