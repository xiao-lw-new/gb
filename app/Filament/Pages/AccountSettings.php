<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = '账号设置';
    protected static ?string $title = '账号设置';
    protected static ?int $navigationSort = 999;

    protected static string $view = 'filament.pages.account-settings';

    /** @var array<string,mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $admin = auth('admin')->user();
        abort_if(! $admin, 403);

        $this->form->fill([
            'email' => (string) ($admin->email ?? ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('登录信息')
                    ->description('修改登录邮箱或密码。为安全起见，提交时需要验证当前密码。')
                    ->schema([
                        TextInput::make('email')
                            ->label('登录邮箱')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('current_password')
                            ->label('当前密码')
                            ->password()
                            ->maxLength(255)
                            ->helperText('修改邮箱或密码时必须填写。'),

                        TextInput::make('new_password')
                            ->label('新密码')
                            ->password()
                            ->maxLength(255)
                            ->helperText('留空则不修改密码。'),

                        TextInput::make('new_password_confirmation')
                            ->label('确认新密码')
                            ->password()
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $admin = auth('admin')->user();
        abort_if(! $admin, 403);

        $email = (string) ($this->data['email'] ?? '');
        $currentPassword = (string) ($this->data['current_password'] ?? '');
        $newPassword = (string) ($this->data['new_password'] ?? '');
        $newPasswordConfirmation = (string) ($this->data['new_password_confirmation'] ?? '');

        $emailChanged = $email !== (string) ($admin->email ?? '');
        $wantsChangePassword = $newPassword !== '';

        $rules = [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admin_users', 'email')->ignore($admin->getKey()),
            ],
        ];

        if ($wantsChangePassword) {
            $rules['new_password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $payload = [
            'email' => $email,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPasswordConfirmation,
        ];

        validator($payload, $rules)->validate();

        if ($emailChanged || $wantsChangePassword) {
            if ($currentPassword === '' || ! Hash::check($currentPassword, (string) $admin->password)) {
                throw ValidationException::withMessages([
                    'current_password' => '当前密码不正确。',
                ]);
            }
        }

        if ($emailChanged) {
            $admin->email = $email;
        }

        if ($wantsChangePassword) {
            $admin->password = Hash::make($newPassword);
        }

        if ($emailChanged || $wantsChangePassword) {
            $admin->save();
        }

        // Clear password fields
        $this->data['current_password'] = '';
        $this->data['new_password'] = '';
        $this->data['new_password_confirmation'] = '';

        Notification::make()
            ->title('账号信息已更新')
            ->success()
            ->send();
    }
}

