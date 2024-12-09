-- Create Database (optional if you want to create a new database)
CREATE DATABASE pbl_presma;
GO

USE pbl_presma;
GO

-- Table: user
CREATE TABLE [user] (
    id_user INT NOT NULL IDENTITY PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL  -- Using VARCHAR for ENUM
);
GO

-- Table: prodi
CREATE TABLE prodi (
    id_prodi INT NOT NULL IDENTITY PRIMARY KEY,
    nama_prodi VARCHAR(100) NOT NULL
);
GO

-- Table: dosen
CREATE TABLE dosen (
    nip INT NOT NULL PRIMARY KEY,
    id_user INT NOT NULL,
    prodi_id INT NOT NULL,
    nama_dosen VARCHAR(100) NOT NULL,
    tanggal_lahir_dsn DATE NOT NULL,
    alamat NVARCHAR(MAX),
    no_telp VARCHAR(20) NULL,
    CONSTRAINT FK_dosen_user FOREIGN KEY (id_user) REFERENCES [user](id_user),
    CONSTRAINT FK_dosen_prodi FOREIGN KEY (prodi_id) REFERENCES prodi(id_prodi)
);
GO

-- Table: mahasiswa
CREATE TABLE mahasiswa (
    nim INT NOT NULL PRIMARY KEY,
    id_user INT NOT NULL,
    id_prodi INT NOT NULL,
    nama_mhs VARCHAR(100) NOT NULL,
    tanggal_lahir_mhs DATE NOT NULL,
    alamat NVARCHAR(MAX),
    no_telp VARCHAR(20) NULL,
    CONSTRAINT FK_mahasiswa_user FOREIGN KEY (id_user) REFERENCES [user](id_user),
    CONSTRAINT FK_mahasiswa_prodi FOREIGN KEY (id_prodi) REFERENCES prodi(id_prodi)
);
GO

-- Table: tingkat
CREATE TABLE tingkat (
    id_tingkat INT NOT NULL IDENTITY PRIMARY KEY,
    nama_tingkat VARCHAR(100) NOT NULL
);
GO

-- Table: lomba
CREATE TABLE lomba (
    id_lomba INT NOT NULL IDENTITY PRIMARY KEY,
    id_tingkat INT NOT NULL,
    nama_lomba VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    detail_lomba NVARCHAR(MAX),
    gambar VARCHAR(255) NULL,
    CONSTRAINT FK_lomba_tingkat FOREIGN KEY (id_tingkat) REFERENCES tingkat(id_tingkat)
);
GO

-- Table: prestasi
CREATE TABLE prestasi (
    id_prestasi INT NOT NULL IDENTITY PRIMARY KEY,
    nim INT NOT NULL,
    nip INT NOT NULL,
    id_lomba INT NOT NULL,
    tanggal DATE NOT NULL,
    detail_lomba NVARCHAR(MAX),
    berkas VARCHAR(255) NULL,
    peringkat INT NULL,
    status_lomba VARCHAR(20) NOT NULL,  -- Using VARCHAR for ENUM
    status_validasi VARCHAR(50) NOT NULL,  -- Using VARCHAR for ENUM
    CONSTRAINT FK_prestasi_mahasiswa FOREIGN KEY (nim) REFERENCES mahasiswa(nim),
    CONSTRAINT FK_prestasi_dosen FOREIGN KEY (nip) REFERENCES dosen(nip),
    CONSTRAINT FK_prestasi_lomba FOREIGN KEY (id_lomba) REFERENCES lomba(id_lomba)
);
GO

-- Table: notifikasi
CREATE TABLE notifikasi (
    id_notifikasi INT NOT NULL IDENTITY PRIMARY KEY,
    id_dosen INT NOT NULL,
    id_mahasiswa INT NOT NULL,
    id_prestasi INT NOT NULL,
    pesan NVARCHAR(MAX) NOT NULL,
    status VARCHAR(20) DEFAULT 'baru',  -- Using VARCHAR for ENUM
    tanggal DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_notifikasi_dosen FOREIGN KEY (id_dosen) REFERENCES dosen(nip) ON DELETE CASCADE,
    CONSTRAINT FK_notifikasi_mahasiswa FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(nim) ON DELETE CASCADE,
    CONSTRAINT FK_notifikasi_prestasi FOREIGN KEY (id_prestasi) REFERENCES prestasi(id_prestasi)
);
GO

