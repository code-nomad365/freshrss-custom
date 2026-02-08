# xExtension-ArticleSummary 项目结构

```
.
├── .github/
│   ├── ISSUE_TEMPLATE/
│   │   ├── bug_report.md
│   │   └── feature_request.md
│   └── workflows/
│       └── translator.yml
├── .gitignore
├── .trae/
│   └── rules/
│       └── project_rules.md
├── configure.phtml
├── Controllers/
│   └── ArticleSummaryController.php
├── extension.php
├── i18n/
│   ├── en/
│   │   └── ArticleSummary.php
│   ├── zh-CN/
│   │   └── ArticleSummary.php
│   └── zh-TW/
│       └── ArticleSummary.php
├── metadata.json
├── README.md
├── README_zh.md
└── static/
    ├── axios.js
    ├── marked.js
    ├── script.js
    └── style.css
```

## 文件说明

- **.github/**: GitHub 相关配置文件，包括 Issue 模板和 CI 工作流
- **.gitignore**: Git 忽略文件配置
- **.trae/rules/project_rules.md**: 项目规则文件
- **configure.phtml**: 扩展配置页面
- **Controllers/ArticleSummaryController.php**: 控制器文件，处理扩展的后端逻辑
- **extension.php**: 扩展主文件，定义扩展的基本信息和功能
- **i18n/**: 国际化文件目录，包含不同语言的翻译
- **metadata.json**: 扩展元数据文件
- **README.md** 和 **README_zh.md**: 项目说明文档（英文和中文）
- **static/**: 静态资源目录，包含 JavaScript 和 CSS 文件
  - **axios.js**: Axios 库，用于 HTTP 请求
  - **marked.js**: Marked 库，用于 Markdown 解析
  - **script.js**: 扩展的前端脚本
  - **style.css**: 扩展的样式文件

# 这个显示只有一个插件，就是 ArticleSummary 的代码, 没有freshrss 相关的代码, 也没有其他插件相关的代码, 如果想要相关的文档, 请 use context7, use Tavily search 来搜索相关内容