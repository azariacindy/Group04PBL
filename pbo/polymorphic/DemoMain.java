package polymorphic;
import java.util.ArrayList;

public class DemoMain {

    public static void main(String[] args) {
        Dosen dosen1 = new Dosen("19940201", "Widia, S.Kom., M.Kom.", "199402");
        TenagaKependidikan tendik1 = new TenagaKependidikan("19750301", "Aida, A.Md.", "Tenaga Administrasi");

        train(dosen1);
        train(tendik1);
    }

    public static void train(Pegawai pegawai) {
        System.out.println("");
        pegawai.displayInfo();
        System.out.println("Mengenalkan lingkungan kampus");
        System.out.println("Menginfokan SOP/Juknis");

        if (pegawai instanceof Dosen) {
            System.out.println("Memberikan pelatihan pedagogik");
        }
    }
}
