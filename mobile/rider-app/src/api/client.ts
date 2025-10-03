import axios from 'axios';
import Constants from 'expo-constants';

const baseURL =
  Constants.expoConfig?.extra?.apiBaseUrl ??
  process.env.EXPO_PUBLIC_API_BASE_URL ??
  'http://10.0.2.2:8000/api/rider';

export const api = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
});

export const attachToken = (token: string | null) => {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common.Authorization;
  }
};

export type LoginResponse = {
  token: string;
  token_type: string;
  expires_at: string | null;
  user: {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    client_id: number | null;
    courier: {
      id: number;
      vehicle_type: string | null;
      external_code: string | null;
      active: boolean;
    } | null;
  };
};

export type ParcelDTO = {
  id: number;
  code: string;
  status: string;
  provider: {
    id: number;
    name: string;
  } | null;
  stop_code: string | null;
  address_line: string | null;
  city: string | null;
  state: string | null;
  postal_code: string | null;
  liquidation_code: string | null;
  liquidation_reference: string | null;
  latest_scan_at: string | null;
  latest_scan_by: {
    id: number;
    name: string;
  } | null;
  updated_at: string | null;
  created_at: string | null;
};
