import React from 'react';
import { ParcelDTO } from '@/api/client';
import { Pressable, StyleSheet, Text, View } from 'react-native';

const STATUS_COLORS: Record<string, string> = {
  pending: '#6B7280',
  assigned: '#2563EB',
  out_for_delivery: '#F97316',
  delivered: '#10B981',
  incident: '#F59E0B',
  returned: '#F87171',
};

interface Props {
  item: ParcelDTO;
  onPress?: () => void;
}

export const ParcelCard: React.FC<Props> = ({ item, onPress }) => {
  const color = STATUS_COLORS[item.status] ?? '#1F2937';

  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.container, pressed && styles.pressed]}>
      <View style={styles.header}>
        <Text style={styles.code}>{item.code}</Text>
        <View style={[styles.badge, { backgroundColor: color }]}>
          <Text style={styles.badgeText}>{item.status.replace(/_/g, ' ')}</Text>
        </View>
      </View>
      <View style={styles.body}>
        <Text style={styles.label}>Proveedor</Text>
        <Text style={styles.value}>{item.provider?.name ?? 'Sin proveedor'}</Text>
        <Text style={styles.label}>Dirección</Text>
        <Text style={styles.value} numberOfLines={2}>{item.address_line ?? '—'}</Text>
      </View>
      <View style={styles.footer}>
        <Text style={styles.meta}>Actualizado: {item.updated_at ? new Date(item.updated_at).toLocaleString() : '—'}</Text>
      </View>
    </Pressable>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: '#fff',
    gap: 12,
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 2 },
    elevation: 2,
  },
  pressed: {
    opacity: 0.9,
    transform: [{ scale: 0.99 }],
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 12,
  },
  code: {
    flex: 1,
    fontSize: 16,
    fontWeight: '600',
    color: '#1F2937',
  },
  badge: {
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 4,
  },
  badgeText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 12,
    textTransform: 'capitalize',
  },
  body: {
    gap: 4,
  },
  label: {
    fontSize: 12,
    color: '#6B7280',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  value: {
    fontSize: 14,
    color: '#1F2937',
  },
  footer: {
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: '#E5E7EB',
    paddingTop: 8,
  },
  meta: {
    fontSize: 12,
    color: '#9CA3AF',
  },
});
