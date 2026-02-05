# MinePool 合约前端接口文档

## 概述

MinePool 合约提供质押挖矿、收益额度购买、涡轮池交换和Merkle树奖励领取功能。本文档描述前端与合约交互所需的方法和数据结构。

## 数据结构

### StakeConfig（质押配置）

质押类型配置信息。

```typescript
interface StakeConfig {
  minAmount: string;    // 最小质押金额（价值，单位：wei）
  maxAmount: string;    // 最大质押金额（价值，单位：wei）
  dayTime: string;      // 质押天数（7天或15天）
}
```

### Record（质押记录）

用户质押记录详情。

```typescript
interface Record {
  idx: string;          // 记录索引（在用户记录列表中的位置）
  stakeWorth: string;   // 质押价值（单位：wei）
  principal: string;    // 质押本金（原生币数量，单位：wei）
  stakeType: string;    // 质押类型（1=7天，2=15天）
  startTime: string;    // 开始时间（Unix时间戳）
  endTime: string;      // 结束时间（Unix时间戳，0表示未解质押）
}
```

### StakeData（用户质押数据）

用户当前质押汇总数据。

```typescript
interface StakeData {
  totalWorth: string;   // 总质押价值（单位：wei）
  stakeType: string;    // 质押类型（1=7天，2=15天，0表示无质押）
  lastStakeTime: string; // 上次质押时间（Unix时间戳）
}
```

### TurbineData（涡轮池数据）

用户涡轮池相关信息。

```typescript
interface TurbineData {
  worth: string;        // 投入价值（单位：wei）
  mxAmount: string;     // MX代币数量（单位：wei）
  claimQuota: string;   // 可提取额度（单位：wei）
}
```

### MerkleData（Merkle树数据）

Merkle树奖励相关配置和数据。

```typescript
interface MerkleData {
  merkleRoot: string;           // Merkle树根哈希
  merkleVersion: string;        // Merkle树版本号
  merkleTotalReward: string;    // 总奖励数量（单位：wei）
  nextMerkleUpdateTime: string; // 下次更新时间（Unix时间戳）
  merkleTotalClaimed: string;   // 已领取总数量（单位：wei）
}
```

## 查询方法（View）

### getUserStakeRecords

获取指定用户的所有质押记录。

**函数签名：**
```solidity
function getUserStakeRecords(address user_) external view returns (Record[] memory)
```

**参数：**
- `user_` (address): 用户地址

**返回值：**
- `Record[]`: 质押记录数组

**说明：**
- 返回的记录中 `idx` 字段为在数组中的索引位置（从0开始）
- 返回的记录按创建时间顺序排列

---

### nativePrice

获取原生币（MCN）的价格。

**函数签名：**
```solidity
function nativePrice() external view returns (uint256)
```

**返回值：**
- `uint256`: 原生币价格（单位：wei）

**说明：**
- 价格由 Oracle 合约提供
- 用于计算质押金额对应的价值

---

### getStakeData

获取用户的质押状态信息。

**函数签名：**
```solidity
function getStakeData(address user_) external view returns(uint256, bool)
```

**参数：**
- `user_` (address): 用户地址

**返回值：**
- `uint256`: 用户可质押的类型（0表示1和2都可以，1表示只能质押7天，2表示只能质押15天）
- `bool`: 当前是否可质押（true=可以质押，false=不可以质押）

**说明：**
- 如果用户没有质押（`totalWorth == 0`），返回类型为0（可以质押任意类型）
- 如果用户已有质押，返回对应的质押类型
- 可质押判断：当前日期必须大于上次质押日期（同一天不能重复质押）

---

### stakeConfigs

获取指定质押类型的配置信息。

**函数签名：**
```solidity
function stakeConfigs(uint256) external view returns (StakeConfig memory)
```

**参数：**
- `uint256`: 质押类型（1=7天，2=15天）

**返回值：**
- `StakeConfig`: 质押配置信息

---

### profitQuota

获取指定用户的收益额度。

**函数签名：**
```solidity
function profitQuota(address) external view returns (uint256)
```

**参数：**
- `address`: 用户地址

**返回值：**
- `uint256`: 收益额度（单位：wei）

**说明：**
- 收益额度用于提取奖励
- 购买收益额度时，每投入1个单位的价值可获得3个单位的额度

---

### recordLength

获取总记录数。

**函数签名：**
```solidity
function recordLength() external view returns (uint256)
```

**返回值：**
- `uint256`: 总记录数

---

### recordList

根据记录ID获取质押记录详情。

