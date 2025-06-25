<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            // Credit Card
            [
                'code' => 'CREDIT_CARD',
                'name' => 'Credit Card',
                'type' => 'credit_card',
                'description' => 'Pay with credit card',
                'fee_percentage' => 2.9,
                'fee_fixed' => 2000,
                'minimum_fee' => 2000,
                'maximum_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // Banks
            [
                'code' => 'BCA',
                'name' => 'Bank BCA',
                'type' => 'bank_transfer',
                'description' => 'Transfer via Bank BCA',
                'fee_percentage' => 0,
                'fee_fixed' => 4000,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'BNI',
                'name' => 'Bank BNI',
                'type' => 'bank_transfer',
                'description' => 'Transfer via Bank BNI',
                'fee_percentage' => 0,
                'fee_fixed' => 4000,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'BRI',
                'name' => 'Bank BRI',
                'type' => 'bank_transfer',
                'description' => 'Transfer via Bank BRI',
                'fee_percentage' => 0,
                'fee_fixed' => 4000,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'MANDIRI',
                'name' => 'Bank Mandiri',
                'type' => 'bank_transfer',
                'description' => 'Transfer via Bank Mandiri',
                'fee_percentage' => 0,
                'fee_fixed' => 4000,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'BSI',
                'name' => 'Bank Syariah Indonesia',
                'type' => 'bank_transfer',
                'description' => 'Transfer via Bank Syariah Indonesia',
                'fee_percentage' => 0,
                'fee_fixed' => 4000,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'code' => 'PERMATA',
                'name' => 'Bank Permata',
                'type' => 'bank_transfer',
                'description' => 'Transfer via Bank Permata',
                'fee_percentage' => 0,
                'fee_fixed' => 4000,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'sort_order' => 7,
            ],
            // E-Wallets
            [
                'code' => 'OVO',
                'name' => 'OVO',
                'type' => 'ewallet',
                'description' => 'Pay with OVO',
                'fee_percentage' => 2,
                'fee_fixed' => 0,
                'minimum_fee' => 1000,
                'maximum_fee' => 10000,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'type' => 'ewallet',
                'description' => 'Pay with DANA',
                'fee_percentage' => 2,
                'fee_fixed' => 0,
                'minimum_fee' => 1000,
                'maximum_fee' => 10000,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'description' => 'Pay with ShopeePay',
                'fee_percentage' => 2,
                'fee_fixed' => 0,
                'minimum_fee' => 1000,
                'maximum_fee' => 10000,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'LINKAJA',
                'name' => 'LinkAja',
                'type' => 'ewallet',
                'description' => 'Pay with LinkAja',
                'fee_percentage' => 2,
                'fee_fixed' => 0,
                'minimum_fee' => 1000,
                'maximum_fee' => 10000,
                'is_active' => true,
                'sort_order' => 11,
            ],
            // QRIS
            [
                'code' => 'QRIS',
                'name' => 'QRIS',
                'type' => 'qris',
                'description' => 'Pay with QRIS',
                'fee_percentage' => 0.7,
                'fee_fixed' => 0,
                'minimum_fee' => 500,
                'maximum_fee' => 5000,
                'is_active' => true,
                'sort_order' => 12,
            ],
            // Retail
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'type' => 'retail',
                'description' => 'Pay at Alfamart',
                'fee_percentage' => 0,
                'fee_fixed' => 2500,
                'minimum_fee' => 2500,
                'maximum_fee' => 2500,
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'type' => 'retail',
                'description' => 'Pay at Indomaret',
                'fee_percentage' => 0,
                'fee_fixed' => 2500,
                'minimum_fee' => 2500,
                'maximum_fee' => 2500,
                'is_active' => true,
                'sort_order' => 14,
            ],
        ];

        foreach ($paymentMethods as $method) {
            DB::table('payment_methods')->insert(array_merge($method, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
