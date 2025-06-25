
import React from 'react';
import Layout from '@/components/layout/Layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Book, Users, Calendar, AlertTriangle, TrendingUp, Clock } from 'lucide-react';
import { mockBooks, mockPeminjaman } from '@/data/mockData';

const AdminDashboard = () => {
  const totalBooks = mockBooks.length;
  const availableBooks = mockBooks.filter(book => book.status === 'tersedia').length;
  const borrowedBooks = mockBooks.filter(book => book.status === 'dipinjam').length;
  const overdueLoans = mockPeminjaman.filter(loan => loan.status === 'terlambat').length;

  const stats = [
    {
      title: 'Total Buku',
      value: totalBooks,
      description: 'Koleksi buku digital',
      icon: Book,
      color: 'bg-blue-500'
    },
    {
      title: 'Buku Tersedia',
      value: availableBooks,
      description: 'Siap dipinjam',
      icon: TrendingUp,
      color: 'bg-green-500'
    },
    {
      title: 'Sedang Dipinjam',
      value: borrowedBooks,
      description: 'Aktif peminjaman',
      icon: Users,
      color: 'bg-orange-500'
    },
    {
      title: 'Terlambat',
      value: overdueLoans,
      description: 'Perlu tindak lanjut',
      icon: AlertTriangle,
      color: 'bg-red-500'
    }
  ];

  const recentActivities = [
    { action: 'Buku dipinjam', detail: 'Kepemimpinan di Era Digital', time: '2 jam lalu', user: 'Anggota Persma' },
    { action: 'Buku dikembalikan', detail: 'Panduan Organisasi Mahasiswa', time: '5 jam lalu', user: 'Budi Santoso' },
    { action: 'Buku baru ditambahkan', detail: 'Manajemen Waktu Efektif', time: '1 hari lalu', user: 'Admin' },
    { action: 'Keterlambatan pengembalian', detail: 'Strategi Fundraising Organisasi', time: '2 hari lalu', user: 'Sari Melati' }
  ];

  return (
    <Layout>
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Dashboard Administrator</h1>
            <p className="text-gray-600 mt-1">Selamat datang kembali! Kelola sistem perpustakaan Persma dengan mudah.</p>
          </div>
          <div className="text-right">
            <p className="text-sm text-gray-500">Hari ini</p>
            <p className="text-lg font-semibold text-gray-900">
              {new Date().toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
              })}
            </p>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {stats.map((stat) => (
            <Card key={stat.title} className="hover:shadow-lg transition-shadow">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">{stat.title}</p>
                    <p className="text-3xl font-bold text-gray-900 mt-2">{stat.value}</p>
                    <p className="text-sm text-gray-500 mt-1">{stat.description}</p>
                  </div>
                  <div className={`w-12 h-12 ${stat.color} rounded-lg flex items-center justify-center`}>
                    <stat.icon className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Recent Activities */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Clock className="w-5 h-5 mr-2 text-gray-600" />
                Aktivitas Terkini
              </CardTitle>
              <CardDescription>
                Pantau aktivitas sistem perpustakaan secara real-time
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentActivities.map((activity, index) => (
                  <div key={index} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div className="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900">{activity.action}</p>
                      <p className="text-sm text-gray-600">{activity.detail}</p>
                      <div className="flex items-center justify-between mt-1">
                        <p className="text-xs text-gray-500">{activity.user}</p>
                        <p className="text-xs text-gray-400">{activity.time}</p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Quick Actions */}
          <Card>
            <CardHeader>
              <CardTitle>Aksi Cepat</CardTitle>
              <CardDescription>
                Akses fitur utama dengan sekali klik
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 gap-4">
                <button className="p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors">
                  <Book className="w-8 h-8 text-green-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-green-800">Tambah Buku</p>
                </button>
                <button className="p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                  <Users className="w-8 h-8 text-blue-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-blue-800">Kelola Peminjaman</p>
                </button>
                <button className="p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border border-orange-200 transition-colors">
                  <Calendar className="w-8 h-8 text-orange-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-orange-800">Monitoring</p>
                </button>
                <button className="p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors">
                  <TrendingUp className="w-8 h-8 text-purple-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-purple-800">Laporan</p>
                </button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </Layout>
  );
};

export default AdminDashboard;
