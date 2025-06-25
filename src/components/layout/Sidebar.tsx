
import React from 'react';
import { useAuth } from '@/context/AuthContext';
import { useLocation, useNavigate } from 'react-router-dom';
import { 
  BookOpen, 
  Users, 
  Calendar, 
  BarChart3, 
  Settings, 
  LogOut,
  Home,
  Search,
  History,
  Book
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const Sidebar = () => {
  const { user, isAdmin, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();

  const adminMenuItems = [
    { icon: Home, label: 'Dashboard', path: '/admin/dashboard' },
    { icon: Book, label: 'Kelola Buku', path: '/admin/books' },
    { icon: Users, label: 'Kelola Peminjaman', path: '/admin/loans' },
    { icon: BarChart3, label: 'Monitoring', path: '/admin/monitoring' },
    { icon: Settings, label: 'Pengaturan', path: '/admin/settings' }
  ];

  const memberMenuItems = [
    { icon: Home, label: 'Dashboard', path: '/member/dashboard' },
    { icon: Search, label: 'Katalog Buku', path: '/member/catalog' },
    { icon: History, label: 'Riwayat Pinjam', path: '/member/history' },
    { icon: Settings, label: 'Profil', path: '/member/profile' }
  ];

  const menuItems = isAdmin ? adminMenuItems : memberMenuItems;

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="w-64 bg-white shadow-lg h-screen flex flex-col">
      {/* Header */}
      <div className="p-6 border-b border-gray-200">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
            <BookOpen className="w-6 h-6 text-white" />
          </div>
          <div>
            <h1 className="font-bold text-lg text-gray-900">Persma Library</h1>
            <p className="text-sm text-gray-500">Digital Book Management</p>
          </div>
        </div>
      </div>

      {/* User Info */}
      <div className="p-4 border-b border-gray-200">
        <div className="flex items-center space-x-3">
          <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <span className="text-green-600 font-semibold text-sm">
              {user?.nama.charAt(0).toUpperCase()}
            </span>
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium text-gray-900 truncate">
              {user?.nama}
            </p>
            <p className="text-xs text-gray-500">
              {isAdmin ? 'Administrator' : 'Anggota'}
            </p>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="flex-1 p-4 space-y-2">
        {menuItems.map((item) => {
          const isActive = location.pathname === item.path;
          return (
            <Button
              key={item.path}
              variant={isActive ? "default" : "ghost"}
              className={cn(
                "w-full justify-start",
                isActive 
                  ? "bg-green-600 text-white hover:bg-green-700" 
                  : "text-gray-700 hover:bg-gray-100"
              )}
              onClick={() => navigate(item.path)}
            >
              <item.icon className="w-4 h-4 mr-3" />
              {item.label}
            </Button>
          );
        })}
      </nav>

      {/* Logout */}
      <div className="p-4 border-t border-gray-200">
        <Button
          variant="ghost"
          className="w-full justify-start text-red-600 hover:bg-red-50 hover:text-red-700"
          onClick={handleLogout}
        >
          <LogOut className="w-4 h-4 mr-3" />
          Keluar
        </Button>
      </div>
    </div>
  );
};

export default Sidebar;
