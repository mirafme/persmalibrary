
import React from 'react';
import Layout from '@/components/layout/Layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Book, Calendar, Clock, Search, History, Star } from 'lucide-react';
import { useAuth } from '@/context/AuthContext';
import { mockBooks, mockPeminjaman } from '@/data/mockData';
import { Button } from '@/components/ui/button';

const MemberDashboard = () => {
  const { user } = useAuth();
  
  const userLoans = mockPeminjaman.filter(loan => loan.id_user === user?.id_user);
  const activeLoans = userLoans.filter(loan => loan.status === 'aktif').length;
  const overdueLoans = userLoans.filter(loan => loan.status === 'terlambat').length;
  const availableBooks = mockBooks.filter(book => book.status === 'tersedia').length;

  const recentBooks = mockBooks.slice(0, 3);
  const myCurrentLoans = userLoans.slice(0, 2);

  return (
    <Layout>
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">
              Selamat datang, {user?.nama}!
            </h1>
            <p className="text-gray-600 mt-1">
              Jelajahi koleksi buku digital Persma dan kelola peminjaman Anda.
            </p>
          </div>
          <div className="text-right">
            <p className="text-sm text-gray-500">Divisi: {user?.divisi}</p>
            <p className="text-sm text-gray-500">Angkatan: {user?.angkatan}</p>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card className="border-l-4 border-l-green-500">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Sedang Dipinjam</p>
                  <p className="text-3xl font-bold text-gray-900 mt-2">{activeLoans}</p>
                  <p className="text-sm text-gray-500">Buku aktif</p>
                </div>
                <Book className="w-8 h-8 text-green-500" />
              </div>
            </CardContent>
          </Card>

          <Card className="border-l-4 border-l-orange-500">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Terlambat</p>
                  <p className="text-3xl font-bold text-gray-900 mt-2">{overdueLoans}</p>
                  <p className="text-sm text-gray-500">Perlu dikembalikan</p>
                </div>
                <Clock className="w-8 h-8 text-orange-500" />
              </div>
            </CardContent>
          </Card>

          <Card className="border-l-4 border-l-blue-500">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Tersedia</p>
                  <p className="text-3xl font-bold text-gray-900 mt-2">{availableBooks}</p>
                  <p className="text-sm text-gray-500">Buku dapat dipinjam</p>
                </div>
                <Search className="w-8 h-8 text-blue-500" />
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* My Current Loans */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <History className="w-5 h-5 mr-2 text-gray-600" />
                Peminjaman Aktif Saya
              </CardTitle>
              <CardDescription>
                Buku yang sedang Anda pinjam
              </CardDescription>
            </CardHeader>
            <CardContent>
              {myCurrentLoans.length > 0 ? (
                <div className="space-y-4">
                  {myCurrentLoans.map((loan) => {
                    const book = mockBooks.find(b => b.id_buku === loan.id_buku);
                    const isOverdue = loan.status === 'terlambat';
                    return (
                      <div key={loan.id_peminjaman} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <img 
                          src={book?.cover_url || '/placeholder.svg'} 
                          alt={book?.judul}
                          className="w-12 h-16 object-cover rounded"
                        />
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-medium text-gray-900">{book?.judul}</p>
                          <p className="text-sm text-gray-600">{book?.pengarang}</p>
                          <div className="flex items-center justify-between mt-2">
                            <span className={`text-xs px-2 py-1 rounded-full ${
                              isOverdue 
                                ? 'bg-red-100 text-red-800' 
                                : 'bg-green-100 text-green-800'
                            }`}>
                              {isOverdue ? 'Terlambat' : 'Aktif'}
                            </span>
                            <p className="text-xs text-gray-500">
                              Kembali: {new Date(loan.estimasi_kembali).toLocaleDateString('id-ID')}
                            </p>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              ) : (
                <div className="text-center py-8">
                  <Book className="w-12 h-12 text-gray-400 mx-auto mb-3" />
                  <p className="text-gray-500">Belum ada buku yang dipinjam</p>
                  <Button className="mt-3 bg-green-600 hover:bg-green-700">
                    Jelajahi Katalog
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Recommended Books */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Star className="w-5 h-5 mr-2 text-gray-600" />
                Buku Terbaru
              </CardTitle>
              <CardDescription>
                Koleksi terbaru yang tersedia untuk dipinjam
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentBooks.map((book) => (
                  <div key={book.id_buku} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <img 
                      src={book.cover_url || '/placeholder.svg'} 
                      alt={book.judul}
                      className="w-12 h-16 object-cover rounded"
                    />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900">{book.judul}</p>
                      <p className="text-sm text-gray-600">{book.pengarang}</p>
                      <p className="text-xs text-gray-500 mt-1">{book.kategori}</p>
                      <span className={`text-xs px-2 py-1 rounded-full mt-2 inline-block ${
                        book.status === 'tersedia' 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-red-100 text-red-800'
                      }`}>
                        {book.status === 'tersedia' ? 'Tersedia' : 'Dipinjam'}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Aksi Cepat</CardTitle>
            <CardDescription>
              Akses fitur utama dengan mudah
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <button className="p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors">
                <Search className="w-8 h-8 text-green-600 mx-auto mb-2" />
                <p className="text-sm font-medium text-green-800">Cari Buku</p>
              </button>
              <button className="p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                <Book className="w-8 h-8 text-blue-600 mx-auto mb-2" />
                <p className="text-sm font-medium text-blue-800">Katalog</p>
              </button>
              <button className="p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border border-orange-200 transition-colors">
                <History className="w-8 h-8 text-orange-600 mx-auto mb-2" />
                <p className="text-sm font-medium text-orange-800">Riwayat</p>
              </button>
              <button className="p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors">
                <Calendar className="w-8 h-8 text-purple-600 mx-auto mb-2" />
                <p className="text-sm font-medium text-purple-800">Jadwal</p>
              </button>
            </div>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
};

export default MemberDashboard;
