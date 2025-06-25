
import { Buku, Peminjaman } from '@/types';

export const mockBooks: Buku[] = [
  {
    id_buku: 1,
    judul: 'Panduan Organisasi Mahasiswa',
    pengarang: 'Dr. Ahmad Susanto',
    kategori: 'Manajemen',
    status: 'tersedia',
    cover_url: '/placeholder.svg'
  },
  {
    id_buku: 2,
    judul: 'Kepemimpinan di Era Digital',
    pengarang: 'Prof. Sari Melati',
    kategori: 'Leadership',
    status: 'dipinjam',
    cover_url: '/placeholder.svg'
  },
  {
    id_buku: 3,
    judul: 'Komunikasi Efektif dalam Tim',
    pengarang: 'Ir. Budi Hartono',
    kategori: 'Komunikasi',
    status: 'tersedia',
    cover_url: '/placeholder.svg'
  },
  {
    id_buku: 4,
    judul: 'Manajemen Event dan Kegiatan',
    pengarang: 'Dra. Lisa Permata',
    kategori: 'Event Management',
    status: 'tersedia',
    cover_url: '/placeholder.svg'
  },
  {
    id_buku: 5,
    judul: 'Strategi Fundraising Organisasi',
    pengarang: 'M. Rizki Pratama',
    kategori: 'Finance',
    status: 'dipinjam',
    cover_url: '/placeholder.svg'
  }
];

export const mockPeminjaman: Peminjaman[] = [
  {
    id_peminjaman: 1,
    id_user: 2,
    id_buku: 2,
    tanggal_pinjam: '2024-06-18',
    estimasi_kembali: '2024-06-25',
    status: 'aktif'
  },
  {
    id_peminjaman: 2,
    id_user: 2,
    id_buku: 5,
    tanggal_pinjam: '2024-06-20',
    estimasi_kembali: '2024-06-27',
    status: 'terlambat'
  }
];
