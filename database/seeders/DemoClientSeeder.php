<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Courier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'GiGi Logistics',
                'cif' => 'B12345678',
                'contact_name' => 'Laura Gómez',
                'contact_email' => 'contacto@gigi-logistics.test',
                'contact_phone' => '+34 600 000 001',
                'admin' => [
                    'name' => 'Laura Gómez',
                    'email' => 'laura.admin@gigi-logistics.test',
                    'phone' => '+34 600 000 011',
                    'password' => 'password',
                ],
                'zones' => [
                    ['name' => 'Girona', 'code' => 'GRO'],
                    ['name' => 'Figueres', 'code' => 'FIG'],
                ],
                'providers' => [
                    [
                        'name' => 'CTT',
                        'slug' => 'ctt',
                        'barcodes' => [
                            [
                                'label' => 'CTT Bulk',
                                'pattern_regex' => '^(?:00)?(?:79\\d{20}|8290\\d{19}|8280\\d{19})\\d{2,3}$',
                                'sample_code' => '7900123456789012345678',
                                'priority' => 10,
                            ],
                        ],
                    ],
                    [
                        'name' => 'SEUR',
                        'slug' => 'seur',
                        'barcodes' => [
                            [
                                'label' => 'SEUR Default',
                                'pattern_regex' => '^[A-Z0-9]{8,30}$',
                                'sample_code' => 'SEUR12345678',
                                'priority' => 20,
                            ],
                        ],
                    ],
                    [
                        'name' => 'GLS',
                        'slug' => 'gls',
                        'barcodes' => [
                            [
                                'label' => 'GLS Default',
                                'pattern_regex' => '^\\d{8,20}$',
                                'sample_code' => '0123456789',
                                'priority' => 30,
                            ],
                        ],
                    ],
                ],
                'couriers' => [
                    [
                        'user' => [
                            'name' => 'Miguel Riera',
                            'email' => 'miguel.riera@gigi-logistics.test',
                            'phone' => '+34 600 000 101',
                            'password' => 'password',
                        ],
                        'vehicle_type' => 'van',
                        'external_code' => 'GIGI-CR-001',
                    ],
                ],
            ],
            [
                'name' => 'Paack Demo Client',
                'cif' => 'B87654321',
                'contact_name' => 'Carlos Ruiz',
                'contact_email' => 'contacto@paack-demo.test',
                'contact_phone' => '+34 600 000 002',
                'admin' => [
                    'name' => 'Carlos Ruiz',
                    'email' => 'carlos.admin@paack-demo.test',
                    'phone' => '+34 600 000 022',
                    'password' => 'password',
                ],
                'zones' => [
                    ['name' => 'Barcelona', 'code' => 'BCN'],
                    ['name' => 'Tarragona', 'code' => 'TGN'],
                ],
                'providers' => [
                    [
                        'name' => 'Correos Express',
                        'slug' => 'correos-express',
                        'barcodes' => [
                            [
                                'label' => 'Correos Express',
                                'pattern_regex' => '^[A-Z0-9]{10,25}$',
                                'priority' => 40,
                            ],
                        ],
                    ],
                ],
                'couriers' => [
                    [
                        'user' => [
                            'name' => 'Ana Martín',
                            'email' => 'ana.martin@paack-demo.test',
                            'phone' => '+34 600 000 102',
                            'password' => 'password',
                        ],
                        'vehicle_type' => 'moto',
                        'external_code' => 'PAACK-CR-001',
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($clients) {
            foreach ($clients as $clientData) {
                $client = Client::updateOrCreate(
                    ['name' => $clientData['name']],
                    Arr::only($clientData, [
                        'cif',
                        'contact_name',
                        'contact_email',
                        'contact_phone',
                    ]) + ['active' => true]
                );

                $adminData = $clientData['admin'];
                $admin = User::firstOrNew(['email' => $adminData['email']]);
                $admin->fill([
                    'name' => $adminData['name'],
                    'phone' => $adminData['phone'] ?? null,
                    'client_id' => $client->id,
                    'is_active' => true,
                ]);

                if (! $admin->exists || ! $admin->password || Hash::needsRehash($admin->password)) {
                    $admin->password = Hash::make($adminData['password']);
                }

                $admin->email_verified_at = now();
                $admin->save();
                $admin->assignRole('client_admin');

                foreach ($clientData['zones'] as $zoneData) {
                    $client->zones()->updateOrCreate(
                        ['name' => $zoneData['name']],
                        [
                            'code' => $zoneData['code'] ?? null,
                            'notes' => $zoneData['notes'] ?? null,
                            'active' => $zoneData['active'] ?? true,
                        ]
                    );
                }

                foreach ($clientData['providers'] as $providerData) {
                    $slug = $providerData['slug'] ?? Str::slug($providerData['name']);
                    $provider = $client->providers()->updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $providerData['name'],
                            'notes' => $providerData['notes'] ?? null,
                            'active' => $providerData['active'] ?? true,
                        ]
                    );

                    foreach ($providerData['barcodes'] ?? [] as $barcodeData) {
                        $provider->barcodes()->updateOrCreate(
                            ['label' => $barcodeData['label']],
                            [
                                'pattern_regex' => $barcodeData['pattern_regex'],
                                'sample_code' => $barcodeData['sample_code'] ?? null,
                                'priority' => $barcodeData['priority'] ?? 100,
                                'active' => $barcodeData['active'] ?? true,
                            ]
                        );
                    }
                }

                foreach ($clientData['couriers'] as $courierData) {
                    $courierUserData = $courierData['user'];
                    $courierUser = User::firstOrNew(['email' => $courierUserData['email']]);
                    $courierUser->fill([
                        'name' => $courierUserData['name'],
                        'phone' => $courierUserData['phone'] ?? null,
                        'client_id' => $client->id,
                        'is_active' => true,
                    ]);

                    if (! $courierUser->exists || ! $courierUser->password || Hash::needsRehash($courierUser->password)) {
                        $courierUser->password = Hash::make($courierUserData['password']);
                    }

                    $courierUser->email_verified_at = now();
                    $courierUser->save();
                    $courierUser->assignRole('courier');

                    $client->couriers()->updateOrCreate(
                        ['user_id' => $courierUser->id],
                        [
                            'vehicle_type' => $courierData['vehicle_type'] ?? Courier::VEHICLE_TYPES[0],
                            'external_code' => $courierData['external_code'] ?? null,
                            'active' => $courierData['active'] ?? true,
                        ]
                    );
                }
            }
        });
    }
}
