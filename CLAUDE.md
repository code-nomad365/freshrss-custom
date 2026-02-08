# FreshRSS 自訂部署（Zeabur）

## 專案概述
自訂 FreshRSS Docker image，預裝 AI 摘要等擴充套件，部署於 Zeabur 台北區域。

## 線上環境
- **URL**：https://freshrss-ai.zeabur.app
- **Zeabur 專案**：`freshrss-tpe`（ID: `698893c3db63a2cc72047879`）
- **服務**：`freshrss-ai`（ID: `69889895db63a2cc72047b51`）
- **環境**：production（ID: `698893c32579f38ed02c63dc`）
- **GitHub repo**：`code-nomad365/freshrss-custom`（repo ID: `1152833879`）

## 架構

```
Dockerfile
├── 基底：freshrss/freshrss:latest
├── COPY extensions/ → /var/www/FreshRSS/extensions/
├── 備份 data → data-default（供 Volume 空時還原）
└── sed 注入還原邏輯到原始 entrypoint
```

### Volume 持久化（重要！）
- Zeabur Volume 掛載在 `/var/www/FreshRSS/data`
- Volume ID：`freshrss-data`
- **Dockerfile 的 VOLUME 指令在 Zeabur 無效**，必須在 Dashboard 手動設定
- 原始 entrypoint 被注入還原邏輯：Volume 空時自動從 `data-default` 複製預設資料

## 已安裝擴充套件

| 擴充套件 | 類型 | 說明 |
|---------|------|------|
| xExtension-ArticleSummary | AI | 手動點擊生成單篇摘要（OpenAI） |
| xExtension-FeedDigest | AI | 自動摘要新抓取的文章 |
| xExtension-AiAssistant | AI | 摘要 + 自動標籤 + 改標題 |
| xExtension-NewsAssistant | AI | GPT 摘要，支援繁中介面 |
| xExtension-AutoRefresh | 工具 | 自動刷新頁面 |
| xExtension-ColorfulList | 工具 | 依 RSS 來源顯示彩色標題 |

## 部署流程

### 新增/移除擴充套件
1. 在 `extensions/` 目錄下新增或刪除擴充套件資料夾
2. 記得移除擴充套件的 `.git` 目錄
3. `git add -A && git commit && git push`
4. 觸發 Zeabur 重新部署（用 `deploy-from-specification`）
5. **不需要重新初始化**，帳號和訂閱會保留

### Zeabur 部署指令
```
deploy-from-specification:
  service_id: 69889895db63a2cc72047b51
  source:
    type: BUILD_FROM_SOURCE
    build_from_source:
      source:
        type: GITHUB
        github:
          repo_id: 1152833879
      dockerfile:
        path: /Dockerfile
  env: []
```

## 踩過的坑

1. **Zeabur VOLUME 指令無效**：必須在 Dashboard → 服務 → Volumes 手動設定
2. **Volume 掛載會清空目錄**：需要在建置時備份 `data-default`，啟動時還原
3. **覆蓋 ENTRYPOINT 會清除 CMD**：FreshRSS 的 CMD 是複雜的 shell 命令，不能覆蓋 ENTRYPOINT。改用 `sed` 注入邏輯到原始 entrypoint
4. **擴充套件 git clone 後要刪 .git**：否則會變成 git submodule，推送後 GitHub 上是空資料夾
