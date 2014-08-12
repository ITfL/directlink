<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 05.08.14
 * Time: 13:53
 */

/**
 * Base class for media players.
 *
 * Media players return embed HTML for a particular way of playing back audio
 * or video (or another file type).
 *
 * In order to make the code more lightweight, this is not a plugin type
 * (players cannot have their own settings, database tables, capabilities, etc).
 * These classes are used only by core_media_renderer in outputrenderers.php.
 * If you add a new class here (in core code) you must modify the
 * get_players_raw function in that file to include it.
 *
 * If a Moodle installation wishes to add extra player objects they can do so
 * by overriding that renderer in theme, and overriding the get_players_raw
 * function. The new player class should then of course be defined within the
 * custom theme or other suitable location, not in this file.
 *
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class directlink_core_media_player {
    /**
     * Placeholder text used to indicate where the fallback content is placed
     * within a result.
     */
    const PLACEHOLDER = '<!--FALLBACK-->';

    /**
     * Generates code required to embed the player.
     *
     * The returned code contains a placeholder comment '<!--FALLBACK-->'
     * (constant core_media_player::PLACEHOLDER) which indicates the location
     * where fallback content should be placed in the event that this type of
     * player is not supported by user browser.
     *
     * The $urls parameter includes one or more alternative media formats that
     * are supported by this player. It does not include formats that aren't
     * supported (see list_supported_urls).
     *
     * The $options array contains key-value pairs. See OPTION_xx constants
     * for documentation of standard option(s).
     *
     * @param array $urls URLs of media files
     * @param string $name Display name; '' to use default
     * @param int $width Optional width; 0 to use default
     * @param int $height Optional height; 0 to use default
     * @param array $options Options array
     * @return string HTML code for embed
     */
    public abstract function embed($urls, $name, $width, $height, $options, $mime);

    /**
     * Gets the list of file extensions supported by this media player.
     *
     * Note: This is only required for the default implementation of
     * list_supported_urls. If you override that function to determine
     * supported URLs in some way other than by extension, then this function
     * is not necessary.
     *
     * @return array Array of strings (extension not including dot e.g. 'mp3')
     */
    public function get_supported_extensions() {
        return array();
    }

    /**
     * Lists keywords that must be included in a url that can be embedded with
     * this player. Any such keywords should be added to the array.
     *
     * For example if this player supports FLV and F4V files then it should add
     * '.flv' and '.f4v' to the array. (The check is not case-sensitive.)
     *
     * Default handling calls the get_supported_extensions function and adds
     * a dot to each of those values, so players only need to override this
     * if they don't implement get_supported_extensions.
     *
     * This is used to improve performance when matching links in the media filter.
     *
     * @return array Array of keywords to add to the embeddable markers list
     */
    public function get_embeddable_markers() {
        $markers = array();
        foreach ($this->get_supported_extensions() as $extension) {
            $markers[] = '.' . $extension;
        }
        return $markers;
    }

    /**
     * Gets the ranking of this player. This is an integer used to decide which
     * player to use (after applying other considerations such as which ones
     * the user has disabled).
     *
     * Rank must be unique (no two players should have the same rank).
     *
     * Rank zero has a special meaning, indicating that this 'player' does not
     * really embed the video.
     *
     * Rank is not a user-configurable value because it needs to be defined
     * carefully in order to ensure that the embedding fallbacks actually work.
     * It might be possible to have some user options which affect rank, but
     * these would be best defined as e.g. checkboxes in settings that have
     * a particular effect on the rank of a couple of plugins, rather than
     * letting users generally alter rank.
     *
     * Note: Within medialib.php, players are listed in rank order (highest
     * rank first).
     *
     * @return int Rank (higher is better)
     */
    public abstract function get_rank();

    /**
     * @return bool True if player is enabled
     */
    public function is_enabled() {
        global $CFG;

        // With the class core_media_player_html5video it is enabled
        // based on $CFG->core_media_enable_html5video.
        $setting = str_replace('_player_', '_enable_', get_class($this));
        return !empty($CFG->{$setting});
    }

    /**
     * Given a list of URLs, returns a reduced array containing only those URLs
     * which are supported by this player. (Empty if none.)
     * @param array $urls Array of moodle_url
     * @param array $options Options (same as will be passed to embed)
     * @return array Array of supported moodle_url
     */
    public function list_supported_urls(array $urls, array $options = array()) {
        $extensions = $this->get_supported_extensions();
        $result = array();
        foreach ($urls as $url) {
            if (in_array(core_media::get_extension($url), $extensions)) {
                $result[] = $url;
            }
        }
        return $result;
    }

    /**
     * Obtains suitable name for media. Uses specified name if there is one,
     * otherwise makes one up.
     * @param string $name User-specified name ('' if none)
     * @param array $urls Array of moodle_url used to make up name
     * @return string Name
     */
    protected function get_name($name, $urls) {
        // If there is a specified name, use that.
        if ($name) {
            return $name;
        }

        // Get filename of first URL.
        $url = reset($urls);
        $name = core_media::get_filename($url);

        // If there is more than one url, strip the extension as we could be
        // referring to a different one or several at once.
        if (count($urls) > 1) {
            $name = preg_replace('~\.[^.]*$~', '', $name);
        }

        return $name;
    }

    /**
     * Compares by rank order, highest first. Used for sort functions.
     * @param core_media_player $a Player A
     * @param core_media_player $b Player B
     * @return int Negative if A should go before B, positive for vice versa
     */
    public static function compare_by_rank(core_media_player $a, core_media_player $b) {
        return $b->get_rank() - $a->get_rank();
    }

    /**
     * Utility function that sets width and height to defaults if not specified
     * as a parameter to the function (will be specified either if, (a) the calling
     * code passed it, or (b) the URL included it).
     * @param int $width Width passed to function (updated with final value)
     * @param int $height Height passed to function (updated with final value)
     */
    protected static function pick_video_size(&$width, &$height) {
        if (!$width) {
            $width = CORE_MEDIA_VIDEO_WIDTH;
            $height = CORE_MEDIA_VIDEO_HEIGHT;
        }
    }
}


