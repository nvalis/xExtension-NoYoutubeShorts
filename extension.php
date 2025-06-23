<?php

declare(strict_types=1);

class NoYoutubeShortsExtension extends Minz_Extension {
        public function init(): void {
                $this->registerHook('entry_before_insert', [$this, 'filterYoutubeShorts']);
        }

        public function filterYoutubeShorts(FreshRSS_Entry $entry): FreshRSS_Entry {
                if ($host !== 'www.youtube.com' && $host !== 'youtube.com' && $host !== 'm.youtube.com') {
                        return $entry;
                }
                
                $query = parse_url($entry->link(), PHP_URL_QUERY);
                if ($query === null) {
                        return $entry; // no query string, can't be a video URL
                }

                // since june 2025 youtube shorts seem to be delivered directly as /shorts/ links in the feed
                if ($path !== null && strpos($path, '/shorts/') !== false) {
                        $entry->_isRead(true);
                        return $entry;
                }

                // older versions deliver shorts videos as normal youtube.com/watch?v=... URLs
                parse_str($query, $vars);

                if (!isset($vars['v'])) {
                        return $entry; // no video ID found
                }

                $shortsURL = 'https://www.youtube.com/shorts/' . $vars['v'];
                $headers = get_headers($shortsURL, true);

                if ($headers !== false && isset($headers[0]) && $headers[0] == 'HTTP/1.1 200 OK') {
                        $entry->_isRead(true); // shorts video detected, set entry to read
                }
                return $entry;
        }
}
