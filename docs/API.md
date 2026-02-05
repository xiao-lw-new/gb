# MCNext API 接口文档

**Base URL**: `https://iface.mcnext.io`

**通用响应格式**:
```json
{
    "code": 200,      // 200为成功，其他为错误码
    "msg": "success",  // 提示信息
    "data": { ... }    // 返回数据对象或数组
}
```

---

## 1. 身份验证 (Member 模块)

### 1.1 获取登录 Nonce (随机码)
用于获取签名所需的随机码，防止重放攻击。
- **URL**: `/api/member/auth/nonce`
- **Method**: `POST`
- **参数**:
  - `address` (string, required): 钱包地址 (42位)
- **CURL 示例**:
  ```bash
  curl -X POST https://iface.mcnext.io/api/member/auth/nonce \
       -H "Content-Type: application/json" \
       -H "Accept: application/json" \
       -d '{"address": "0x4746A2Ca9df4101f089078C362Ef0514019BF75d"}'
  ```
- **返回**:
  ```json
  {
    "code": 200,
    "msg": "",
    "data": {
      "nonce": "123456"
    }
  }
  ```

### 1.2 Web3 签名登录
- **URL**: `/api/member/auth/login`
- **Method**: `POST`
- **参数**:
  - `address` (string, required): 钱包地址
  - `signature` (string, required): 使用私钥对 Nonce 进行签名后的字符串
- **CURL 示例**:
  ```bash
  curl -X POST https://iface.mcnext.io/api/member/auth/login \
       -H "Content-Type: application/json" \
       -H "Accept: application/json" \
       -d '{"address": "0x4746A2Ca9df4101f089078C362Ef0514019BF75d", "signature": "0x..."}'
  ```
- **返回**:
  ```json
  {
    "code": 200,
    "msg": "Login successful",
    "data": {
      "token": "1|abcdefg...", // 后续请求需放在 Authorization: Bearer token 中
      "user": {
        "id": 1,
        "address": "0x...",
        "name": "0x...",
        "status": 1,
        "active": 1
      }
    }
  }
  ```

### 1.3 获取当前用户信息 (支持游客)
- **URL**: `/api/member/auth/user-info`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **CURL 示例**:
  ```bash
  curl -X GET https://iface.mcnext.io/api/member/auth/user-info \
       -H "Accept: application/json" \
       -H "Authorization: Bearer {TOKEN}"
  ```

---

## 2. MG 生态数据 (Mg 模块)

### 2.1 全局数据概览 (无需登录)
- **URL**: `/api/mg/overview`
- **Method**: `GET`
- **CURL 示例**:
  ```bash
  curl -X GET https://iface.mcnext.io/api/mg/overview \
       -H "Accept: application/json"
  ```
- **返回**:
  ```json
  {
    "data": {
      "active_users": 100,
      "total_staked": "5000.00",
      "turbine_pool": "10000.00"
    }
  }
  ```

### 2.2 个人社区信息 (支持游客)
- **URL**: `/api/mg/community/info`
- **Method**: `GET`
- **CURL 示例**:
  ```bash
  curl -X GET https://iface.mcnext.io/api/mg/community/info \
       -H "Accept: application/json" \
       -H "Authorization: Bearer {TOKEN}"
  ```
- **返回**:
  ```json
  {
    "data": {
      "direct_count": 10,       // 直推人数
      "direct_active_count": 5, // 直推有效人数
      "team_count": 50,         // 团队总人数
      "team_active_count": 20   // 团队有效人数
    }
  }
  ```

### 2.3 奖励余额查询 (支持游客)
- **奖励汇总 (Summary)**: `/api/mg/rewards/summary` (GET) **[推荐]**
- **静态奖励 (Static)**: `/api/mg/rewards/static` (GET) [已废弃，建议使用 summary]
- **动态奖励 (Dynamic)**: `/api/mg/rewards/dynamic` (GET) [已废弃，建议使用 summary]

- **CURL 示例 (Summary)**:
  ```bash
  curl -X GET https://iface.mcnext.io/api/mg/rewards/summary \
       -H "Accept: application/json" \
       -H "Authorization: Bearer {TOKEN}"
  ```
