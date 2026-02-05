<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class Admin extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $table = 'admin_users';

    protected $casts = [
        'modules' => 'array',
        'is_super' => 'boolean',
    ];

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'modules',
        'is_super',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
{
        return true;
    }

    public function isSuper(): bool
    {
        if ((bool) $this->is_super) {
            return true;
        }

        if ($this->id === 1) {
            return true;
        }

        if ($this->username === 'admin') {
            return true;
        }

        return $this->email === 'admin@mcnext.io';
    }

    public function allowedModules(): array
    {
        $modules = $this->modules ?? [];

        return is_array($modules) ? $modules : [];
    }

    public function canAccessModule(?string $group): bool
    {
        if ($this->isSuper()) {
            return true;
        }

        if (! $group) {
            return false;
        }

        return in_array($group, $this->allowedModules(), true);
    }

    public static function moduleOptions(): array
    {
        return [
            '用户中心' => '用户中心',
            '指定VIP' => '指定VIP',
            '社区管理' => '社区管理',
            '收益审计' => '收益审计',
            '收益配额' => '收益配额',
            '质押体系' => '质押体系',
            '涡轮池监控' => '涡轮池监控',
            '系统管理' => '系统管理',
        ];
    }
}