**函数签名：**
```solidity
function recordList(uint256) external view returns (Record memory)
```

**参数：**
- `uint256`: 记录ID

**返回值：**
- `Record`: 质押记录详情

---

### userStakeData

获取指定用户的质押数据。

**函数签名：**
```solidity
function userStakeData(address) external view returns (StakeData memory)
```

**参数：**
- `address`: 用户地址

**返回值：**
- `StakeData`: 用户质押数据（包含 `totalWorth`、`stakeType`、`lastStakeTime`）

**说明：**
- `lastStakeTime` 用于判断同一天是否可以重复质押
- 建议使用 `getStakeData` 函数获取更友好的质押状态信息

---

### userTurbineData

获取指定用户的涡轮池数据。

**函数签名：**
```solidity
function userTurbineData(address) external view returns (TurbineData memory)
```

**参数：**
- `address`: 用户地址

**返回值：**
- `TurbineData`: 用户涡轮池数据

---

### estimatedTurbinePoolIn

估算涡轮池转入的MX代币数量和价值。

**函数签名：**
```solidity
function estimatedTurbinePoolIn(uint256 amount_) external view returns (uint256, uint256)
```

**参数：**
- `amount_` (uint256): 要转入的原生币数量（单位：wei）

**返回值：**
- `uint256`: 将获得的MX代币数量（单位：wei）
- `uint256`: 对应的价值（单位：wei）

**说明：**
- 用于前端在转入前估算能获得多少MX代币和价值
- 基于当前DEX价格计算

---

### estimatedTurbinePoolOut

估算涡轮池转出的手续费和收益。

**函数签名：**
```solidity
function estimatedTurbinePoolOut(address user_, uint256 amount_) external view returns (uint256, uint256)
```

**参数：**
- `user_` (address): 用户地址
- `amount_` (uint256): 要转出的MX代币数量（单位：wei）

**返回值：**
- `uint256`: 手续费（单位：wei，如果有盈利）
- `uint256`: 转回的价值（扣除手续费后，单位：wei）

**说明：**
- 用于前端在转出前估算手续费和收益
- 如果转出价值大于投入价值，会计算15%的盈利手续费

---

### merkleData

获取Merkle树奖励的配置和数据。

**函数签名：**
```solidity
function merkleData() external view returns (MerkleData memory)
```

**返回值：**
- `MerkleData`: Merkle树数据

---

### userClaimedState

查询用户是否已领取指定版本的奖励。

**函数签名：**
```solidity
function userClaimedState(address, uint256) external view returns (bool)
```

**参数：**
- `address`: 用户地址
- `uint256`: Merkle树版本号

**返回值：**
- `bool`: 是否已领取（true=已领取，false=未领取）

---

### merkleClaimedAmounts

获取用户已领取的奖励总额。

**函数签名：**
```solidity
function merkleClaimedAmounts(address) external view returns (uint256)
```

**参数：**
- `address`: 用户地址

**返回值：**
- `uint256`: 已领取的奖励总额（单位：wei）

---

### merkleClaimedMCN

获取用户已领取的MCN数量。

**函数签名：**
```solidity
function merkleClaimedMCN(address) external view returns (uint256)
```

**参数：**
- `address`: 用户地址

**返回值：**
- `uint256`: 已领取的MCN数量（单位：wei）

---

### merkleTotalClaimedMCN

获取所有用户已领取的MCN总数量。

**函数签名：**
```solidity
function merkleTotalClaimedMCN() external view returns (uint256)
```

**返回值：**
- `uint256`: 已领取的MCN总数量（单位：wei）

## 交易方法（Transaction）

### stake

进行质押操作。

**函数签名：**
```solidity
function stake(uint256 sType_, uint256 amount_, uint256 quotaAmount_) external payable
```

**参数：**
- `sType_` (uint256): 质押类型（1=7天，2=15天）
- `amount_` (uint256): 质押金额（原生币数量，单位：wei）
- `quotaAmount_` (uint256): 购买收益额度的金额（原生币数量，单位：wei）

**发送金额：**
- `msg.value` 必须等于 `amount_ + quotaAmount_`

**业务规则：**
1. 质押金额对应的价值必须在配置的最小值和最大值之间
2. 如果用户已有质押，新质押类型必须与现有类型相同
3. 同一天不能重复质押（当前日期必须大于上次质押日期）
4. 如果 `quotaAmount_ > 0`，会自动购买收益额度（1单位价值获得3单位额度）

**事件：**
- `EventStake`: 质押成功时触发

