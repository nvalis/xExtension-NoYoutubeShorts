<?php

declare(strict_types=1);

class NoYoutubeShortsExtension extends Minz_Extension {
        public function init(): void {
                $this->registerHook('entry_before_insert', [$this, 'filterYoutubeShorts']);
        }

        public function filterYoutubeShorts(FreshRSS_Entry $entry): FreshRSS_Entry {
                if (parse_url($entry->link(), PHP_URL_HOST) != 'www.youtube.com') {
                        return $entry; // we are only interested in youtube URLs
                }

                $query = parse_url($entry->link(), PHP_URL_QUERY);
                if ($query === null) {
                        return $entry; // no query string, can't be a video URL
                }

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
