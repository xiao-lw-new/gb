# MG 模块 - 日志查询接口文档

本文档描述了 MG 模块中用于查询用户相关日志记录的 API 接口。

## 0. 额度信息查询 (Quota Info)

查询当前登录用户的总贡献值（总充值额度）和当前可用额度。

- **URL**: `/api/mg/quota/info`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数
无

### 响应示例

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "total_quota": "5000.000000000000000000",     // 历史累计总额度
        "available_quota": "1200.500000000000000000" // 当前可用额度
    }
}
```

---

## 0.1. 贡献值消耗总数 (Quota Consumed Total)

查询当前登录用户累计**消耗**的贡献值总数。

- **URL**: `/api/mg/quota/consumed`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数
无

### 响应示例

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "consumed_quota": "123.000000000000000000"
  }
}
```

---

## 1. 贡献值购买记录 (Quota Buy Logs)

查询当前登录用户购买贡献值（算力额度）的历史记录。

- **URL**: `/api/mg/logs/quota-buy`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| page | int | 否 | 1 | 页码 |
| per_page | int | 否 | 15 | 每页数量 |

### 响应示例

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "user_id": 1001,
                "transaction_hash": "0xabc...",
                "log_index": 1,
                "block_time": 1736659200,
                "type": 0, // 0: 购买, 1: 消耗, ...
                "quota_before": "0.000000000000000000",
                "amount": "100.000000000000000000", // 购买数量
                "quota_after": "100.000000000000000000",
                "cumulative_before": "0.000000000000000000",
                "cumulative_after": "100.000000000000000000",
                "remark": "Buy profit quota via contract event",
                "created_at": "2026-01-12 12:00:00",
                "updated_at": "2026-01-12 12:00:00"
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 68
        }
    }
}
```

---

## 2. 额度流水 (Quota Flow Logs)

查询当前登录用户的额度变动流水（包含购买增加、收益发放扣除），仅返回以下字段：
`transaction_hash`, `block_time`, `quota_before`, `amount`, `quota_after`。

- **URL**: `/api/mg/logs/quota-flow`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| page | int | 否 | 1 | 页码 |
| per_page | int | 否 | 15 | 每页数量 |
| type | int | 否 | - | 0:购买, 1:收益扣除 |

### 响应示例

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "transaction_hash": "0xabc...",
        "block_time": 1736659200,
        "quota_before": "0.000000000000000000",
        "amount": "100.000000000000000000",
        "quota_after": "100.000000000000000000"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 68
    }
  }
}
```

---

## 3. 涡轮池买入记录 (Turbine Pool Swap In Logs)

查询当前登录用户在涡轮池中**投入 MCN 换取 MX** 的记录。

- **URL**: `/api/mg/logs/turbine-in`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| page | int | 否 | 1 | 页码 |
| per_page | int | 否 | 15 | 每页数量 |

### 响应示例

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "list": [
            {
                "id": 5,
                "user_id": 1001,
                "type": "in",
                "transaction_hash": "0xdef...",
                "log_index": 2,
                "block_time": 1736662800,
                "in_amount": "50.000000000000000000",      // 消耗 MCN 数量
                "in_claim_quota": "50.000000000000000000", // 增加的领取额度
                "in_worth": "50.000000000000000000",       // 投入价值 (U)
                "mx_amount": "48.500000000000000000",      // 获得 MX 数量
                "created_at": "2026-01-12 13:00:00",
                "updated_at": "2026-01-12 13:00:00"
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 5
        }
    }
}
```

---

## 4. 涡轮池卖出记录 (Turbine Pool Swap Out Logs)

查询当前登录用户在涡轮池中**卖出 MX 换回 MCN** 的记录。

- **URL**: `/api/mg/logs/turbine-out`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| page | int | 否 | 1 | 页码 |
| per_page | int | 否 | 15 | 每页数量 |

### 响应示例

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "list": [
            {
                "id": 2,
                "user_id": 1001,
                "type": "out",
                "transaction_hash": "0xghi...",
                "log_index": 5,
                "block_time": 1736666400,
                "out_mx_amount": "10.000000000000000000",  // 卖出 MX 数量
                "out_worth": "12.000000000000000000",      // 卖出价值 (U)
                "fee": "0.600000000000000000",             // 手续费
                "back_worth": "11.400000000000000000",     // 实际返还价值
                "out_quota": "12.000000000000000000",      // 移除的额度数量
                "buy_mcn_amount": "11.400000000000000000", // 买入(返还)的 MCN 数量
                "created_at": "2026-01-12 14:00:00",
                "updated_at": "2026-01-12 14:00:00"
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 2,
            "per_page": 15,
            "total": 20
        }
    }
}
```

