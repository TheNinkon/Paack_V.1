import { Platform } from 'react-native';
import * as SecureStore from 'expo-secure-store';
import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = 'rider-token';

export const readToken = async (): Promise<string | null> => {
  if (Platform.OS === 'web') {
    try {
      return await AsyncStorage.getItem(TOKEN_KEY);
    } catch (error) {
      console.warn('Unable to read token from storage', error);
      return null;
    }
  }

  try {
    return await SecureStore.getItemAsync(TOKEN_KEY);
  } catch (error) {
    console.warn('SecureStore getItem failed', error);
    return null;
  }
};

export const persistToken = async (token: string | null): Promise<void> => {
  if (Platform.OS === 'web') {
    if (token) {
      await AsyncStorage.setItem(TOKEN_KEY, token);
    } else {
      await AsyncStorage.removeItem(TOKEN_KEY);
    }
    return;
  }

  if (token) {
    await SecureStore.setItemAsync(TOKEN_KEY, token);
  } else {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
  }
};
