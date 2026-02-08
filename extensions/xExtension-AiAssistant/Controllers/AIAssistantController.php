<?php

require_once __DIR__ . '/../AIHelper.php';

class FreshExtension_AIAssistant_Controller extends Minz_ActionController
{
    private $config;
    private $entryDAO;
    private $feedDAO;

    public function __construct()
    {
        parent::__construct();

        $conf = Minz_Configuration::get('system');
        $this->config = (object) [
            'openai_baseurl_article' => $conf->openai_baseurl_article ?? 'https://api.openai.com/v1',
            'openai_key_article'     => $conf->openai_key_article     ?? 'YOUR_OPENAI_KEY',
            'openai_model_article'   => $conf->openai_model_article   ?? 'gpt-4o-mini',
            'openai_temp_article'    => $conf->openai_temp_article    ?? '0.7',
            'openai_prompt_article'  => $conf->openai_prompt_article  ??
                "You are an impartial news reporter. Return JSON {\"title\":\"...\",\"summary\":\"...\",\"tags\":\"...\"}\nArticle:\n{{ARTICLE}}",

            'openai_baseurl_roundup' => $conf->openai_baseurl_roundup ?? 'https://api.openai.com/v1',
            'openai_key_roundup'     => $conf->openai_key_roundup     ?? 'YOUR_OPENAI_KEY',
            'openai_model_roundup'   => $conf->openai_model_roundup   ?? 'gpt-4o-mini',
            'openai_temp_roundup'    => $conf->openai_temp_roundup    ?? '0.7',
            'openai_prompt_roundup'  => $conf->openai_prompt_roundup  ?? "Summarize these articles:\n{{ARTICLES}}",

            'openai_max_tokens'      => (int)($conf->openai_max_tokens ?? 4096),
            'mark_read_after_summary'=> $conf->mark_read_after_summary ?? '0'
        ];

        $this->entryDAO = FreshRSS_Factory::createEntryDao();
        $this->feedDAO  = FreshRSS_Factory::createFeedDao();
    }

    public function summaryAction()
    {
        Minz_Log::debug("AIAssistant: Entered summaryAction()");
        Minz_View::_title('AI Assistant Summary');
        $this->view->_param('rss_title', 'AI Summary');
        $this->view->_param('html_url', Minz_Url::display('index.php'));

        $catId = (int)Minz_Request::param('cat_id', 0);
        $state = FreshRSS_Entry::STATE_NOT_READ;
        $limit = 50;

        $articles = $this->fetchUnreadArticles($catId, $state, $limit);

        if (empty($articles)) {
            Minz_Log::warning("AIAssistant: No unread articles to summarize.");
            $this->view->_param('summaryText', "[No unread articles found]");
            $this->view->_param('articleCount', 0);
            $this->view->_param('articleIds', []);
        } else {
            Minz_Log::debug("AIAssistant: Retrieved " . count($articles) . " unread articles.");
            $this->view->_param('summaryText', "[AI Summary Goes Here]");
            $this->view->_param('articleCount', count($articles));
            $this->view->_param('articleIds', array_map(fn($a) => $a['id'], $articles));
        }

        Minz_Log::debug("AIAssistant: Retrieved " . count($articles) . " unread articles.");

        $textBlock = $this->buildContentFromArticles($articles);

        $maxTokens = $this->config->openai_max_tokens;

        Minz_Log::debug("AIAssistant: Sending text block of length " . strlen($textBlock) . " to AI.");

        $aiData = AIHelper::generateArticleData(
            $this->config->openai_baseurl_roundup,
            $this->config->openai_key_roundup,
            $this->config->openai_model_roundup,
            (float)$this->config->openai_temp_roundup,
            "Summarize these unread articles.",
            str_replace('{{ARTICLES}}', $textBlock, $this->config->openai_prompt_roundup),
            $maxTokens
        );

        $summaryText = $aiData['summary'] ?? "[Error: AI did not return summary]";

        Minz_Log::debug("AIAssistant: Summary received, length " . strlen($summaryText));

        $this->view->_param('summaryText', is_string($summaryText) ? $summaryText : "[No summary generated]");
        $this->view->_param('articleCount', is_array($articles) ? count($articles) : 0);
	$this->view->_param('articleIds', is_array($articles) ? array_map(fn($a) => $a['id'], $articles) : []);
	$this->view->_param('markReadEnabled', ($this->config->mark_read_after_summary ?? '0') === '1');
	$this->view->_path('aiassistant/summary.phtml');
	$this->view->attributeParams();

	Minz_Log::debug("AIAssistant: summaryAction() completed successfully.");
    }