-- Insert into user
INSERT INTO [user] (username, password, role)
VALUES
    ('admin1', '$2y$10$6o7kuYoF30uTvGkLqaIBT.mMSDyifKfZsxwJ2VKPHUWW.WLXAWA92
', 'admin'),
    ('dosen1', '$2y$10$fdYRDYF5sgnwpXWOye1iV.ejsx.lP4E3qfDeJ75vV5dpEguni/nbC
', 'dosen'),
    ('mhs1', '$2y$10$KZbIuT126N4fY8QDgEIqyuiUyzAp1zCAhDCCh6sOOvXvSrlPxDXIe
', 'mahasiswa'),
    ('dosen2', '$2y$10$1AdvzpxN/uf2SzowxkbOUeaPSUGDKkDUiQV.7SlrLB3g5.r1y.zS2', 'dosen'),
    ('mhs2', '$10$HjaooBmbxp388u8tkcWDZuZhxzPGk.lMgQ9Z4xgsbPQmMQUT1FqTu
', 'mahasiswa');
GO

-- Insert into prodi
INSERT INTO prodi (nama_prodi)
VALUES
    ('Teknik Informatika'),
    ('Sistem Informasi');
GO

-- Insert into dosen
INSERT INTO dosen (nip, id_user, prodi_id, nama_dosen, tanggal_lahir_dsn, alamat, no_telp)
VALUES
    (1001, 6, 1, 'Dr. Suryadi', '1975-03-20', 'Jl. Sudirman', '08129876543'),
    (1002, 8, 2, 'Prof. Ana', '1980-02-25', 'Jl. Merdeka', '08123456789');
GO

-- Insert into mahasiswa
INSERT INTO mahasiswa (nim, id_user, id_prodi, nama_mhs, tanggal_lahir_mhs, alamat, no_telp)
VALUES
    (2001, 7, 1, 'Budi', '2000-05-10', 'Jl. Merdeka', '08123456789'),
    (2002, 9, 2, 'Siti', '2001-08-15', 'Jl. Jendral', '08123456788');
GO

-- Insert into tingkat
INSERT INTO tingkat (nama_tingkat)
VALUES
    ('Regional'),
    ('Nasional'),
    ('Internasional');
GO

-- Insert into lomba
INSERT INTO lomba (id_tingkat, nama_lomba, tanggal, detail_lomba, gambar)
VALUES
    (3, 'Kompetisi Pemrograman', '2024-12-15', 'Syarat: Mahasiswa Aktif, Membawa Laptop', 'path_to_image1.jpg'),
    (2, 'Lomba Desain Grafis', '2024-11-20', 'Lomba desain grafis untuk mahasiswa', 'path_to_image2.jpg'),
    (1, 'Lomba Presentasi Bisnis', '2024-10-10', 'Presentasi bisnis dengan tema inovasi teknologi', 'path_to_image3.jpg');
GO

-- Insert into prestasi
INSERT INTO prestasi (nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_lomba, status_validasi)
VALUES
    (2001, 1001, 1, '2024-12-20', 'Juara 1 Kompetisi Pemrograman', 'Piagam Penghargaan', 1, 'completed', 'skkm point 1'),
    (2002, 1002, 2, '2024-11-22', 'Juara 2 Lomba Desain Grafis', 'Medali Emas', 2, 'completed', 'skkm point 1');
GO

-- Insert into notifikasi
INSERT INTO notifikasi (id_dosen, id_mahasiswa, id_prestasi, pesan, status, tanggal)
VALUES
    (1001, 2001, 1, 'Mahasiswa Budi mengajukan bimbingan lomba Kompetisi Pemrograman.', 'baru', '2024-12-04 09:57:05'),
    (1002, 2002, 2, 'Mahasiswa Siti mengajukan bimbingan lomba Desain Grafis.', 'baru', '2024-12-04 09:57:05');
GO

-- Show all tables in the current database
SELECT name
FROM sys.tables;

-- Select all columns from all tables in the current database
SELECT 
    t.name AS TableName, 
    c.name AS ColumnName, 
    c.column_id, 
    tp.name AS DataType, 
    c.max_length, 
    c.precision, 
    c.scale, 
    c.is_nullable
FROM 
    sys.tables AS t
INNER JOIN 
    sys.columns AS c ON t.object_id = c.object_id
INNER JOIN 
    sys.types AS tp ON c.user_type_id = tp.user_type_id
ORDER BY 
    t.name, c.column_id;

select * from [user];
select * from dosen;
select * from mahasiswa;
select * from prodi;
select * from tingkat;
select * from lomba;
select * from prestasi;
select * from notifikasi;
-- Delete rows with id_user between 10 and 14
DELETE FROM tingkat
WHERE id_tingkat BETWEEN 4 AND 9;
GO