---

### unstake

解质押操作。

**函数签名：**
```solidity
function unstake(uint256 index_, uint256 releaseLevel_, uint256 feeAmount_) external payable
```

**参数：**
- `index_` (uint256): 在用户质押记录列表中的索引位置
- `releaseLevel_` (uint256): 释放等级，1是立即，2是15天，3是30天
- `feeAmount_` (uint256): 手续费金额（原生币数量，单位：wei）

**发送金额：**
- `msg.value` 必须等于 `feeAmount_`

**业务规则：**
1. 质押必须已到期（到期时间 = 开始时间 + 配置天数）
2. 记录必须未解质押（`endTime == 0`）
3. 解质押后，本金会转入 ReleasePool 进行释放

**事件：**
- `EventUnstake`: 解质押成功时触发

---

### buyProfitQuota

购买收益额度。

**函数签名：**
```solidity
function buyProfitQuota(uint256 amount_) external payable
```

**参数：**
- `amount_` (uint256): 购买额度的金额（原生币数量，单位：wei）

**发送金额：**
- `msg.value` 必须等于 `amount_`

**业务规则：**
1. 投入的金额会兑换为MX代币并销毁
2. 每投入1个单位的价值可获得3个单位的收益额度
3. 额度会累加到用户的 `profitQuota` 中

**事件：**
- `EventBuyProfitQuota`: 购买成功时触发

---

### turbinePoolSwapIn

向涡轮池转入资金。

**函数签名：**
```solidity
function turbinePoolSwapIn(uint256 amount_) external payable
```

**参数：**
- `amount_` (uint256): 转入金额（原生币数量，单位：wei）

**发送金额：**
- `msg.value` 必须等于 `amount_`

**业务规则：**
1. 原生币会兑换为MX代币并存入合约
2. 增加用户的 `claimQuota`（可提取额度）
3. 增加用户的 `worth`（投入价值）
4. 增加用户的 `mxAmount`（MX代币数量）

**事件：**
- `EventTurbinePoolSwapIn`: 转入成功时触发

---

### turbinePoolSwapOut

从涡轮池转出资金。

**函数签名：**
```solidity
function turbinePoolSwapOut(uint256 amount_) external
```

**参数：**
- `amount_` (uint256): 要转出的MX代币数量（单位：wei）

**业务规则：**
1. 将指定数量的MX代币兑换回原生币
2. 转出的MX数量不能超过用户持有的MX数量
3. 如果盈利（转出价值 > 投入价值），收取15%的盈利手续费
4. 转出后，`claimQuota` 会被清空为0
5. 转出后，用户数据会更新（保留剩余的MX代币和对应的价值）
6. 剩余金额转给用户

**事件：**
- `EventTurbinePoolSwapOut`: 转出成功时触发

---

### claim

领取Merkle树奖励。

**函数签名：**
```solidity
function claim(
    uint256 feeAmount_,
    uint256 releaseLevel_,
    uint256 totalAmount_,
    bytes32[] calldata merkleProof_
) external payable
```

**参数：**
- `feeAmount_` (uint256): 手续费金额（原生币数量，单位：wei）
- `releaseLevel_` (uint256): 释放等级，1是立即，2是15天，3是30天
- `totalAmount_` (uint256): 用户的总奖励数量（单位：wei）
- `merkleProof_` (bytes32[]): Merkle树证明

**发送金额：**
- `msg.value` 必须等于 `feeAmount_`

**业务规则：**
1. Merkle树根必须已设置
2. 用户必须提供有效的Merkle证明
3. 用户不能重复领取同一版本的奖励
4. 领取的奖励数量不能超过用户的收益额度（`profitQuota`）
5. 领取的奖励数量不能超过用户的提取额度（`claimQuota`）
6. 奖励会转入 ReleasePool 进行释放
7. 领取后，会扣除对应的 `claimQuota`

**事件：**
- `EventClaim`: 领取成功时触发

## 事件（Events）

### EventStake

质押事件。

```solidity
event EventStake(
  address indexed user,
  uint256 stakeType,
  uint256 stakeWorth,
  uint256 stakeAmount,
  uint256 recordIdx
);
```

**参数：**
- `user`: 用户地址
- `stakeType`: 质押类型
- `stakeWorth`: 质押MCN对应的USDT价值
- `stakeAmount`: 质押MCN的数量
- `recordIdx`: 记录ID

---

### EventUnstake

解质押事件。

