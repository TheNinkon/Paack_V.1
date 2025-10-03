import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { api, attachToken, LoginResponse } from '@/api/client';
import { persistToken, readToken } from '@/storage/tokenStorage';

interface AuthState {
  token: string | null;
  user: LoginResponse['user'] | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthState | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [token, setToken] = useState<string | null>(null);
  const [user, setUser] = useState<LoginResponse['user'] | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const bootstrap = async () => {
      try {
        const stored = await readToken();
        if (stored) {
          setToken(stored);
          attachToken(stored);
          const response = await api.get('/me');
          setUser(response.data.user);
        }
      } catch (error) {
        console.warn('Failed to bootstrap auth state', error);
        await persistToken(null);
        setToken(null);
        attachToken(null);
      } finally {
        setLoading(false);
      }
    };

    bootstrap();
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const { data } = await api.post<LoginResponse>('/login', {
      email,
      password,
      device_name: 'RiderApp',
    });

    await persistToken(data.token);
    attachToken(data.token);
    setToken(data.token);
    setUser(data.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post('/logout');
    } catch (error) {
      console.warn('Failed to logout', error);
    } finally {
      await persistToken(null);
      attachToken(null);
      setToken(null);
      setUser(null);
    }
  }, []);

  const value = useMemo<AuthState>(() => ({ token, user, loading, login, logout }), [token, user, loading, login, logout]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = (): AuthState => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
