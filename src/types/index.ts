
export interface User {
  id_user: number;
  nama: string;
  username: string;
  password: string;
  jabatan_id: number;
  divisi: string;
  angkatan: string;
  no_wa: string;
  jabatan?: Jabatan;
}

export interface Jabatan {
  id_jabatan: number;
  nama_jabatan: string;
}

export interface Buku {
  id_buku: number;
  judul: string;
  pengarang: string;
  kategori: string;
  status: 'tersedia' | 'dipinjam' | 'rusak';
  cover_url?: string;
}

export interface Peminjaman {
  id_peminjaman: number;
  id_user: number;
  id_buku: number;
  tanggal_pinjam: string;
  estimasi_kembali: string;
  status: 'aktif' | 'dikembalikan' | 'terlambat';
  user?: User;
  buku?: Buku;
}

export interface Pengembalian {
  id_pengembalian: number;
  id_peminjaman: number;
  tanggal_kembali: string;
  id_user_pengelola: number;
  peminjaman?: Peminjaman;
  user_pengelola?: User;
}

export interface AuthState {
  isAuthenticated: boolean;
  user: User | null;
  isAdmin: boolean;
}
