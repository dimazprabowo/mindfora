<?php

namespace App\Livewire\Pages\Auth;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Helpers\ConfigHelper;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Component;

class Register extends Component
{
    // Account data
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Profile data (optional)
    public ?string $phone = '';

    public ?string $gender = '';

    public ?string $birth_date = '';

    public ?string $education_level = '';

    public ?string $department_id = '';

    public ?string $position = '';

    public ?string $work_started_at = '';

    public ?string $company_id = '';

    public function mount(): void
    {
        // Pre-populate nothing; fields are optional and empty by default
    }

    public function register(): void
    {
        if (! ConfigHelper::isRegistrationOpen()) {
            $this->dispatch('notify',
                type: 'error',
                message: ConfigHelper::getRegistrationClosedMessage()
            );
            $this->redirect(route('login'), navigate: true);

            return;
        }

        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],

                // Profile (optional)
                'phone' => ['nullable', 'string', 'max:20'],
                'gender' => ['nullable', 'string', 'in:'.implode(',', Gender::values())],
                'birth_date' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
                'education_level' => ['nullable', 'string', 'in:'.implode(',', EducationLevel::values())],
                'department_id' => ['nullable', 'exists:departments,id'],
                'position' => ['nullable', 'string', 'max:255'],
                'work_started_at' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
                'company_id' => ['nullable', 'exists:companies,id'],
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $validated['locale'] = app()->getLocale();

            // Normalize nullable profile fields: empty string -> null
            foreach (['phone', 'gender', 'birth_date', 'education_level', 'department_id', 'position', 'work_started_at', 'company_id'] as $field) {
                if (filled($validated[$field] ?? null) === false) {
                    $validated[$field] = null;
                }
            }

            event(new Registered($user = User::create($validated)));

            // Assign default role 'user' to newly registered user
            $user->assignRole('user');

            Auth::login($user);

            $this->dispatch('notify',
                type: 'success',
                message: 'Pendaftaran berhasil! Email verifikasi telah dikirim.'
            );

            $this->redirect(route('dashboard', absolute: false), navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'
            );
        }
    }

    public function with(): array
    {
        return [
            'companies' => Company::active()->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'genderOptions' => Gender::options(),
            'educationOptions' => EducationLevel::options(),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('auth.full_name'),
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => __('auth.confirm_password'),
            'phone' => __('auth.phone'),
            'gender' => __('auth.gender'),
            'birth_date' => __('auth.birth_date'),
            'education_level' => __('auth.education_level'),
            'department_id' => __('auth.department'),
            'position' => __('auth.position'),
            'work_started_at' => __('auth.work_started_at'),
            'company_id' => __('auth.company'),
        ];
    }

    public function render()
    {
        return view('livewire.pages.auth.register');
    }
}
