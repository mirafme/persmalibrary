
import React, { createContext, useContext, useState, useEffect } from 'react';
import { AuthState, User } from '@/types';

interface AuthContextType extends AuthState {
  login: (username: string, password: string) => Promise<boolean>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

// Mock data untuk demo - dalam implementasi nyata akan terhubung ke database
const mockUsers: User[] = [
  {
    id_user: 1,
    nama: 'Administrator Persma',
    username: 'admin',
    password: 'admin123',
    jabatan_id: 1,
    divisi: 'Pengurus Inti',
    angkatan: '2022',
    no_wa: '081234567890',
    jabatan: { id_jabatan: 1, nama_jabatan: 'Administrator' }
  },
  {
    id_user: 2,
    nama: 'Anggota Persma',
    username: 'anggota',
    password: 'anggota123',
    jabatan_id: 2,
    divisi: 'Anggota Biasa',
    angkatan: '2023',
    no_wa: '081234567891',
    jabatan: { id_jabatan: 2, nama_jabatan: 'Anggota' }
  }
];

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [authState, setAuthState] = useState<AuthState>({
    isAuthenticated: false,
    user: null,
    isAdmin: false
  });

  useEffect(() => {
    // Check localStorage for existing session
    const storedUser = localStorage.getItem('persma_user');
    if (storedUser) {
      const user = JSON.parse(storedUser);
      setAuthState({
        isAuthenticated: true,
        user,
        isAdmin: user.jabatan?.nama_jabatan === 'Administrator'
      });
    }
  }, []);

  const login = async (username: string, password: string): Promise<boolean> => {
    console.log('Attempting login:', { username, password });
    
    const user = mockUsers.find(u => u.username === username && u.password === password);
    
    if (user) {
      const authData = {
        isAuthenticated: true,
        user,
        isAdmin: user.jabatan?.nama_jabatan === 'Administrator'
      };
      
      setAuthState(authData);
      localStorage.setItem('persma_user', JSON.stringify(user));
      console.log('Login successful:', user);
      return true;
    }
    
    console.log('Login failed: Invalid credentials');
    return false;
  };

  const logout = () => {
    setAuthState({
      isAuthenticated: false,
      user: null,
      isAdmin: false
    });
    localStorage.removeItem('persma_user');
    console.log('User logged out');
  };

  return (
    <AuthContext.Provider value={{
      ...authState,
      login,
      logout
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
