<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 基金会达标分红检查结果快照表（每用户一条，覆盖更新）
 *
 * 达标规则：3 个条件任一满足即达标，阈值 = 个人基金会质押分红狗头 * 10%
 * 每个条件都是"自己 + 伞下两代"的合计值与阈值比较
 *
 * @property int    $user_id        用户 ID
 * @property string $threshold      达标阈值 = total_burn_amount * 0.1（基金会质押分红狗头的 10%）
 * @property string $cond1_value    条件1 实际值：自己 + 伞下两代 持有狗头币数量合计（token_balance）
 * @property bool   $cond1_met      条件1 是否达标：合计持币 >= 阈值
 * @property string $cond2_value    条件2 实际值：自己 + 伞下两代 燃烧狗头币数量合计（burn_amount_new）
 * @property bool   $cond2_met      条件2 是否达标：合计燃烧 >= 阈值
 * @property string $cond3_value    条件3 实际值：自己 + 伞下两代 质押打新数量合计（new_gout_amount）
 * @property bool   $cond3_met      条件3 是否达标：合计打新 >= 阈值
 * @property bool   $is_qualified   最终是否达标（任一条件满足即为 true）
 * @property \Carbon\Carbon $checked_at 最后检查时间
 */
class FoundationQualificationResult extends Model
{
    protected $table = 'foundation_qualification_results';

    protected $fillable = [
        'user_id',
        'threshold',
        'cond1_value', 'cond1_met',
        'cond2_value', 'cond2_met',
        'cond3_value', 'cond3_met',
        'is_qualified',
        'checked_at',
    ];

    protected $casts = [
        'cond1_met' => 'boolean',
        'cond2_met' => 'boolean',
        'cond3_met' => 'boolean',
        'is_qualified' => 'boolean',
        'checked_at' => 'datetime',
    ];
}