---

## 5. 涡轮池买入/卖出合并记录 (Turbine Pool Swap Logs)

查询当前登录用户在涡轮池中 **买入/卖出** 的合并记录，返回结果中使用 `type` 字段区分：

- `type=in`：买入记录（来自 `mg_turbine_pool_swap_in_logs`）
- `type=out`：卖出记录（来自 `mg_turbine_pool_swap_out_logs`）

- **URL**: `/api/mg/logs/turbine`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| page | int | 否 | 1 | 页码 |
| per_page | int | 否 | 15 | 每页数量 |
| type | string | 否 | - | 传 `in` 或 `out` 可只看某一类 |

### 响应说明

为便于合并分页，接口会返回两张表字段的“合集”，不属于该 `type` 的字段为 `null`。

### 响应示例

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 5,
        "user_id": 1001,
        "type": "in",
        "transaction_hash": "0xdef...",
        "log_index": 2,
        "block_time": 1736662800,
        "in_amount": "50.000000000000000000",
        "in_claim_quota": "50.000000000000000000",
        "in_worth": "50.000000000000000000",
        "mx_amount": "48.500000000000000000",
        "out_mx_amount": null,
        "out_worth": null,
        "fee": null,
        "back_worth": null,
        "out_quota": null,
        "buy_mcn_amount": null,
        "cur_mx_amount": null,
        "cur_worth": null,
        "created_at": "2026-01-12 13:00:00",
        "updated_at": "2026-01-12 13:00:00"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 5
    }
  }
}
```

---

## 6. 收益汇总信息 (Rewards Summary)

查询当前用户的静态和动态收益余额。

- **URL**: `/api/mg/rewards/summary`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数
无

### 响应示例

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "static_rewards": "150.500000000000000000", // 静态收益余额
        "dynamic_rewards": "320.000000000000000000" // 动态收益余额
    }
}
```

---

## 5. 收益流水记录 (Rewards Flow)

查询用户的收益流水记录，支持筛选静态或动态收益。

- **URL**: `/api/mg/rewards/flow`
- **Method**: `GET`
- **Auth**: Required (Bearer Token)

### 请求参数

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| page | int | 否 | 1 | 页码 |
| per_page | int | 否 | 15 | 每页数量 |
| type | string | 否 | `all` | 流水类型筛选: `all` (全部), `static` (静态), `dynamic` (动态) |

### 响应示例

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "list": [
            {
                "id": 10,
                "user_id": 1001,
                "type": "static", // static, referral, community
                "amount": "1.500000000000000000",
                "visible": 1,
                "tx_hash": null,
                "created_at": "2026-01-12 15:30:00",
                "updated_at": "2026-01-12 15:30:00"
            },
            {
                "id": 9,
                "user_id": 1001,
                "type": "referral",
                "amount": "0.800000000000000000",
                "visible": 1,
                "tx_hash": null,
                "created_at": "2026-01-12 14:20:00",
                "updated_at": "2026-01-12 14:20:00"
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 10,
            "per_page": 15,
            "total": 145
        }
    }
}
```
