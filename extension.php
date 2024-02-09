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
		parse_str(parse_url($entry->link(), PHP_URL_QUERY), $vars);
		$shortsURL = 'https://www.youtube.com/shorts/' . $vars['v'];
		$headers = get_headers($shortsURL, true);

		if ($headers[0] == 'HTTP/1.1 200 OK') {
			$entry->_isRead(true); // shorts video detected, set entry to read
			return $entry;
		} elseif ($headers[0] == 'HTTP/1.1 303 See Other') {
			if ($headers['Location'] == 'https://www.youtube.com/watch?v=' . $videoID) {
				return $entry; // not a shorts video, just forward the entry unchanged
			}
		}
		return $entry; // should normally not be reached, just in case
	}
}
