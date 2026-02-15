<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Membership extends Model
{
    use HasFactory;

    public $timestamps = true;

    public const UPDATED_AT = null;

    /**
     * Lokasi file konfigurasi pricing membership.
     */
    private const PRICING_RELATIVE_PATH = 'app/public/membership_pricing.json';

    protected $fillable = [
        'member_id',
        'start_date',
        'end_date',
        'duration_months',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Mengembalikan seluruh konfigurasi pricing membership dari file JSON.
     */
    public static function getPricingConfig(): array
    {
        $config = static::readPricingFile();

        $membershipPackages = $config['membership_packages'] ?? [];
        $fees = $config['fees'] ?? [];

        if (! is_array($membershipPackages)) {
            $membershipPackages = [];
        }

        if (! is_array($fees)) {
            $fees = [];
        }

        return [
            'membership_packages' => $membershipPackages,
            'fees' => $fees,
        ];
    }

    /**
     * Mengembalikan daftar paket membership yang sudah dinormalisasi.
     *
     * Struktur hasil:
     * [
     *   'package_key' => [
     *       'key' => 'package_key',
     *       'label' => 'Label Paket',
     *       'price' => int,
     *       'discount_percent' => int, // 0-100
     *       'duration_days' => int,    // > 0
     *   ],
     * ]
     */
    public static function getMembershipPackages(): array
    {
        $config = static::getPricingConfig();
        $rawPackages = $config['membership_packages'] ?? [];

        $packages = [];

        foreach ($rawPackages as $key => $package) {
            if (! is_array($package)) {
                continue;
            }

            if (! array_key_exists('price', $package) || ! array_key_exists('duration_days', $package)) {
                continue;
            }

            $price = (int) $package['price'];
            $durationDays = (int) $package['duration_days'];
            $discount = isset($package['discount_percent']) ? (int) $package['discount_percent'] : 0;
            $label = isset($package['label']) && is_string($package['label'])
                ? $package['label']
                : (string) $key;

            if ($price < 0 || $durationDays <= 0) {
                continue;
            }

            if ($discount < 0) {
                $discount = 0;
            } elseif ($discount > 100) {
                $discount = 100;
            }

            $packages[(string) $key] = [
                'key' => (string) $key,
                'label' => $label,
                'price' => $price,
                'discount_percent' => $discount,
                'duration_days' => $durationDays,
            ];
        }

        return $packages;
    }

    /**
     * Mengembalikan satu paket membership berdasarkan key.
     */
    public static function getPackage(string $packageKey): ?array
    {
        $packages = static::getMembershipPackages();

        return $packages[$packageKey] ?? null;
    }

    /**
     * Mengembalikan seluruh biaya non-package seperti registration dan non-member fee.
     *
     * Struktur hasil:
     * [
     *   'new_member_fee' => int,
     *   'non_member_visit_daily' => int,
     *   ...
     * ]
     */
    public static function getFees(): array
    {
        $config = static::getPricingConfig();
        $rawFees = $config['fees'] ?? [];

        $fees = [];

        foreach ($rawFees as $key => $value) {
            if (! is_numeric($value)) {
                continue;
            }

            $amount = (int) $value;

            if ($amount < 0) {
                continue;
            }

            $fees[(string) $key] = $amount;
        }

        return $fees;
    }

    /**
     * Menyimpan konfigurasi pricing ke file JSON secara aman (atomic write).
     */
    public static function savePricingConfig(array $config): void
    {
        $normalized = [
            'membership_packages' => $config['membership_packages'] ?? [],
            'fees' => $config['fees'] ?? [],
        ];

        static::writePricingFile($normalized);
    }

    private static function getPricingFilePath(): string
    {
        return storage_path(self::PRICING_RELATIVE_PATH);
    }

    /**
     * Membaca file pricing JSON dan mengembalikannya sebagai array mentah.
     */
    private static function readPricingFile(): array
    {
        $path = static::getPricingFilePath();

        if (! file_exists($path)) {
            return [
                'membership_packages' => [],
                'fees' => [],
            ];
        }

        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            return [
                'membership_packages' => [],
                'fees' => [],
            ];
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('Gagal membaca konfigurasi membership_pricing.json', 0, $exception);
        }

        if (! is_array($decoded)) {
            return [
                'membership_packages' => [],
                'fees' => [],
            ];
        }

        return $decoded;
    }

    /**
     * Menulis konfigurasi pricing ke file JSON dengan mekanisme atomic write.
     */
    private static function writePricingFile(array $config): void
    {
        $path = static::getPricingFilePath();
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException(sprintf('Gagal membuat direktori untuk pricing: %s', $directory));
        }

        $encoded = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            throw new RuntimeException('Gagal meng-encode konfigurasi pricing membership ke JSON.');
        }

        $tempPath = $path . '.tmp';
        $handle = fopen($tempPath, 'wb');

        if ($handle === false) {
            throw new RuntimeException(sprintf('Gagal membuka file sementara untuk pricing: %s', $tempPath));
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new RuntimeException('Gagal mengunci file sementara pricing untuk penulisan.');
            }

            if (fwrite($handle, $encoded) === false) {
                throw new RuntimeException('Gagal menulis konfigurasi pricing membership ke file sementara.');
            }

            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        if (! rename($tempPath, $path)) {
            throw new RuntimeException('Gagal memindahkan file konfigurasi pricing membership ke lokasi final.');
        }
    }
}
