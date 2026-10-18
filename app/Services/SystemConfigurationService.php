<?php

namespace App\Services;

use App\Enums\ConfigCategory;
use App\Enums\ConfigDataType;
use App\Models\SystemConfiguration;
use App\Traits\HasDynamicLike;
use App\Traits\SortsQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class SystemConfigurationService
{
    use HasDynamicLike, SortsQuery;

    public function getFiltered(
        ?string $search = null,
        ?string $isActive = null,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = SystemConfiguration::query();

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('key', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%")
                    ->orWhere('value', $operator, "%{$search}%");
            });
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', $isActive === '1');
        }

        $sortable = [
            'key' => 'system_configurations.key',
            'category' => 'CASE system_configurations.category '
                .collect(ConfigCategory::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(ConfigCategory::cases()).' END',
            'value' => 'system_configurations.value',
            'data_type' => 'CASE system_configurations.data_type '
                .collect(ConfigDataType::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(ConfigDataType::cases()).' END',
            'is_active' => 'system_configurations.is_active',
        ];

        $this->applySorting($query, $sortable, $sortField, $sortDir, 'category', 'asc', 'system_configurations.key', 'asc');

        return $query->paginate($perPage);
    }

    public function update(SystemConfiguration $config, array $data): SystemConfiguration
    {
        $config->update($data);

        $this->applyRuntimeConfig($config->key, $config->value, $config->is_active);

        return $config;
    }

    public function toggleActive(SystemConfiguration $config): SystemConfiguration
    {
        $config->update(['is_active' => ! $config->is_active]);

        $this->applyRuntimeConfig($config->key, $config->value, $config->is_active);

        return $config;
    }

    /**
     * Apply config change to Laravel runtime if it maps to a Laravel config key.
     */
    public function applyRuntimeConfig(string $key, mixed $value, bool $isActive): void
    {
        $configMap = [
            'app.name' => 'app.name',
            'app.timezone' => 'app.timezone',
        ];

        if (isset($configMap[$key]) && $isActive) {
            config([$configMap[$key] => $value]);

            if ($key === 'app.timezone') {
                date_default_timezone_set($value);
            }
        }
    }
}
