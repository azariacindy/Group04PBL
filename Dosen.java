<<<<<<< HEAD
public class Dosen extends Pegawai{

    public String nidn;

    public Dosen(){

    }

    public Dosen(String nip, String nama, String nidn){
=======
public class Dosen extends Pegawai {
    public String nidn;

    public Dosen() {
    }

    public Dosen(String nip, String nama, String nidn) {
>>>>>>> becf8d8808757a452a2ba61ef14f376cd1ed0e6c
        super(nip, nama);
        this.nidn = nidn;
    }

<<<<<<< HEAD
    public void displayInfo(){

        super.displayInfo();
        System.out.println("NIDN        : " + nidn);
    }

    public void mengajar(){
        System.out.println("Membuat rencana pembelajaran");
        System.out.println("Menyusun Materi");
        System.out.println("Melaksanakan PBM");
        System.out.println("Melaksanakan PBM");
        System.out.println("Melakukan Evaluasi");
    }
}
=======
    public void displayInfo() {
        super.displayInfo();
        System.out.println("NIDN: " + nidn);
    }

    public void mengajar() {
        System.out.println("Membuat rencana pembelajaran");
        System.out.println("Menyusun materi");
        System.out.println("Melaksanakan PBM");
        System.out.println("Melakukan evaluasi");
    }
}
>>>>>>> becf8d8808757a452a2ba61ef14f376cd1ed0e6c
