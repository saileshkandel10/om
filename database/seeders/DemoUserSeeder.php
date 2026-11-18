<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Pharmacy;
use App\Models\RewardWallet;
use App\Models\Role;
use App\Models\ShareholderAccount;
use App\Models\ShareholderMaster;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password123!');

        // Ensure roles exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $pharmacyRole = Role::firstOrCreate(['name' => 'pharmacy']);
        $shareholderRole = Role::firstOrCreate(['name' => 'shareholder']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // 1. Admin Demo User
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@omconnect.com'],
            [
                'name' => 'OM Connect Admin',
                'password' => $password,
                'mobile' => '9800000000',
                'status' => 'active',
            ]
        );
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. Doctor Demo User
        $doctorUser = User::updateOrCreate(
            ['email' => 'doctor@omconnect.com'],
            [
                'name' => 'Dr. Sunita Basnet',
                'password' => $password,
                'mobile' => '9801122334',
                'status' => 'active',
            ]
        );
        $doctorUser->roles()->syncWithoutDetaching([$doctorRole->id]);

        Doctor::updateOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'registration_no' => 'NMC-7788',
                'specialty' => 'Internal Medicine & Cardiology',
                'hospital_clinic' => 'OM Hospital & Research Center, Chabahil',
                'status' => 'active',
                'approved_at' => now(),
            ]
        );

        RewardWallet::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'total_points' => 800.00,
                'total_bonus' => 100.00,
                'total_royalty' => 0.00,
            ]
        );

        // 3. Pharmacy Demo User
        $pharmacyUser = User::updateOrCreate(
            ['email' => 'pharmacy@omconnect.com'],
            [
                'name' => 'Kathmandu Central Pharmacy',
                'password' => $password,
                'mobile' => '9841987654',
                'status' => 'active',
            ]
        );
        $pharmacyUser->roles()->syncWithoutDetaching([$pharmacyRole->id]);

        Pharmacy::updateOrCreate(
            ['user_id' => $pharmacyUser->id],
            [
                'store_name' => 'Kathmandu Central Pharmacy & Surgicals',
                'pan_vat_no' => '300123456',
                'license_no' => 'DDA-KTM-2024-991',
                'owner_name' => 'Ramesh Khadka',
                'district' => 'Kathmandu',
                'address' => 'Tripureshwor, Kathmandu, Nepal',
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );

        RewardWallet::firstOrCreate(
            ['user_id' => $pharmacyUser->id],
            [
                'total_points' => 1250.00,
                'total_bonus' => 200.00,
                'total_royalty' => 450.00,
            ]
        );

        // 4. Shareholder Demo User
        $shareholderUser = User::updateOrCreate(
            ['email' => 'shareholder@omconnect.com'],
            [
                'name' => 'Aarav Sharma (Shareholder)',
                'password' => $password,
                'mobile' => '9851012345',
                'status' => 'active',
            ]
        );
        $shareholderUser->roles()->syncWithoutDetaching([$shareholderRole->id]);

        $master = ShareholderMaster::firstOrCreate(
            ['boid' => '1301000000001234'],
            [
                'shareholder_name' => 'Aarav Sharma',
                'mobile' => '9851012345',
                'email' => 'shareholder@omconnect.com',
                'status' => 'active',
            ]
        );

        ShareholderAccount::updateOrCreate(
            ['user_id' => $shareholderUser->id],
            [
                'shareholder_master_id' => $master->id,
                'boid' => $master->boid,
                'shares_owned' => 500,
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]
        );

        RewardWallet::firstOrCreate(
            ['user_id' => $shareholderUser->id],
            [
                'total_points' => 350.00,
                'total_bonus' => 50.00,
                'total_royalty' => 120.00,
            ]
        );

        // 5. Customer / Patient Demo User
        $customerUser = User::updateOrCreate(
            ['email' => 'customer@omconnect.com'],
            [
                'name' => 'Sita Maya Tamang',
                'password' => $password,
                'mobile' => '9812345678',
                'status' => 'active',
            ]
        );
        $customerUser->roles()->syncWithoutDetaching([$customerRole->id]);

        UserProfile::updateOrCreate(
            ['user_id' => $customerUser->id],
            [
                'dob' => '1995-04-12',
                'gender' => 'female',
                'district' => 'Kathmandu',
                'province' => 'Bagmati Province',
                'address' => 'Baneshwor, Kathmandu',
            ]
        );

        RewardWallet::firstOrCreate(
            ['user_id' => $customerUser->id],
            [
                'total_points' => 180.00,
                'total_bonus' => 25.00,
                'total_royalty' => 0.00,
            ]
        );
    }
}
