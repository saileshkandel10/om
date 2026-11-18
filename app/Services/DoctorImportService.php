<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Throwable;

class DoctorImportService
{
    public function import(string $filePath, array $options = []): array
    {
        $onDuplicate = $options['on_duplicate'] ?? 'skip';
        $defaultStatus = $options['default_status'] ?? 'active';

        $stats = [
            'total_rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'duplicates_skipped' => 0,
            'invalid_skipped' => 0,
            'errors' => [],
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            $reader = new CsvReader;
        } else {
            $reader = new XlsxReader;
        }

        try {
            $reader->open($filePath);
        } catch (Throwable $e) {
            Log::error('Doctor import reader open failed: '.$e->getMessage());
            if ($extension !== 'csv') {
                try {
                    $reader = new CsvReader;
                    $reader->open($filePath);
                } catch (Throwable $fallbackError) {
                    $stats['errors'][] = 'Unable to read file: '.$e->getMessage();

                    return $stats;
                }
            } else {
                $stats['errors'][] = 'Unable to read file: '.$e->getMessage();

                return $stats;
            }
        }

        $headerMap = null;
        $batch = [];
        $batchSize = 500;
        $seenRegsInFile = [];
        $rowNumber = 0;
        $now = Carbon::now();

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    $cells = $row->toArray();

                    if ($this->isRowEmpty($cells)) {
                        continue;
                    }

                    if ($headerMap === null) {
                        $headerMap = $this->mapHeaders($cells);

                        continue;
                    }

                    $stats['total_rows']++;

                    $parsed = $this->parseRow($cells, $headerMap, $defaultStatus, $now);

                    if (! $parsed['valid']) {
                        $stats['invalid_skipped']++;
                        if (count($stats['errors']) < 10) {
                            $stats['errors'][] = "Row {$rowNumber}: {$parsed['reason']}";
                        }

                        continue;
                    }

                    $regNo = $parsed['data']['registration_no'];

                    if (isset($seenRegsInFile[$regNo])) {
                        $stats['duplicates_skipped']++;

                        continue;
                    }

                    $seenRegsInFile[$regNo] = true;
                    $batch[] = $parsed['data'];

                    if (count($batch) >= $batchSize) {
                        $this->processBatch($batch, $onDuplicate, $stats, $now);
                        $batch = [];
                    }
                }
                break;
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $onDuplicate, $stats, $now);
                $batch = [];
            }
        } catch (Throwable $e) {
            Log::error('Error processing doctor import: '.$e->getMessage());
            $stats['errors'][] = 'An error occurred during doctor import: '.$e->getMessage();
        } finally {
            $reader->close();
        }

        return $stats;
    }

    protected function processBatch(array $batch, string $onDuplicate, array &$stats, Carbon $now): void
    {
        if (empty($batch)) {
            return;
        }

        $regNos = array_column($batch, 'registration_no');
        $emails = array_filter(array_column($batch, 'email'));

        $existingDoctors = Doctor::with('user')
            ->whereIn('registration_no', $regNos)
            ->get()
            ->keyBy('registration_no');

        $existingUsersByEmail = User::whereIn('email', $emails)->get()->keyBy('email');
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);

        DB::beginTransaction();
        try {
            foreach ($batch as $record) {
                $regNo = $record['registration_no'];

                if ($existingDoctors->has($regNo)) {
                    if ($onDuplicate === 'update') {
                        $doctor = $existingDoctors->get($regNo);
                        $doctor->update([
                            'specialty' => $record['specialty'] ?? $doctor->specialty,
                            'hospital_clinic' => $record['hospital_clinic'] ?? $doctor->hospital_clinic,
                            'status' => $record['status'] ?? $doctor->status,
                            'approved_at' => ($record['status'] === 'active' || $record['status'] === 'approved') ? ($doctor->approved_at ?? $now) : null,
                        ]);

                        if ($doctor->user) {
                            $userUpdate = ['name' => $record['name']];
                            if (! empty($record['mobile'])) {
                                $userUpdate['mobile'] = $record['mobile'];
                            }
                            if (! empty($record['email'])) {
                                $userUpdate['email'] = $record['email'];
                            }
                            $doctor->user->update($userUpdate);
                        }

                        $stats['updated']++;
                    } else {
                        $stats['duplicates_skipped']++;
                    }
                } else {
                    $email = $record['email'];
                    $user = $email && $existingUsersByEmail->has($email)
                        ? $existingUsersByEmail->get($email)
                        : null;

                    if (! $user) {
                        if (empty($email)) {
                            $safeReg = preg_replace('/[^a-zA-Z0-9]/', '', $regNo);
                            $email = 'dr.'.strtolower($safeReg).'@omconnect.com';
                        }

                        if (User::where('email', $email)->exists()) {
                            $email = 'dr.'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $regNo)).'.'.Str::random(4).'@omconnect.com';
                        }

                        $user = User::create([
                            'name' => $record['name'],
                            'email' => $email,
                            'mobile' => $record['mobile'] ?? null,
                            'password' => Hash::make('Doctor@123'),
                            'status' => 'active',
                        ]);
                    }

                    $user->roles()->syncWithoutDetaching([$doctorRole->id]);

                    Doctor::create([
                        'user_id' => $user->id,
                        'registration_no' => $regNo,
                        'specialty' => $record['specialty'] ?? null,
                        'hospital_clinic' => $record['hospital_clinic'] ?? null,
                        'status' => $record['status'],
                        'approved_at' => ($record['status'] === 'active' || $record['status'] === 'approved') ? $now : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $stats['imported']++;
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Batch insert failed for doctors: '.$e->getMessage());
            $stats['errors'][] = 'Batch database error: '.$e->getMessage();
        }
    }

    protected function mapHeaders(array $headerRow): array
    {
        $map = [
            'registration_no' => null,
            'name' => null,
            'specialty' => null,
            'hospital_clinic' => null,
            'mobile' => null,
            'email' => null,
            'status' => null,
        ];

        $aliases = [
            'registration_no' => [
                'registrationno', 'registrationnumber', 'regno', 'regnumber',
                'nmcno', 'nmcnumber', 'license', 'licenseno', 'licensenumber',
                'doctorid', 'docid', 'councilno', 'reg',
            ],
            'name' => [
                'doctorname', 'name', 'fullname', 'doctor', 'physicianname',
                'practitionername', 'drname',
            ],
            'specialty' => [
                'specialty', 'speciality', 'specialization', 'department',
                'field', 'discipline',
            ],
            'hospital_clinic' => [
                'hospitalclinic', 'hospital', 'clinic', 'hospitalname',
                'clinicname', 'workplace', 'institution', 'affiliation',
                'organization', 'workplacehospital',
            ],
            'mobile' => [
                'mobile', 'mobileno', 'mobilenumber', 'phone', 'phoneno',
                'phonenumber', 'contact', 'contactno', 'cell', 'telephone',
            ],
            'email' => [
                'email', 'emailaddress', 'mail', 'emailid',
            ],
            'status' => [
                'status', 'accountstatus', 'doctorstatus', 'state',
            ],
        ];

        foreach ($headerRow as $index => $header) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $header));
            if ($normalized === '') {
                continue;
            }

            foreach ($aliases as $field => $aliasList) {
                if ($map[$field] === null && in_array($normalized, $aliasList, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        if ($map['registration_no'] === null && isset($headerRow[0])) {
            $map['registration_no'] = 0;
        }
        if ($map['name'] === null && isset($headerRow[1])) {
            $map['name'] = 1;
        }
        if ($map['specialty'] === null && isset($headerRow[2])) {
            $map['specialty'] = 2;
        }
        if ($map['hospital_clinic'] === null && isset($headerRow[3])) {
            $map['hospital_clinic'] = 3;
        }
        if ($map['mobile'] === null && isset($headerRow[4])) {
            $map['mobile'] = 4;
        }
        if ($map['email'] === null && isset($headerRow[5])) {
            $map['email'] = 5;
        }
        if ($map['status'] === null && isset($headerRow[6])) {
            $map['status'] = 6;
        }

        return $map;
    }

    protected function parseRow(array $cells, array $map, string $defaultStatus, Carbon $now): array
    {
        $rawRegNo = $map['registration_no'] !== null && isset($cells[$map['registration_no']]) ? $cells[$map['registration_no']] : null;
        $rawName = $map['name'] !== null && isset($cells[$map['name']]) ? $cells[$map['name']] : null;
        $rawSpecialty = $map['specialty'] !== null && isset($cells[$map['specialty']]) ? $cells[$map['specialty']] : null;
        $rawHospital = $map['hospital_clinic'] !== null && isset($cells[$map['hospital_clinic']]) ? $cells[$map['hospital_clinic']] : null;
        $rawMobile = $map['mobile'] !== null && isset($cells[$map['mobile']]) ? $cells[$map['mobile']] : null;
        $rawEmail = $map['email'] !== null && isset($cells[$map['email']]) ? $cells[$map['email']] : null;
        $rawStatus = $map['status'] !== null && isset($cells[$map['status']]) ? $cells[$map['status']] : null;

        $regNo = $this->formatScalarValue($rawRegNo);
        $name = $this->formatScalarValue($rawName);
        $specialty = $this->formatScalarValue($rawSpecialty);
        $hospital = $this->formatScalarValue($rawHospital);
        $mobile = $this->formatScalarValue($rawMobile);
        $email = strtolower($this->formatScalarValue($rawEmail));
        $status = strtolower($this->formatScalarValue($rawStatus) ?: $defaultStatus);

        if ($regNo === '') {
            return ['valid' => false, 'data' => [], 'reason' => 'Missing registration / NMC number'];
        }

        if ($name === '') {
            return ['valid' => false, 'data' => [], 'reason' => 'Missing doctor name'];
        }

        if (! in_array($status, ['active', 'inactive', 'suspended', 'pending'], true)) {
            $status = $defaultStatus;
        }

        return [
            'valid' => true,
            'data' => [
                'registration_no' => $regNo,
                'name' => $name,
                'specialty' => $specialty ?: 'General Physician',
                'hospital_clinic' => $hospital ?: null,
                'mobile' => $mobile ?: null,
                'email' => $email ?: null,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }

    protected function isRowEmpty(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function formatScalarValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value)) {
            return number_format($value, 0, '', '');
        }

        return trim((string) $value);
    }

    public function generateSampleFile(string $filePath, string $format = 'xlsx'): void
    {
        if ($format === 'csv') {
            $writer = new CsvWriter;
        } else {
            $writer = new XlsxWriter;
        }

        $writer->openToFile($filePath);

        $writer->addRow(Row::fromValues([
            'Registration No',
            'Doctor Name',
            'Specialty',
            'Hospital / Clinic',
            'Mobile',
            'Email',
            'Status',
        ]));

        $writer->addRow(Row::fromValues([
            'NMC-10842',
            'Dr. Anil Sharma',
            'Cardiology',
            'Om Hospital & Research Center, Kathmandu',
            '9841234560',
            'dr.anil.sharma@example.com',
            'active',
        ]));

        $writer->addRow(Row::fromValues([
            'NMC-12948',
            'Dr. Sunita Karki',
            'Gastroenterology',
            'Norvic International Hospital, Kathmandu',
            '9851234561',
            'dr.sunita.karki@example.com',
            'active',
        ]));

        $writer->close();
    }
}
