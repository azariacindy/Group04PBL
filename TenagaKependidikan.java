public class TenagaKependidikan extends Pegawai {
    public String kategori;

    public TenagaKependidikan() {
    }

    public TenagaKependidikan(String nip, String nama, String kategori) {
        super(nip, nama);
        this.kategori = kategori;
    }

   
}
