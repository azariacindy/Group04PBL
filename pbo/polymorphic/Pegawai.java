package polymorphic;
public class Pegawai {

    public String nip;
    public String nama;

    public Pegawai() {}

    public Pegawai(String nip, String nama) {
        this.nip = nip;
        this.nama = nama;
    }

    public static void train(Pegawai pegawai) {
        System.out.println("Memberikan pelatihan untuk pegawai:");
        pegawai.displayInfo();
    }

    public void displayInfo() {
        System.out.println("NIP: " + nip);
        System.out.println("Nama: " + nama);
    }
}