    public function apiSummarizeAction()
    {
        header('Content-Type: application/json; charset=UTF-8');

        if (!FreshRSS_Auth::hasAccess()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid authentication.']);
            die();
        }

        $catId   = (int)Minz_Request::param('cat_id', 0);
        $state   = FreshRSS_Entry::STATE_NOT_READ;
        $articles= $this->fetchUnreadArticles($catId, $state, 50);

        if (empty($articles)) {
            Minz_Log::warning("AIAssistant: No unread articles found for category [$catId].");
            $this->view->summaryText = "[No unread articles found]";
            $this->view->articleCount = 0;
	    $this->view->articleIds = [];
	    echo json_encode(['status' => 'error', 'message' => "No unread articles found for category [$catId]."]);
	    die();
        }

        $maxTokens = $this->config->openai_max_tokens;

        $combined = $this->buildContentFromArticles($articles);
        $aiData   = AIHelper::generateArticleData(
            $this->config->openai_baseurl_roundup,
            $this->config->openai_key_roundup,
            $this->config->openai_model_roundup,
            (float)$this->config->openai_temp_roundup,
            "Summarize these unread articles (API).",
            str_replace('{{ARTICLES}}', $combined, $this->config->openai_prompt_roundup),
            $maxTokens
        );

        $summary = $aiData['summary'] ?? "[Error: no summary returned]";

        if ($this->config->mark_read_after_summary === '1') {
            $this->markArticlesAsRead($articles);
        }

        echo json_encode([
            'status'  => 'success',
            'summary' => $summary
	]);
	die();
    }

    private function fetchUnreadArticles(int $catId, int $state, int $limit): array
    {
        $username = Minz_User::name();
        if (!$username) {
            Minz_Log::warning("AIAssistant: No user context available.");
            return [];
        }

        $unreadArticles = [];
        $articleIds     = [];

        Minz_Log::debug("AIAssistant: Fetching unread articles for user [$username], category [$catId]...");

	$feedsInCategory = [];
        foreach ($this->feedDAO->listFeeds() as $feed) {
            if ($catId === 0 || $feed->category()->id() == $catId) {
                $feedsInCategory[] = $feed->id();
            }
        }

        if (empty($feedsInCategory)) {
            Minz_Log::warning("AIAssistant: No feeds found for category [$catId]");
            return [];
        }

	foreach ($feedsInCategory as $feedId) {
	    $feed = FreshRSS_Factory::createFeedDao()->listFeeds()[$feedId] ?? null;

            if ($feed) {
                $nbUnread = $feed->nbNotRead();
            } else {
                Minz_Log::warning("AIAssistant: Could not find feed with ID [$feedId].");
                return [];
	    }

            if ($nbUnread > 0) {
                Minz_Log::debug("AIAssistant: Found $nbUnread unread articles in feed $feedId");
                $feedUnreadIds = $this->entryDAO->listIdsWhere('f', $feedId, FreshRSS_Entry::STATE_NOT_READ, limit: min($nbUnread, $limit));
                $articleIds = array_merge($articleIds, $feedUnreadIds);
            }
        }

        if (empty($articleIds)) {
            Minz_Log::warning("AIAssistant: No unread article IDs found.");
            return [];
        }

        Minz_Log::debug("AIAssistant: Fetching full articles for " . count($articleIds) . " unread items...");

        foreach ($this->entryDAO->listByIds($articleIds) as $entry) {
            $unreadArticles[] = $entry->toArray();
        }

        Minz_Log::debug("AIAssistant: Retrieved " . count($unreadArticles) . " unread articles.");

        return $unreadArticles;
    }

    private function markArticlesAsRead(array $articles): void
    {
        if (empty($articles)) {
            Minz_Log::warning("AIAssistant: No articles to mark as read.");
            return;
        }

        $dao = FreshRSS_Factory::createEntryDao();
        $articleIds = array_map(fn($a) => $a['id'], $articles);

        Minz_Log::debug("AIAssistant: Marking " . count($articleIds) . " articles as read...");

        $dao->markRead(array_unique($articleIds));

        Minz_Log::debug("AIAssistant: Successfully marked articles as read.");
    }

    private function buildContentFromArticles(array $articles): string
    {
        $lines = [];
        foreach ($articles as $a) {
            $t = rtrim($a['title'] ?? "[No title]");
            if (!preg_match('/[.!?]$/', $t)) {
                $t .= '.';
            }
            $lines[] = $t;
        }
        return implode("\n", $lines);
    }

    public function getFileUrl(string $filename, string $type): string
    {
        $dirName  = basename(dirname(__DIR__));
        $fileEnc  = urlencode($dirName."/static/$filename");
        $mtime    = @filemtime(dirname(__DIR__)."/static/$filename");
        return Minz_Url::display("/ext.php?f=$fileEnc&amp;t=$type&amp;$mtime", 'php');
    }
}
