<<<<<<< HEAD
public class TenagaKependidikan extends Pegawai{

    public String kategori;

    public TenagaKependidikan(){

    }

    public TenagaKependidikan(String nip, String nama, String kategori){
=======
public class TenagaKependidikan extends Pegawai {
    public String kategori;

    public TenagaKependidikan() {
    }

    public TenagaKependidikan(String nip, String nama, String kategori) {
>>>>>>> becf8d8808757a452a2ba61ef14f376cd1ed0e6c
        super(nip, nama);
        this.kategori = kategori;
    }

<<<<<<< HEAD
    public void displayInfo(){
        super.displayInfo();
        System.out.println("Kategori        : " + kategori);
    }
}
=======
    public void displayInfo() {
        super.displayInfo();
        System.out.println("Kategori: " + kategori);
    }
}
>>>>>>> becf8d8808757a452a2ba61ef14f376cd1ed0e6c
