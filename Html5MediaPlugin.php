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
                                 'video/webm', 'video/wmv'),
            'fileExtensions' => array('mp4', 'm4v', 'flv', 'webm', 'wmv'))
    );

    public function hookInitialize()
    {
        $commonOptions = $this->_mediaOptions['common'];
        add_file_display_callback($this->_mediaSupported['audio'],
            'Html5MediaPlugin::audio', $commonOptions + $this->_mediaOptions['audio']);
        add_file_display_callback($this->_mediaSupported['video'],
            'Html5MediaPlugin::video', $commonOptions + $this->_mediaOptions['video']);
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

        return <<<HTML
<$type id="html5-media-$i" src="$filename"$mediaOptions></$type>
<script type="text/javascript">
jQuery('#html5-media-$i').mediaelementplayer();
</script>
HTML;
    }
}
