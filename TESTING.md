# Ledger App: Testing & Scheduler Quick Start

このファイルは、ローカルでテストとスケジューラを実行するための簡単な手順を説明しています。

## 前提条件

- Docker Desktop がインストール済み
- Git がインストール済み
- このプロジェクトをクローン済み

## GitHub Actions CI (推奨)

最も簡単な方法：GitHub に push するだけで自動テストが実行されます。

```bash
git add .
git commit -m "Add tests and scheduler"
git push origin main
```

GitHub の **Actions** タブで テスト結果を確認できます。

## ローカルでテストを実行 (Windows PowerShell)

### 1. Sail サービスの起動

```powershell
# プロジェクトディレクトリに移動
cd Ledger

# Sail サービスを起動（MySQL, Redis, Laravel など）
.\vendor\bin\sail up -d
```

初回実行時は Docker イメージをダウンロード・ビルドするため数分かかります。

### 2. テストの実行

**自動スクリプト（推奨）:**
```powershell
# すべてのテストを実行
.\scripts\test.ps1

# 特定のテストのみ実行
.\scripts\test.ps1 tests/Feature/SyncTest.php
```

**手動実行:**
```powershell
.\vendor\bin\sail artisan migrate --force
.\vendor\bin\sail artisan test
```

テスト結果例：
```
✓ tests/Feature/SyncTest.php
✓ tests/Feature/AccountTest.php
...
Tests: X passed
```

## スケジューラ & キャッシュのテスト

### 1. 合計流動資産をキャッシュに保存

```powershell
# 手動実行
.\vendor\bin\sail artisan ledger:compute-totals
```

### 2. スケジューラを監視（リアルタイム）

新しいターミナルを開き：

```powershell
# スケジューラは毎時間自動実行（Kernel で登録済み）
.\vendor\bin\sail artisan schedule:work
```

出力例：
```
Scheduling work ...
Running scheduled command: ledger:compute-totals
```

### 3. スケジューラを一度実行

```powershell
# 手動で一度実行
.\vendor\bin\sail artisan schedule:run
```

## Redis キャッシュの確認

```powershell
# Tinker シェルを開く
.\vendor\bin\sail artisan tinker

# Tinker内:
Cache::get('user:1:total_liquid_assets')

# キャッシュをクリア:
Cache::clear()
```

## サービスの停止

```powershell
.\vendor\bin\sail down
```

## トラブルシューティング

**症状: サービスが起動しない**
```powershell
# ログを確認
.\vendor\bin\sail logs

# コンテナを再ビルド
.\vendor\bin\sail build
```

**症状: データベースがロックされた**
```powershell
# データベースをリセット
.\vendor\bin\sail artisan migrate:reset
.\vendor\bin\sail artisan migrate --force
```

## 概要

- **CI**: GitHub Actions が自動的に PHPUnit を実行（`.github/workflows/phpunit.yml`）
- **テスト**: `./scripts/test.ps1` で実行
- **スケジューラ**: `.\vendor\bin\sail artisan schedule:work` で監視
- **キャッシュ**: Redis (Sail に含まれる) で自動保存
- **合計資産**: 毎時間自動計算、リアルタイム更新時も無効化

すべて実装済みです！ 🎉
