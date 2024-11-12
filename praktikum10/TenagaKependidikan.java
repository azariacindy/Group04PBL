
public class TenagaKependidikan extends Pegawai {
    public String kategori;

    // Default constructor
    public TenagaKependidikan() {
    }

    // Parameterized constructor
    public TenagaKependidikan(String nip, String nama, String kategori) {
        super(nip, nama);
        this.kategori = kategori;
    }

    // Method to display information
    @Override
    public void displayInfo() {
        super.displayInfo();
        System.out.println("Kategori: " + kategori);
    }
}
