<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class FakeDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Create Users (Admin, Accountants, Promoters)
        $userIds = [];
        $roles = ['admin', 'promoteur', 'comptable'];
        foreach (range(1, 10) as $index) {
            $userId = DB::table('users')->insertGetId([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'created_at' => $faker->dateTimeBetween('-1 year'),
                'updated_at' => now(),
            ]);
            $userIds[] = $userId;

            // Create wallet for each user
            DB::table('wallets')->insert([
                'user_id' => $userId,
                'balance' => $faker->randomFloat(2, 1000, 50000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Employees
        $employeeIds = [];
        foreach (range(1, 20) as $index) {
            $employeeId = DB::table('employees')->insertGetId([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'birthday' => $faker->date('Y-m-d', '-25 years'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $employeeIds[] = $employeeId;
        }

        // Create Clients
        $clientIds = [];
        foreach (range(1, 50) as $index) {
            $clientId = DB::table('clients')->insertGetId([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone' => $faker->phoneNumber,
                'email' => $faker->safeEmail,
                'birthday' => $faker->date(),
                'address' => $faker->address,
                'created_at' => $faker->dateTimeBetween('-1 year'),
                'updated_at' => now(),
            ]);
            $clientIds[] = $clientId;
        }

        // Create Projects
        $projectIds = [];
        foreach (range(1, 15) as $index) {
            $startDate = $faker->dateTimeBetween('-6 months', '+1 month');
            $endDate = $faker->dateTimeBetween($startDate, '+6 months');

            $projectId = DB::table('projects')->insertGetId([
                'name' => $faker->words(3, true) . ' Project',
                'promoter_id' => $faker->randomElement($userIds),
                'accountant_id' => $faker->randomElement($userIds),
                'started_at' => $startDate,
                'ended_at' => $endDate,
                'status' => $faker->randomElement(['pending', 'in_progress', 'completed', 'not_launched','archived']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $projectIds[] = $projectId;

            // Assign employees to projects
            foreach ($faker->randomElements($employeeIds, 3) as $employeeId) {
                DB::table('employee_project')->insert([
                    'employee_id' => $employeeId,
                    'project_id' => $projectId,
                ]);
            }
        }

        // Create Blocks
        $blockIds = [];
        foreach ($projectIds as $projectId) {
            foreach (range(1, 3) as $index) {
                $blockId = DB::table('blocks')->insertGetId([
                    'name' => 'Block ' . $faker->buildingNumber,
                    'project_id' => $projectId,
                    'state' => $faker->randomElement(['pending', 'in_progress', 'completed', 'not_launched','archived']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $blockIds[] = $blockId;
            }
        }

        // Create Promotion Types
        $promotionTypeIds = [];
        $types = ['Apartment', 'Box', 'Villa', 'Studio'];
        foreach ($types as $type) {
            $promotionTypeId = DB::table('promotion_types')->insertGetId([
                'name' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $promotionTypeIds[] = $promotionTypeId;
        }

        // Create Promotions
        $promotionIds = [];
        foreach ($blockIds as $blockId) {
            foreach (range(1, 4) as $index) {
                $promotionId = DB::table('promotions')->insertGetId([
                    'name' => $faker->words(2, true) . ' ' . $faker->randomElement(['Suite', 'Complex', 'Residence']),
                    'promotion_type_id' => $faker->randomElement($promotionTypeIds),
                    'block_id' => $blockId,
                    'state' => $faker->randomElement(['pending', 'in_progress', 'completed', 'not_launched','archived']),
                    'selling_price' => $faker->randomFloat(2, 100000, 1000000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $promotionIds[] = $promotionId;
            }
        }

        // Create Client Promotions
        foreach ($faker->randomElements($clientIds, 30) as $clientId) {
            DB::table('client_promotions')->insert([
                'client_id' => $clientId,
                'promotion_id' => $faker->randomElement($promotionIds),
                'state' => $faker->randomElement(['interested', 'negotiating', 'reserved', 'purchased']),
                'rest' => $faker->randomFloat(2, 0, 100000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Invoices and Invoice Items
        foreach ($projectIds as $projectId) {
            foreach (range(1, 5) as $index) {
                $invoiceId = DB::table('invoices')->insertGetId([
                    'project_id' => $projectId,
                    'promotion_id' => $faker->randomElement($promotionIds),
                    'type' => $faker->randomElement(['project', 'supplier', 'bill']),
                    'amount' => $amount = $faker->randomFloat(2, 1000, 50000),
                    'paid_amount' => $paidAmount = $faker->randomFloat(2, 0, $amount),
                    'status' => $paidAmount >= $amount ? 'paid' : 'not paid',
                    'invoiced_at' => $faker->dateTimeBetween('-6 months'),
                    'comment' => $faker->sentence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create Invoice Items
                foreach (range(1, 3) as $itemIndex) {
                    DB::table('invoice_items')->insert([
                        'name' => $faker->words(3, true),
                        'price' => $faker->randomFloat(2, 100, 5000),
                        'invoice_id' => $invoiceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Create Payments
        foreach ($employeeIds as $employeeId) {
            foreach (range(1, 3) as $index) {
                DB::table('payments')->insert([
                    'employee_id' => $employeeId,
                    'paid_by' => $faker->randomElement($userIds),
                    'project_id' => $faker->randomElement($projectIds),
                    'amount' => $amount = $faker->randomFloat(2, 1000, 10000),
                    'paid_amount' => $faker->randomFloat(2, 0, $amount),
                    'paid_at' => $faker->dateTimeBetween('-3 months'),
                    'description' => $faker->sentence,
                    'status' => $faker->randomElement(['pending', 'paid', 'not paid']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create Transactions
        foreach ($userIds as $userId) {
            $walletId = DB::table('wallets')->where('user_id', $userId)->value('id');
            foreach (range(1, 5) as $index) {
                DB::table('transactions')->insert([
                    'issued_by' => $faker->randomElement($userIds),
                    'amount' => $faker->randomFloat(2, 100, 5000),
                    'wallet_id' => $walletId,
                    'status' => $faker->randomElement(['pending', 'accepted', 'refused']),
                    'created_at' => $faker->dateTimeBetween('-6 months'),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create Suppliers
        foreach (range(1, 10) as $index) {
            DB::table('suppliers')->insert([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone' => $faker->phoneNumber,
                'email' => $faker->companyEmail,
                'address' => $faker->address,
                'trade_registery' => $faker->numerify('TR###-####-####'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Plans
        $planIds = [];
        $planTypes = ['Basic', 'Premium', 'Enterprise'];
        foreach ($planTypes as $type) {
            $planId = DB::table('plans')->insertGetId([
                'name' => json_encode(['en' => $type . ' Plan']),
                'slug' => strtolower($type),
                'description' => json_encode(['en' => $faker->paragraph]),
                'is_active' => true,
                'price' => $faker->randomFloat(2, 50, 500),
                'signup_fee' => $faker->randomFloat(2, 10, 100),
                'currency' => 'USD',
                'trial_period' => 14,
                'trial_interval' => 'day',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'grace_period' => 3,
                'grace_interval' => 'day',
                'sort_order' => $faker->numberBetween(1, 10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $planIds[] = $planId;
        }

        // Create Subscriptions
        foreach ($faker->randomElements($clientIds, 20) as $clientId) {
            $startDate = $faker->dateTimeBetween('-6 months');
            $endDate = $faker->dateTimeBetween($startDate, '+1 year');

            DB::table('subscriptions')->insert([
                'subscriber_type' => 'App\\Models\\Client',
                'subscriber_id' => $clientId,
                'plan_id' => $faker->randomElement($planIds),
                'name' => json_encode(['en' => $faker->words(2, true) . ' Subscription']),
                'slug' => $faker->slug,
                'description' => json_encode(['en' => $faker->sentence]),
                'status' => $faker->randomElement(['active', 'suspended', 'canceled']),
                'starts_at' => $startDate,
                'ends_at' => $endDate,
                'trial_ends_at' => Carbon::parse($startDate)->addDays(14),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
