import React from 'react';
import { ActivityIndicator, FlatList, RefreshControl, SafeAreaView, StyleSheet, Text, View } from 'react-native';
import { useAuth } from '@/contexts/AuthContext';
import { useParcels } from '@/hooks/useParcels';
import { ParcelCard } from '@/components/ParcelCard';
import { ParcelDTO } from '@/api/client';

export const ParcelListScreen: React.FC = () => {
  const { user, logout } = useAuth();
  const { data, loading, error, refresh } = useParcels();

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.welcome}>Hola, {user?.name ?? 'Courier'}</Text>
          <Text style={styles.subtitle}>Tienes {data.length} bultos pendientes</Text>
        </View>
        <Text style={styles.logout} onPress={logout}>
          Cerrar sesión
        </Text>
      </View>
      {loading && data.length === 0 ? (
        <View style={styles.loader}>
          <ActivityIndicator size="large" color="#2563EB" />
          <Text style={styles.loaderText}>Cargando bultos…</Text>
        </View>
      ) : (
        <FlatList
          data={data}
          keyExtractor={(item: ParcelDTO) => String(item.id)}
          renderItem={({ item }) => <ParcelCard item={item} />}
          contentContainerStyle={styles.list}
          ItemSeparatorComponent={() => <View style={{ height: 16 }} />}
          refreshControl={<RefreshControl refreshing={loading} onRefresh={refresh} />}
          ListEmptyComponent={
            !loading ? (
              <View style={styles.empty}>
                <Text style={styles.emptyTitle}>No hay bultos asignados</Text>
                <Text style={styles.emptySubtitle}>Cuando tengas ruta asignada aparecerá aquí.</Text>
              </View>
            ) : null
          }
        />
      )}
      {error && (
        <View style={styles.errorBanner}>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F3F4F6',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  welcome: {
    fontSize: 22,
    fontWeight: '700',
    color: '#111827',
  },
  subtitle: {
    fontSize: 14,
    color: '#6B7280',
  },
  logout: {
    color: '#EF4444',
    fontWeight: '600',
  },
  list: {
    padding: 20,
    paddingBottom: 40,
  },
  loader: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    gap: 12,
  },
  loaderText: {
    color: '#6B7280',
  },
  empty: {
    paddingVertical: 80,
    alignItems: 'center',
    gap: 8,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: '#111827',
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
  },
  errorBanner: {
    position: 'absolute',
    bottom: 30,
    left: 20,
    right: 20,
    backgroundColor: '#FECACA',
    borderRadius: 10,
    padding: 12,
    borderWidth: 1,
    borderColor: '#FCA5A5',
  },
  errorText: {
    color: '#991B1B',
    textAlign: 'center',
  },
});
