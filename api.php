<?php
require 'vendor/autoload.php';

include 'SqliteService.php';
include 'UrlHelper.php';

class Quotes {

    const QUOTES_MOCK = 'quotes.json';
    const QUOTES_FROM_URL = 1;
    public $quotes;

    public function fetch($url) {
        $client = new GuzzleHttp\Client();
        try {
            $res = $client->request('GET', $url);
        } catch (Exception $exception) {
            throw new Exception('It wasn\'t possible fetch data: ' . $exception->getMessage());
        }
        if ($res->getStatusCode() === 200) {
            return json_decode($res->getBody());
        }
    }

    public function fetchMock() {
        if (!file_exists(self::QUOTES_MOCK)) {
            throw new Exception('Mock file ' . self::QUOTES_MOCK . ' doesn\'t exist');
        }
        $quotes = json_decode(file_get_contents(__DIR__ . '/' . self::QUOTES_MOCK));
        $this->quotes = isset($quotes->quotes) ? $quotes->quotes : [];
    }

    public function setQuotes($from, $url = '') {
        switch($from) {
            case self::QUOTES_FROM_URL:
                $this->quotes = $this->fetch($url);
                break;
            default:
                $this->fetchMock();
        }
    }

    public function filterQuotes($quotesLimit, $author) {
        $quotes = [];
        foreach ($this->quotes as $quote) {
            $quoteAuthor = trim(str_replace(' ', '-', strtolower($quote->author)));
            if ($quoteAuthor === $author) {
                if (count($quotes) < $quotesLimit) {
                    $quotes[] = $this->formatQuote($quote);
                }
            }
        }
        $this->quotes = $quotes;
    }

    public function formatQuote($quote) {
        $upperQuote = mb_strtoupper($quote->quote);
        if (substr($upperQuote, -1) !== '!') {
            if (substr($upperQuote, -1) === '.') {
                $upperQuote = substr($upperQuote, 0, strlen($upperQuote)-1); // Remove ending dot
            }
            $upperQuote .= '!'; // Append exclamation mark
        }
        return ['quote' => $upperQuote, 'author' => $quote->author];
    }

    public function responseQuotes() {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($this->quotes);
    }

}

$url = new UrlHelper();
$quotesLimit = (integer)$url->getVar('limit');
$requestUri = $_SERVER['REQUEST_URI'];

if ($quotesLimit && $quotesLimit >= 1 && $quotesLimit <= 10) {
    $quotesService = new Quotes();
    $author = array_pop($url->segments);

    echo "<pre>";
    // Just for testing purposes, use this uid, in real cases it should be a client api key
    $uid = md5($_SERVER['HTTP_USER_AGENT'] .  $_SERVER['REMOTE_ADDR']);

    $db = new SqliteService();
    // $db->rebuildTables();
    if ($cachedQuotes = $db->getLastUserConnection($uid, $requestUri)) { // Get cached quotes
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($cachedQuotes);

    } else { // If no cached quotes get from a source (mock, api, etc)
        $quotesService->setQuotes('mock');
        $quotesService->filterQuotes($quotesLimit, $author);

        $thisRequest = $db->registerRequest($uid, $requestUri);
        $db->saveRequestQuotes($quotesService->quotes, $thisRequest);
        $quotesService->responseQuotes();
    }

    //  Change this for use a real URL
    //  $quotesService->setQuotes(Quotes::QUOTES_FROM_URL, 'https://jsonplaceholder.typicode.com/posts');

    // Since data came from source save it in cache


} else {
    throw new Exception('Quotes limit has to be between 1 and 10');
}