/**
 * Player that creates HTML5 <audio> tag.
 *
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class directlink_core_media_player_html5audio extends directlink_core_media_player {
    public function embed($urls, $name, $width, $height, $options, $forcemime='') {

        // Build array of source tags.
        $sources = array();
        foreach ($urls as $url) {
            $mimetype = core_media::get_mimetype($url);
            if ($forcemime) {
                $mimetype = $forcemime;
            }
            $sources[] = html_writer::tag('source', '', array('src' => $url, 'type' => $mimetype));
        }

        $sources = implode("\n", $sources);
        $title = s($this->get_name($name, $urls));

        // Default to not specify size (so it can be changed in css).
        $size = '';
        if ($width) {
            $size = 'width="' . $width . '"';
        }

        $fallback = core_media_player::PLACEHOLDER;

        return <<<OET
<audio controls="true" $size class="mediaplugin mediaplugin_html5audio" preload="no" title="$title">
$sources
$fallback
</audio>
OET;
    }

    public function get_supported_extensions() {
        return array('ogg', 'oga', 'aac', 'm4a', 'mp3');
    }

    public function list_supported_urls(array $urls, array $options = array()) {
        $extensions = $this->get_supported_extensions();
        $result = array();
        foreach ($urls as $url) {
            $ext = core_media::get_extension($url);
            if (in_array($ext, $extensions)) {
                if ($ext === 'ogg' || $ext === 'oga') {
                    // Formats .ogg and .oga are not supported in IE or Safari.
                    if (core_useragent::is_ie() || core_useragent::is_safari()) {
                        continue;
                    }
                } else {
                    // Formats .aac, .mp3, and .m4a are not supported in Firefox or Opera.
                    if (core_useragent::is_firefox() || core_useragent::is_opera()) {
                        continue;
                    }
                }
                // Old Android versions (pre 2.3.3) 'support' audio tag but no codecs.
                if (core_useragent::is_webkit_android() &&
                    !core_useragent::is_webkit_android('533.1')) {
                    continue;
                }

                $result[] = $url;
            }
        }
        return $result;
    }

    public function get_rank() {
        return 10;
    }
}

/**
 * Player that creates HTML5 <video> tag.
 *
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class directlink_core_media_player_html5video extends directlink_core_media_player {
    public function embed($urls, $name, $width, $height, $options, $forcemime='') {
        // Special handling to make videos play on Android devices pre 2.3.
        // Note: I tested and 2.3.3 (in emulator) works without, is 533.1 webkit.
        $oldandroid = core_useragent::is_webkit_android() &&
            !core_useragent::check_webkit_android_version('533.1');

        // Build array of source tags.
        $sources = array();
        foreach ($urls as $url) {
            $mimetype = core_media::get_mimetype($url);
            if ($forcemime) {
                $mimetype = $forcemime;
            }
            $source = html_writer::tag('source', '', array('src' => $url, 'type' => $mimetype));
            if ($mimetype === 'video/mp4') {
                if ($oldandroid) {
                    // Old Android fails if you specify the type param.
                    $source = html_writer::tag('source', '', array('src' => $url));
                }

                // Better add m4v as first source, it might be a bit more
                // compatible with problematic browsers.
                array_unshift($sources, $source);
            } else {
                $sources[] = $source;
            }
        }

        $sources = implode("\n", $sources);
        $title = s($this->get_name($name, $urls));

        if (!$width) {
            // No width specified, use system default.
            $width = CORE_MEDIA_VIDEO_WIDTH;
        }

        if (!$height) {
            // Let browser choose height automatically.
            $size = "width=\"$width\"";
        } else {
            $size = "width=\"$width\" height=\"$height\"";
        }

        $sillyscript = '';
        $idtag = '';
        if ($oldandroid) {
            // Old Android does not support 'controls' option.
            $id = 'core_media_html5v_' . md5(time() . '_' . rand());
            $idtag = 'id="' . $id . '"';
            $sillyscript = <<<OET
<script type="text/javascript">
document.getElementById('$id').addEventListener('click', function() {
    this.play();
}, false);
</script>
OET;
        }

        $fallback = core_media_player::PLACEHOLDER;
        return <<<OET
<span class="mediaplugin mediaplugin_html5video">
<video $idtag controls="true" $size preload="metadata" title="$title">
    $sources
    $fallback
</video>
$sillyscript
</span>
OET;
    }

    public function get_supported_extensions() {
        return array('m4v', 'webm', 'ogv', 'mp4');
    }

    public function list_supported_urls(array $urls, array $options = array()) {
        $extensions = $this->get_supported_extensions();
        $result = array();
        foreach ($urls as $url) {
            $ext = core_media::get_extension($url);
            if (in_array($ext, $extensions)) {
                // Unfortunately html5 video does not handle fallback properly.
                // https://www.w3.org/Bugs/Public/show_bug.cgi?id=10975
                // That means we need to do browser detect and not use html5 on
                // browsers which do not support the given type, otherwise users
                // will not even see the fallback link.
                // Based on http://en.wikipedia.org/wiki/HTML5_video#Table - this
                // is a simplified version, does not take into account old browser
                // versions or manual plugins.
                if ($ext === 'ogv' || $ext === 'webm') {
                    // Formats .ogv and .webm are not supported in IE or Safari.
                    if (core_useragent::is_ie() || core_useragent::is_safari()) {
                        continue;
                    }
                } else {
                    // Formats .m4v and .mp4 are not supported in Firefox or Opera.
                    if (core_useragent::is_firefox() || core_useragent::is_opera()) {
                        continue;
                    }
                }

                $result[] = $url;
            }
        }
        return $result;
    }

    public function get_rank() {
        return 20;
    }
}


class directlink_core_media_player_link extends directlink_core_media_player {
    public function embed($urls, $name, $width, $height, $options, $mime) {
        // If link is turned off, return empty.
        if (!empty($options[core_media::OPTION_NO_LINK])) {
            return '';
        }

        // Build up link content.
        $output = '';
        foreach ($urls as $url) {

            $url .= "&forcedownload=1";
            $url = new moodle_url($url);

            $title = core_media::get_filename($url);
            $printlink = html_writer::link( $url, "Download " . $name, array('class' => 'mediafallbacklink'));
            if ($output) {
                // Where there are multiple available formats, there are fallback links
                // for all formats, separated by /.
                $output .= ' / ';
            }
            $output .= $printlink;
        }
        return $output;
    }

    public function list_supported_urls(array $urls, array $options = array()) {
        // Supports all URLs.
        return $urls;
    }

    public function is_enabled() {
        // Cannot be disabled.
        return true;
    }

    public function get_rank() {
        return 0;
    }
}


/**
 * Flash video player inserted using JavaScript.
 *
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class directlink_core_media_player_flv extends core_media_player {
    public function embed($urls, $name, $width, $height, $options, $mimetype='video/flv') {
        // Use first url (there can actually be only one unless some idiot
        // enters two mp3 files as alternatives).
        $url = reset($urls);

        // Unique id even across different http requests made at the same time
        // (for AJAX, iframes).
        $id = 'core_media_flv_' . md5(time() . '_' . rand());

        // Compute width and height.
        $autosize = false;
        if (!$width && !$height) {
            $width = CORE_MEDIA_VIDEO_WIDTH;
            $height = CORE_MEDIA_VIDEO_HEIGHT;
            $autosize = true;
        }

        // Fallback span (will normally contain link).
        $output = html_writer::tag('span', core_media_player::PLACEHOLDER,
            array('id'=>$id, 'class'=>'mediaplugin mediaplugin_flv'));
        // We can not use standard JS init because this may be cached.
        $output .= html_writer::script(js_writer::function_call(
            'M.util.add_video_player', array($id, addslashes_js($url->out(false)),
            $width, $height, $autosize)));
        return $output;
    }

    public function get_supported_extensions() {
        return array('flv', 'f4v');
    }

    public function get_rank() {
        return 70;
    }
}