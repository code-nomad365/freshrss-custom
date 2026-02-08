<?php

final class AIAssistantExtension extends Minz_Extension
{
    public function autoload(string $className): void
    {
        if ($className === 'AIHelper') {
            require_once __DIR__ . '/AIHelper.php';
        }
    }

    public function init(): void
    {
        parent::init();

        $this->registerHook('entry_before_insert', [$this, 'onEntryBeforeInsert']);
        $this->registerHook('entry_before_display', [$this, 'onEntryBeforeDisplay']);
        $this->registerHook('freshrss_user_maintenance', [$this, 'onUserMaintenance']);
        $this->registerHook('nav_menu', [$this, 'addSummarizeButton']);
        $this->registerController('AIAssistant');
        $this->registerViews();
    }

    public function install(): bool
    {
	$conf = Minz_Configuration::get('system');

        if (!isset($conf->openai_baseurl_article)) {
            $conf->openai_baseurl_article = 'https://api.openai.com/v1';
        }
        if (!isset($conf->openai_key_article)) {
            $conf->openai_key_article = 'YOUR_OPENAI_KEY';
        }
        if (!isset($conf->openai_model_article)) {
            $conf->openai_model_article = 'gpt-4o-mini';
        }
        if (!isset($conf->openai_temp_article)) {
            $conf->openai_temp_article = '0.7';
        }
        if (!isset($conf->openai_prompt_article)) {
            $conf->openai_prompt_article = "You are an impartial news reporter. Given an article, return JSON with the keys 'title', 'summary', and 'tags'.\nDo NOT wrap the JSON in any code blocks or nest it. Return the JSON in the format requested.\n\n* `title` is a de-editorialised version of the title that represents the key driving point of the article.\n* `summary` is an executive summary, containing the key facts and findings and conclusion.\n* `tags` is a comma-delimited list of relevant topics and key words.\n\nExample:\n{\"title\":\"...\",\"summary\":\"...\",\"tags\":\"...\"}\nArticle:\n{{ARTICLE}}";
        }

        if (!isset($conf->openai_baseurl_roundup)) {
            $conf->openai_baseurl_roundup = 'https://api.openai.com/v1';
        }
        if (!isset($conf->openai_key_roundup)) {
            $conf->openai_key_roundup = 'YOUR_OPENAI_KEY';
        }
        if (!isset($conf->openai_model_roundup)) {
            $conf->openai_model_roundup = 'gpt-4o-mini';
        }
        if (!isset($conf->openai_temp_roundup)) {
            $conf->openai_temp_roundup = '0.7';
        }
        if (!isset($conf->openai_prompt_roundup)) {
            $conf->openai_prompt_roundup = "You are a researcher reporting on a feed of articles of interest. Create an engaging and factually accurate summary of these articles, emphasizing significant new developments. Ensure a natural flow and high readability. Strip any bias or extraneous information unrelated to the topic but otherwise feel free to make predictions or observations. Do not use markdown or markup.\n\nArticles:\n{{ARTICLES}}";
        }

        if (!isset($conf->openai_max_tokens)) {
            $conf->openai_max_tokens = '4096';
        }
        if (!isset($conf->mark_read_after_summary)) {
            $conf->mark_read_after_summary = '0';
        }

        $conf->save();

        return true;
    }

    public function handleConfigureAction()
    {
        if (!Minz_Request::isPost()) {
            Minz_Log::warning("AI Assistant: Invalid request method for configure.");
            return;
        }

        $conf = Minz_Configuration::get('system');

        $fields = [
            'openai_baseurl_article','openai_key_article','openai_model_article','openai_temp_article','openai_prompt_article',
            'openai_baseurl_roundup','openai_key_roundup','openai_model_roundup','openai_temp_roundup','openai_prompt_roundup',
            'openai_max_tokens','mark_read_after_summary'
        ];

        foreach ($fields as $field) {
            $val = Minz_Request::param($field, '');
            $conf->$field = $val;
        }

        $conf->mark_read_after_summary = Minz_Request::param('mark_read_after_summary', '0');

        $conf->save();
    }