```solidity
event EventUnstake(
  address indexed user,
  uint256 recordIdx,
  uint256 stakeType,
  uint256 stakeAmount,
  uint256 releaseLevel
);
```

**参数：**
- `user`: 用户地址
- `recordIdx`: 记录ID
- `stakeType`: 质押类型
- `stakeAmount`: 解质押MCN的数量
- `releaseLevel`: 释放等级，1是立即，2是15天，3是30天

---

### EventTurbinePoolSwapIn

涡轮池转入事件。

```solidity
event EventTurbinePoolSwapIn(
  address indexed user,
  uint256 inAmount,
  uint256 inClaimQuota,
  uint256 inWorth,
  uint256 mxAmount
);
```

**参数：**
- `user`: 用户地址
- `inAmount`: 转入金额
- `inClaimQuota`: 增加的提取额度
- `inWorth`: 增加的价值
- `mxAmount`: 获得的MX代币数量

---

### EventTurbinePoolSwapOut

涡轮池转出事件。

```solidity
event EventTurbinePoolSwapOut(
  address indexed user,
  uint256 outMxAmount,
  uint256 outWorth,
  uint256 fee_,
  uint256 backWorth,
  uint256 curMxAmount,
  uint256 curWorth
);
```

**参数：**
- `user`: 用户地址
- `outMxAmount`: 转出的MX代币数量
- `outWorth`: 转出的价值（用于计算投入价值减少）
- `fee_`: 手续费（如果有盈利）
- `backWorth`: 转回的价值（扣除手续费后）
- `curMxAmount`: 转出后剩余的MX代币数量
- `curWorth`: 转出后剩余的价值

---

### EventChangeStakeTotal

质押总额变更事件。

```solidity
event EventChangeStakeTotal(
  address indexed user,
  bool isAdd,
  uint256 stakeType,
  uint256 changeWorth,
  uint256 newTotalWorth
);
```

**参数：**
- `user`: 用户地址
- `isAdd`: 是否为增加（true=增加，false=减少）
- `stakeType`: 质押类型
- `changeWorth`: 变更的价值
- `newTotalWorth`: 新的总价值

---

### EventBuyProfitQuota

购买收益额度事件。

```solidity
event EventBuyProfitQuota(
  address indexed user,
  uint256 profitWorth,
  uint256 newProfitQuota,
  uint256 quotaCostAmount,
  uint256 burnMXAmount
);
```

**参数：**
- `user`: 用户地址
- `profitWorth`: 获得的收益额度
- `newProfitQuota`: 新的总收益额度
- `quotaCostAmount`: 花费的金额
- `burnMXAmount`: 销毁的MX代币数量

---

### EventClaim

领取奖励事件。

```solidity
event EventClaim(
  address user,
  uint256 reward,
  uint256 totalAmount,
  uint256 mcnAmount,
  uint256 releaseLevel_
);
```

**参数：**
- `user`: 用户地址
- `reward`: 本次领取的奖励数量（单位：wei）
- `totalAmount`: 用户的总奖励数量（单位：wei）
- `mcnAmount`: 对应的MCN数量（单位：wei）
- `releaseLevel_`: 释放等级

---

### EventUpdateMerkleRoot

更新Merkle树根事件。

```solidity
event EventUpdateMerkleRoot(
  bytes32 merkleRoot,
  uint256 merkleVersion,
  uint256 newTotalReward,
  uint256 addReward
);
```

**参数：**
- `merkleRoot`: 新的Merkle树根哈希
- `merkleVersion`: 新的版本号
- `newTotalReward`: 新的总奖励数量（单位：wei）
- `addReward`: 新增的奖励数量（单位：wei）

**说明：**
- 此事件由管理员调用 `updateMerkleRoot` 时触发
- 前端通常不需要直接调用此方法

## 错误处理

### ErrorAddressZero

地址为零地址错误。

```solidity
error ErrorAddressZero();
```

### ErrorFailTransferNative

原生币转账失败错误。

```solidity
error ErrorFailTransferNative();
```

### ErrorMsgValue

发送金额与参数不匹配错误。

```solidity
error ErrorMsgValue();
```

### ErrorWorthError

质押价值超出范围错误。

```solidity
error ErrorWorthError();
```

### ErrorStakeTypeError

质押类型不匹配错误。

```solidity
error ErrorStakeTypeError();
```

### ErrorNoExpired

质押未到期错误。

```solidity
error ErrorNoExpired();
```

### ErrorAlreadyUnstake

已解质押错误。

```solidity
error ErrorAlreadyUnstake();
```

