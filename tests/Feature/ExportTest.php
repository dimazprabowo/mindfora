<?php

namespace Tests\Feature;

use App\Livewire\MasterData\CompanyManagement;
use App\Livewire\MasterData\DepartmentManagement;
use App\Livewire\Programs\TestProgramIndex;
use App\Livewire\Settings\RolePermission;
use App\Livewire\Settings\SystemConfiguration;
use App\Livewire\Settings\UserManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public static function exportComponents(): array
    {
        return [
            'perusahaan' => [CompanyManagement::class, 'companies_view'],
            'departemen' => [DepartmentManagement::class, 'departments_view'],
            'user' => [UserManagement::class, 'users_view'],
            'konfigurasi' => [SystemConfiguration::class, 'configuration_view'],
            'role' => [RolePermission::class, 'roles_view'],
            'program_test' => [TestProgramIndex::class, 'test_programs_view'],
        ];
    }

    #[DataProvider('exportComponents')]
    public function test_export_excel_dan_pdf_menghasilkan_file(string $component, string $viewPermission): void
    {
        $this->seed();
        $exporter = User::factory()->create();
        $exporter->givePermissionTo([
            $viewPermission,
            str_replace('_view', '_export_excel', $viewPermission),
            str_replace('_view', '_export_pdf', $viewPermission),
        ]);
        $this->actingAs($exporter);

        Livewire::test($component)->call('exportExcel')->assertFileDownloaded();
        Livewire::test($component)->call('exportPdf')->assertFileDownloaded();
    }

    #[DataProvider('exportComponents')]
    public function test_export_ditolak_tanpa_izin_export(string $component, string $viewPermission): void
    {
        $this->seed();
        $viewer = User::factory()->create();
        $viewer->givePermissionTo($viewPermission);
        $this->actingAs($viewer);

        Livewire::test($component)->call('exportExcel')->assertForbidden();
        Livewire::test($component)->call('exportPdf')->assertForbidden();
    }
}