    public function onEntryBeforeInsert(FreshRSS_Entry $entry): FreshRSS_Entry
    {
        $conf = Minz_Configuration::get('system');
        $maxTokens = (int)($conf->openai_max_tokens ?? 4096);

        $fullText  = $entry->title() . "\n" . $entry->content();
        $systemMsg = "You are a helpful AI. Return JSON {\"title\":\"...\",\"summary\":\"...\",\"tags\":\"...\"}";
        $userPrompt= str_replace('{{ARTICLE}}', $fullText, $conf->openai_prompt_article);

        $articleData = AIHelper::generateArticleData(
            $conf->openai_baseurl_article,
            $conf->openai_key_article,
            $conf->openai_model_article,
            (float)$conf->openai_temp_article,
            $systemMsg,
            $userPrompt,
            $maxTokens
        );

        $newTitle = $articleData['title']   ?? "[AI Error Title]";
        $tags     = $articleData['tags']    ?? "";
        $summary  = $articleData['summary'] ?? "[AI Error Summary]";

        $entry->_title($newTitle);
        $entry->_tags($tags);
        $entry->_content(
            $entry->content() .
            "\n<!-- AI_SUMMARY_START -->$summary<!-- AI_SUMMARY_END -->"
        );
        return $entry;
    }

    public function onEntryBeforeDisplay(FreshRSS_Entry $entry): FreshRSS_Entry
    {
        $content = $entry->content();
        $sum     = $this->extractSummary($content);
        if ($sum !== "") {
            $entry->_content("<strong>Executive Summary:</strong> $sum<br/><br/>" . $content);
        }
        return $entry;
    }

    public function onUserMaintenance(): void
    {
        $today    = date('Y-m-d');
        $filePath = "/tmp/freshrss_roundup_{$today}.txt";

        if (file_exists($filePath)) {
            return;
        }

        $conf      = Minz_Configuration::get('system');
        $maxTokens = (int)($conf->openai_max_tokens ?? 4096);

        $articlesText = $this->gatherAllArticles();
        if ($articlesText === "") {
            file_put_contents($filePath, "[No articles found to summarize]");
            return;
        }

        $roundupData = AIHelper::generateArticleData(
            $conf->openai_baseurl_roundup,
            $conf->openai_key_roundup,
            $conf->openai_model_roundup,
            (float)$conf->openai_temp_roundup,
            "Summarize these articles for the day.",
            str_replace('{{ARTICLES}}', $articlesText, $conf->openai_prompt_roundup),
            $maxTokens
        );
        $dailySummary = $roundupData['summary'] ?? "[AI Error generating roundup]";
        file_put_contents($filePath, $dailySummary);
    }

    public function addSummarizeButton(): string
    {
        $catId = FreshRSS_Context::isCategory() ? (int)FreshRSS_Context::$current_get['category'] : 0;
        $st    = (int)FreshRSS_Context::$state;
        if ($st === 0) {
            $st = FreshRSS_Entry::STATE_NOT_READ;
        }

        $url = Minz_Url::display([
            'c' => 'AIAssistant',
            'a' => 'summary',
            'params' => [
                'cat_id' => $catId,
                'state'  => $st,
            ]
        ]);
        return '<a class="btn" href="'.$url.'" title="AI Summaries">'
             . '📰 Summarize'
             . '</a>';
    }

    private function extractSummary(string $content): string
    {
        if (preg_match('/<!-- AI_SUMMARY_START -->(.*?)<!-- AI_SUMMARY_END -->/s', $content, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function gatherAllArticles(): string
    {
        $files = glob("/tmp/freshrss_ai_*.json");
        if (!$files) {
            return "";
        }
        $lines = [];
        foreach ($files as $file) {
            $arr = json_decode(file_get_contents($file), true);
            if (!is_array($arr)) {
                continue;
            }
            $t = $arr['title']   ?? "[No title]";
            $s = $arr['summary'] ?? "[No summary]";
            $lines[] = "Title: $t\n$s";
        }
        return implode("\n\n", $lines);
    }
}