### ErrorEmptyTurbine

涡轮池为空错误。

```solidity
error ErrorEmptyTurbine();
```

### ErrorStakeTime

质押时间错误（同一天不能重复质押）。

```solidity
error ErrorStakeTime();
```

### ErrorRewardType

奖励类型错误（Merkle根未设置）。

```solidity
error ErrorRewardType();
```

### ErrorAlreadyClaimed

已领取错误。

```solidity
error ErrorAlreadyClaimed();
```

### ErrorClaimedEpoch

已领取该版本奖励错误。

```solidity
error ErrorClaimedEpoch();
```

### ErrorInvalidProof

Merkle证明无效错误。

```solidity
error ErrorInvalidProof();
```

### ErrorRewardAmount

奖励数量错误。

```solidity
error ErrorRewardAmount();
```

### ErrorClaimQuotaNotEnough

提取额度不足错误。

```solidity
error ErrorClaimQuotaNotEnough();
```

## 前端交互流程建议

### 质押流程

1. 查询 `getStakeData(user)` 获取用户质押状态和可质押类型
2. 确认返回的 `bool` 值为 `true`（可以质押）
3. 查询 `stakeConfigs(sType_)` 获取质押配置
4. 查询 `nativePrice()` 计算质押金额对应的价值
5. 验证价值是否在配置范围内
6. 如果用户已有质押，确认新质押类型与返回的类型一致
7. 调用 `stake(sType_, amount_, quotaAmount_)` 并发送 `amount_ + quotaAmount_` 的原生币

### 解质押流程

1. 查询 `getUserStakeRecords(user)` 获取用户质押记录
2. 根据记录计算是否到期（到期时间 = startTime + dayTime）
3. 确认记录未解质押（`endTime == 0`）
4. 调用 `unstake(index_, releaseLevel_, feeAmount_)` 并发送 `feeAmount_` 的原生币

### 购买收益额度流程

1. 调用 `buyProfitQuota(amount_)` 并发送 `amount_` 的原生币
2. 监听 `EventBuyProfitQuota` 事件获取结果

### 涡轮池操作流程

**转入：**
1. 可选：调用 `estimatedTurbinePoolIn(amount_)` 估算将获得的MX代币数量和价值
2. 调用 `turbinePoolSwapIn(amount_)` 并发送 `amount_` 的原生币
3. 监听 `EventTurbinePoolSwapIn` 事件获取结果

**转出：**
1. 查询 `userTurbineData(user)` 确认有余额
2. 可选：调用 `estimatedTurbinePoolOut(user, amount_)` 估算手续费和收益
3. 调用 `turbinePoolSwapOut(amount_)` 传入要转出的MX代币数量
4. 监听 `EventTurbinePoolSwapOut` 事件获取结果

### 领取奖励流程

1. 查询 `merkleData()` 获取当前Merkle树信息
2. 查询 `userClaimedState(user, version)` 确认未领取该版本奖励
3. 查询 `merkleClaimedAmounts(user)` 获取已领取数量
4. 查询 `profitQuota(user)` 和 `userTurbineData(user).claimQuota` 确认额度充足
5. 从后端获取用户的Merkle证明（`merkleProof_`）和总奖励数量（`totalAmount_`）
6. 查询 `getFeeAmountByLevel`（在ReleasePool中）获取手续费
7. 调用 `claim(feeAmount_, releaseLevel_, totalAmount_, merkleProof_)` 并发送 `feeAmount_` 的原生币
8. 监听 `EventClaim` 事件获取结果

## 注意事项

1. 所有金额单位均为 wei（1 ETH = 10^18 wei）
2. 质押类型：1 = 7天，2 = 15天
3. 质押到期判断：到期时间必须精确到天数边界（基于UTC+8时区，偏移28800秒）
4. 盈利手续费：15%（1500/10000）
5. 购买收益额度比例：1单位价值 = 3单位额度
6. 所有 payable 函数都需要精确匹配 `msg.value` 和参数值
7. 用户质押类型必须一致：如果已有质押，新质押必须使用相同类型
8. 同一天不能重复质押：当前日期必须大于上次质押日期（基于UTC+8时区，偏移28800秒）
8. 领取奖励需要提供有效的Merkle证明，通常由后端服务生成
9. 领取奖励会消耗 `claimQuota`（提取额度），需要确保额度充足
10. 领取奖励会消耗 `profitQuota`（收益额度），需要确保额度充足
11. 每个Merkle版本只能领取一次，可通过 `userClaimedState` 查询是否已领取

