<?php
namespace App\Service;

use App\Logger\Logger;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubApiService
{
    private $httpClient;
    private $cache;
    
    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }
    
    public function getLanguagesFromGitHubApi(): array
    {
        return $this->cache->get('languages', function(CacheItemInterface $cacheItem) {
            $cacheItem->expiresAfter(86400);
            $response = $this->httpClient->request('GET', "https://api.github.com/repos/".$_ENV['GITHUB_USER']."/".$_ENV['GITHUB_REPONAME']."/languages", [
                'headers' => [
                    'Authorization' => "Bearer ".$_ENV['GITHUB_TOKEN'],
                    'Accept' => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28'
                ],
            ]);

            $languages = $response->toArray();
            $sum = array_sum($languages);
            $languagesInPercent = [];

            foreach ($languages as $key => $value) {
                $languagesInPercent[$key] = round(($value / $sum) * 100, 0);
            }

            return $languagesInPercent;
        });
    }

    public function getFullCommitsData(): array
    {
        return $this->cache->get('commit_data', function (CacheItemInterface $cacheItem) {
            $cacheItem->expiresAfter(86400);
        
            $allCommits = [];
            $page = 1;
            $perPage = 100;
        
            do {
                $response = $this->httpClient->request('GET', "https://api.github.com/repos/".$_ENV['GITHUB_USER']."/".$_ENV['GITHUB_REPONAME']."/commits?per_page=$perPage&page=$page", [
                    'headers' => [
                        'Authorization' => "Bearer ".$_ENV['GITHUB_TOKEN'],
                        'Accept' => 'application/vnd.github+json',
                        'X-GitHub-Api-Version' => '2022-11-28'
                    ],
                ]);
        
                $commits = $response->toArray();

                $allCommits = array_merge($allCommits, $commits);
                
                $page++;
            } while (!empty($commits));
        
            return $allCommits;
        });
    }

    public function getCommitsAndDate(): array
    {
        $allCommits = $this->getFullCommitsData();
        $commitsByDate = [];

        foreach ($allCommits as $commit) {
            $date = (new \DateTime($commit['commit']['author']['date']))->format('d.m.Y');
            if (!isset($commitsByDate[$date])) {
                $commitsByDate[$date] = 0;
            }
            $commitsByDate[$date]++;
        }

        $commitDates = implode(', ', array_reverse(array_keys($commitsByDate)));
        $commitsPerDay = implode(', ', array_reverse(array_values($commitsByDate)));

        return ['commitDates' => $commitDates, 'commitsPerDay' => $commitsPerDay];
    }

    public function getCommitsAndMessage(): array
    {
        $allCommits = $this->getFullCommitsData();
        
        $commitMessages = [];

        foreach ($allCommits as $commit) {
            $commitMessages[$commit['commit']['author']['date']] = $commit['commit']['message'];
        }

        return $commitMessages ;
    }
}