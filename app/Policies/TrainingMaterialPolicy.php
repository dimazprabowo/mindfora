<?php

namespace App\Policies;

use App\Models\TrainingMaterial;
use App\Models\User;

class TrainingMaterialPolicy
{
    /**
     * Admin/super admin — kelola materi (CRUD).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('training_materials_view');
    }

    public function view(User $user, TrainingMaterial $material): bool
    {
        // Admin bisa lihat semua (termasuk yang belum published)
        if ($user->can('training_materials_view')) {
            return true;
        }

        // User hanya bisa lihat yang published & file ready
        return $user->can('training_materials_view_public') && $material->is_published && $material->is_ready;
    }

    public function create(User $user): bool
    {
        return $user->can('training_materials_create');
    }

    public function update(User $user, TrainingMaterial $material): bool
    {
        return $user->can('training_materials_update');
    }

    public function delete(User $user, TrainingMaterial $material): bool
    {
        return $user->can('training_materials_delete');
    }

    public function togglePublish(User $user, TrainingMaterial $material): bool
    {
        return $user->can('training_materials_update');
    }

    /**
     * User akses library materi pelatihan (public-facing).
     */
    public function viewLibrary(User $user): bool
    {
        return $user->can('training_materials_view_public') || $user->can('training_materials_view');
    }
}
