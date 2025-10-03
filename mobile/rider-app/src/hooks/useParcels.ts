import { useCallback, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import NetInfo from '@react-native-community/netinfo';
import { api, ParcelDTO } from '@/api/client';

const STORAGE_KEY = 'rider-parcels-cache';

interface State {
  data: ParcelDTO[];
  loading: boolean;
  error: string | null;
  refresh: () => Promise<void>;
}

export const useParcels = (): State => {
  const [data, setData] = useState<ParcelDTO[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchFromApi = useCallback(async () => {
    try {
      const response = await api.get<{ data: ParcelDTO[] }>('/parcels', {
        params: {
          per_page: 100,
          status: 'pending,assigned,out_for_delivery',
        },
      });
      const list = response.data.data ?? [];
      setData(list);
      await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(list));
      setError(null);
    } catch (err: any) {
      console.warn('Parcel fetch failed', err?.response ?? err);
      setError(err?.response?.data?.message ?? 'No se pudo cargar la lista');
    }
  }, []);

  const refresh = useCallback(async () => {
    setLoading(true);
    await fetchFromApi();
    setLoading(false);
  }, [fetchFromApi]);

  useEffect(() => {
    const bootstrap = async () => {
      try {
        const cached = await AsyncStorage.getItem(STORAGE_KEY);
        if (cached) {
          setData(JSON.parse(cached));
        }
      } catch (error) {
        console.warn('Failed to parse cache', error);
      } finally {
        setLoading(false);
      }
    };

    bootstrap();
  }, []);

  useEffect(() => {
    let unsubscribe: (() => void) | undefined;

    if (NetInfo?.addEventListener) {
      unsubscribe = NetInfo.addEventListener((state) => {
        if (state.isConnected) {
          fetchFromApi();
        }
      });
    }

    fetchFromApi();

    return () => {
      if (unsubscribe) {
        unsubscribe();
      }
    };
  }, [fetchFromApi]);

  return { data, loading, error, refresh };
};
