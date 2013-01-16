<?php
/**
 * @package Html5Media
 * @copyright Copyright 2012, John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

class Html5MediaPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array('initialize', 'admin_theme_header', 'public_theme_header');

    private $_mediaOptions = array(
        'common' => array('autoplay' => false,
                          'controls' => true,
                          'loop'     => false),
        'audio'  => array(),
        'video'  => array('width'  => '480',
                          'height' => '270')
    );

    private $_mediaSupported = array(
        'audio' => array(
            'mimeTypes' => array('audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/m4a', 'audio/wma'),
            'fileExtensions' => array('mp3', 'm4a', 'wav', 'wma')),
        'video' => array(
            'mimeTypes' => array('video/flv', 'video/x-flv', 'video/mp4', 'video/m4v',
                                 'video/webm', 'video/wmv', 'video/quicktime'),
            'fileExtensions' => array('mp4', 'm4v', 'flv', 'webm', 'wmv')),
        'text' => array(
            'mimeTypes' => array('text/vtt'),
            'fileExtensions' => array('srt', 'vtt'))
    );

    public function hookInitialize()
    {
        $commonOptions = $this->_mediaOptions['common'];
        add_file_display_callback($this->_mediaSupported['audio'],
            'Html5MediaPlugin::audio', $commonOptions + $this->_mediaOptions['audio']);
        add_file_display_callback($this->_mediaSupported['video'],
            'Html5MediaPlugin::video', $commonOptions + $this->_mediaOptions['video']);
        add_file_display_callback($this->_mediaSupported['text'],
            'Html5MediaPlugin::text');
    }

    public function hookAdminThemeHeader()
    {
        $this->_head();
    }

    public function hookPublicThemeHeader()
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
        queue_js('mediaelement-and-player.min', 'mediaelement');
        queue_css('mediaelementplayer', 'screen', false, 'mediaelement');
    }

    private static function _media($type, $file, $options)
    {
        static $i = 0;
        $i++;

        $mediaOptions = '';

        if (isset($options['width']))
            $mediaOptions .= ' width="' . $options['width'] . '"';
        if (isset($options['height']))
            $mediaOptions .= ' height="' . $options['height'] . '"';
        if ($options['autoplay'])
            $mediaOptions .= ' autoplay';
        if ($options['controls'])
            $mediaOptions .= ' controls';
        if ($options['loop'])
            $mediaOptions .= ' loop';

        $filename = html_escape($file->getWebPath('archive'));

        $tracks = '';
        foreach (self::_findTextTrackFiles($file) as $textFile) {
            $kind = item_file('Dublin Core', 'Type', array(), $textFile);
            $language = item_file('Dublin Core', 'Language', array(), $textFile);
            $label = item_file('Dublin Core', 'Title', array(), $textFile);

            if (!$kind) {
                $kind = 'subtitles';
            }

            if (!$language) {
                $language = 'en';
            }

            $trackSrc = html_escape($textFile->getWebPath('archive'));

            if ($label) {
                $labelPart = ' label="' . $label . '"';
            } else {
                $labelPart = '';
            }

            $tracks .= '<track kind="' . $kind . '" src="' . $trackSrc . '" srclang="' . $language . '"' . $labelPart . '>';
        }

        return <<<HTML
<$type id="html5-media-$i" src="$filename"$mediaOptions>
$tracks
</$type>
<script type="text/javascript">
jQuery('#html5-media-$i').mediaelementplayer();
</script>
HTML;
    }

    private static function _findTextTrackFiles($mediaFile)
    {
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
                && ($pathInfo['extension'] == 'srt'
                    || $pathInfo['extension'] == 'vtt')
            ) {
                $trackFiles[] = $file;
            }
        }
        return $trackFiles;
    }
}
