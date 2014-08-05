<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 05.08.14
 * Time: 13:53
 */
/**
 * Player that creates HTML5 <audio> tag.
 *
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class directlink_core_media_player_html5audio extends core_media_player {
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
class directlink_core_media_player_html5video extends core_media_player {
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