- **返回示例**:
  ```json
  {
    "code": 200,
    "msg": "",
    "data": {
      "static_rewards": "125.500000000000000000",
      "dynamic_rewards": "50.000000000000000000"
    }
  }
  ```

### 2.4 奖励流水记录 (分页 - 支持游客)
- **静态流水**: `/api/mg/rewards/static-flow?page=0` (GET)
- **动态流水**: `/api/mg/rewards/dynamic-flow?page=0` (GET)
- **参数**: `page` (int, default 0)
- **CURL 示例**:
  ```bash
  curl -X GET "https://iface.mcnext.io/api/mg/rewards/static-flow?page=0" \
       -H "Accept: application/json" \
       -H "Authorization: Bearer {TOKEN}"
  ```
- **返回示例**:
  ```json
  {
    "code": 200,
    "msg": "",
    "data": {
      "list": [
        {
          "time": "2026-01-09 10:00:00",
          "type": "static",
          "amount": "10.000000"
        }
      ]
    }
  }
  ```

### 2.5 赎回记录 (分页 - 需登录)
- **URL**: `/api/mg/logs/redeem`
- **Method**: `GET`
- **参数**:
  - `per_page` (int, optional): 每页数量，默认 15
  - `page` (int, optional): 页码，默认 1
- **CURL 示例**:
  ```bash
  curl -X GET "https://iface.mcnext.io/api/mg/logs/redeem?page=1" \
       -H "Accept: application/json" \
       -H "Authorization: Bearer {TOKEN}"
  ```
- **返回示例**:
  ```json
  {
    "code": 200,
    "msg": "success",
    "data": {
      "list": [
        {
          "start_time": "2026-01-10 10:00:00",  // 开始时间 (原始质押时间)
          "end_time": "2026-01-25 10:00:00",    // 结束时间 (根据释放等级计算)
          "total_amount": "100.000000000000000000", // 赎回总金额 (绝对值)
          "release_type": 2,                    // 释放类型值 (1: 立即, 2: 15天, 3: 30天)
          "release_type_label": "15天释放",      // 释放类型显示文本
          "transaction_hash": "0x..."           // 交易哈希
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 70
      }
    }
  }
  ```

---

## 3. 链上交互 (Blockchain 模块)

### 3.1 提交交易哈希 (需登录)
用户在前端完成链上合约调用后，将交易哈希提交给后端进行扫块同步。
- **URL**: `/api/blockchain/submit-tx`
- **Method**: `POST`
- **参数**:
  - `txHash` (string, required): 以 `0x` 开头的 64 位哈希
- **CURL 示例**:
  ```bash
  curl -X POST https://iface.mcnext.io/api/blockchain/submit-tx \
       -H "Content-Type: application/json" \
       -H "Accept: application/json" \
       -H "Authorization: Bearer {TOKEN}" \
       -d '{"txHash": "0x..."}'
  ```
- **返回示例**:
  ```json
  {
    "code": 200,
    "msg": "提交成功，后台正在处理中",
    "data": {}
  }
  ```

---

## 4. 奖励提取 (MerkleTree 模块)

### 4.1 获取提现 Merkle 证明 (Proof)
用于调用合约 claim 时的验证参数。
- **URL**: `/api/merkle/reward-proof`
- **Method**: `GET`
- **参数**:
  - `address` (string, required): 钱包地址
- **CURL 示例**:
  ```bash
  curl -X GET "https://iface.mcnext.io/api/merkle/reward-proof?address=0x4746A2Ca9df4101f089078C362Ef0514019BF75d" \
       -H "Accept: application/json"
  ```
- **返回示例**:
  ```json
  {
    "code": 200,
    "data": {
      "totalAmount": "1000000000000000000", // 单位 wei (18位)
      "merkleProof": [
        "0x123...",
        "0x456..."
      ]
    }
  }
  ```

---

**备注**: 
1. 所有的分页接口 `page` 从 `1` 开始 (Laravel 分页默认)。
2. 所有涉及金额的字段，除非特别说明，均返回字符串格式。
3. 测试网环境支持 `0x0000` 开头的地址绕过签名验证（Debug 模式）。
4. 所有请求建议带上 `-H "Accept: application/json"` 以确保获取 JSON 响应。
