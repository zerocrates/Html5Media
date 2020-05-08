<?php
/**
 * @package Html5Media
 * @copyright Copyright 2012-2015 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

class Html5MediaPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array('initialize', 'admin_head', 'public_head',
        'config', 'config_form', 'install', 'uninstall', 'upgrade');

    public function hookInstall()
    {
        $defaults = array(
            'video' => array(
                'options' => array(
                    'width' => 480,
                    'height' => 270,
                    'responsive' => false
                ),
                'types' => array(
                    'video/flv', 'video/x-flv', 'video/mp4', 'video/m4v',
                    'video/webm', 'video/wmv', 'video/quicktime'
                ),
                'extensions' => array('mp4', 'm4v', 'flv', 'webm', 'wmv'),
            ),
            'audio' => array(
                'options' => array(
                    'width' => 400,
                    'responsive' => false
                ),
                'types' => array(
                    'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav',
                    'audio/m4a', 'audio/wma', 'audio/mp4'
                ),
                'extensions' => array('mp3', 'm4a', 'wav', 'wma'),
            ),
            'text' => array(
                'types' => array('text/vtt'),
                'extensions' => array('srt', 'vtt')
            ),
            'common' => array(
                'options' => array(
                    'autoplay' => false,
                    'controls' => true,
                    'loop'     => false,
                    'preload'  => 'metadata',
                    'download' => false,
                )
            )
        );
        set_option('html5_media_settings', serialize($defaults));
    }

    public function hookUninstall()
    {
        delete_option('html5_media_settings');
    }

    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        if (version_compare($oldVersion, '1.1', '<')) {
            $this->hookInstall();
        }
        $settings = unserialize(get_option('html5_media_settings'));
        if (version_compare($oldVersion, '2.1', '<')) {
            $settings['audio']['options']['width'] = 400;
        }
        if (version_compare($oldVersion, '2.2', '<')) {
            $settings['video']['options']['responsive'] = false;
        }
        if (version_compare($oldVersion, '2.5', '<')) {
            $settings['common']['options']['preload'] = 'metadata';
            if(!in_array('audio/mp4', $settings['audio']['types'])) {
                $settings['audio']['types'][] = 'audio/mp4';
            }
            if (!in_array('audio/x-wav', $settings['audio']['types'])) {
                $settings['audio']['types'][] = 'audio/x-wav';
            }
        }
        if (version_compare($oldVersion, '2.6', '<')) {
            $settings['audio']['options']['responsive'] = false;
        }
        if (version_compare($oldVersion, '2.7', '<')) {
            $settings['common']['options']['download'] = false;
        }
        set_option('html5_media_settings', serialize($settings));
    }

    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');

        $settings = unserialize(get_option('html5_media_settings'));
        $commonOptions = $settings['common']['options'];
        add_file_display_callback(array(
            'mimeTypes' => $settings['audio']['types'],
            'fileExtensions' => $settings['audio']['extensions']
            ), 'Html5MediaPlugin::audio',
            $commonOptions + $settings['audio']['options']);
        add_file_display_callback(array(
            'mimeTypes' => $settings['video']['types'],
            'fileExtensions' => $settings['video']['extensions']
            ), 'Html5MediaPlugin::video',
            $commonOptions + $settings['video']['options']);
        add_file_display_callback(array(
            'mimeTypes' => $settings['text']['types'],
            'fileExtensions' => $settings['text']['extensions']
            ), 'Html5MediaPlugin::text');
    }

    public function hookConfigForm()
    {
        $settings = unserialize(get_option('html5_media_settings'));
        
        $audio = $settings['audio'];
        $audio['types'] = implode(',', $audio['types']);
        $audio['extensions'] = implode(',', $audio['extensions']);
        
        $video = $settings['video'];
        $video['types'] = implode(',', $video['types']);
        $video['extensions'] = implode(',', $video['extensions']);
        
        $text = $settings['text'];
        $text['types'] = implode(',', $text['types']);
        $text['extensions'] = implode(',', $text['extensions']);

        $common = $settings['common'];
        
        include 'config-form.php';
    }

    public function hookConfig()
    {
        $settings = unserialize(get_option('html5_media_settings'));
        
        $audio = $_POST['audio'];
        $settings['audio']['options']['width'] = (int) $audio['options']['width'];
        $settings['audio']['options']['responsive'] = (bool) $audio['options']['responsive'];
        $settings['audio']['types'] = explode(',', $audio['types']);
        $settings['audio']['extensions'] = explode(',', $audio['extensions']);

        $video = $_POST['video'];
        $settings['video']['options']['width'] = (int) $video['options']['width'];
        $settings['video']['options']['height'] = (int) $video['options']['height'];
        $settings['video']['options']['responsive'] = (bool) $video['options']['responsive'];
        $settings['video']['types'] = explode(',', $video['types']);
        $settings['video']['extensions'] = explode(',', $video['extensions']);

        $text = $_POST['text'];
        $settings['text']['types'] = explode(',', $text['types']);
        $settings['text']['extensions'] = explode(',', $text['extensions']);

        $common = $_POST['common'];
        $settings['common']['options']['preload'] = $common['options']['preload'];
        $settings['common']['options']['download'] = (bool) $common['options']['download'];

        set_option('html5_media_settings', serialize($settings));
    }

    public function hookAdminHead()
    {
        $this->_head();
    }

    public function hookPublicHead()
    {
        $this->_head();
    }

    public static function audio($file, $options)
    {
        return self::_media('audio', $file, $options);
    }

    public static function video($file, $options)
    {
        return self::_media('video', $file, $options);
    }

    public static function text($file, $options)
    {
        return null;
    }

    private function _head()
    {
        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $l10n = array(
                'language' => get_html_lang(),
                'strings' => array(
                    'mejs.download-file' => __('Download File'),
                    'mejs.play' => __('Play'),
                    'mejs.pause' => __('Pause'),
                    'mejs.mute-toggle' => __('Mute Toggle'),
                    'mejs.fullscreen' => __('Fullscreen'),
                    'mejs.captions-subtitles' => __('Captions/Subtitles'),
                    'mejs.none' => __('None'),
                    'mejs.fullscreen-off' => __('Turn off Fullscreen'),
                    'mejs.fullscreen-on' => __('Go Fullscreen'),
                    'mejs.unmute' => __('Unmute'),
                    'mejs.mute' => __('Mute'),
                    'mejs.download-video' => __('Download Video'),
                    'mejs.close' => __('Close')
                )
            );
            $l10nScript = 'mejsL10n = ' . js_escape($l10n) . ';';
            queue_js_string($l10nScript);
        }

        $pluginLoader = Zend_Registry::get('plugin_loader');
        $html5media = $pluginLoader->getPlugin('Html5Media');
        $version = $html5media->getIniVersion();
        queue_js_file('mediaelement-and-player.min', 'mediaelement', array(), $version);
        queue_css_file('mediaelementplayer-legacy.min', 'all', false, 'mediaelement', $version);
        queue_css_file('html5media', 'all', null, 'css', $version);
        if (is_admin_theme()) {
            queue_css_file('html5media-mejs-overrides', 'all', null, 'css', $version);
        }
    }

    private static function _media($type, $file, $options)
    {
        static $i = 0;
        $i++;

        $class = "html5media-player $type";
        $mediaOptions = '';

        if (isset($options['width']))
            $mediaOptions .= ' width="' . $options['width'] . '"';
        if (isset($options['height']))
            $mediaOptions .= ' height="' . $options['height'] . '"';
        if (isset($options['autoplay']) && $options['autoplay'])
            $mediaOptions .= ' autoplay';
        if (isset($options['controls']) && $options['controls'])
            $mediaOptions .= ' controls';
        if (isset($options['loop']) && $options['loop'])
            $mediaOptions .= ' loop';
        if (isset($options['responsive']) && $options['responsive']) {
            $mediaOptions .= ' style="width:100%;height:100%"';
            $class .= ' responsive';
        }
        if (isset($options['preload'])) {
            $mediaOptions .= ' preload="' . html_escape($options['preload']). '"';
        }
        if ($type === 'video' && $file->has_derivative_image) {
            $posterUrl = $file->getWebPath('fullsize');
            $mediaOptions .= ' poster="' . html_escape($posterUrl) . '"';
        }
        if ($type === 'video' && $file->mime_type === 'video/quicktime') {
            $mediaOptions .= ' type="video/mp4"';
        }


        $filename = html_escape($file->getWebPath('original'));

        $tracks = '';
        foreach (self::_findTextTrackFiles($file) as $textFile) {
            $kind = metadata($textFile, array('Dublin Core', 'Type'));
            $language = metadata($textFile, array('Dublin Core', 'Language'));
            $label = metadata($textFile, array('Dublin Core', 'Title'));

            if (!$kind) {
                $kind = 'subtitles';
            }

            if (!$language) {
                $language = 'en';
            }

            $trackSrc = html_escape($textFile->getWebPath('original'));

            if ($label) {
                $labelPart = ' label="' . $label . '"';
            } else {
                $labelPart = '';
            }

            $tracks .= '<track kind="' . $kind . '" src="' . $trackSrc . '" srclang="' . $language . '"' . $labelPart . '>';
        }

        if ($options['download']) {
            $download = '<p class="html5media-download"><a href="' . $filename . '" download>'
                . __('Download File')
                . '</a></p>';
        } else {
            $download = '';
        }

        return <<<HTML
<div class="$class">
<$type id="html5-media-$i" src="$filename"$mediaOptions>
$tracks
</$type>
$download
</div>
<script type="text/javascript">
jQuery('#html5-media-$i').mediaelementplayer({classPrefix: 'mejs-'});
</script>
HTML;
    }

    private static function _findTextTrackFiles($mediaFile)
    {
        $settings = unserialize(get_option('html5_media_settings'));
        $extensions = $settings['text']['extensions'];

        $item = $mediaFile->getItem();
        $mediaName = pathinfo($mediaFile->original_filename,
            PATHINFO_FILENAME);

        $trackFiles = array();
        foreach ($item->Files as $file) {
            if ($file->id == $mediaFile->id) {
                continue;
            }
            $pathInfo = pathinfo($file->original_filename);
            if ($pathInfo['filename'] == $mediaName
                && isset($pathInfo['extension'])
                && in_array($pathInfo['extension'], $extensions)
            ) {
                $trackFiles[] = $file;
            }
        }
        return $trackFiles;
    }
}
