<div id="html5-media-video-settings">
<h2><?php echo __('Video Settings'); ?></h2>
    <div class="field">
        <?php echo __v()->formLabel('video[options][width]', __('Width')); ?>
        <div class="inputs">
            <?php echo __v()->formText('video[options][width]', $video['options']['width']); ?>
        </div>
    </div>
    <div class="field">
        <?php echo __v()->formLabel('video[options][height]', __('Height')); ?>
        <div class="inputs">
            <?php echo __v()->formText('video[options][height]', $video['options']['height']); ?>
        </div>
    </div>
    <div class="field">
        <?php echo __v()->formLabel('video[types]', __('MIME Types')); ?>
        <div class="inputs">
            <?php echo __v()->formTextarea('video[types]', $video['types'], array('rows' => 3, 'cols' => 50)); ?>
        </div>
    </div>
    <div class="field">
        <?php echo __v()->formLabel('video[extensions]', __('Extensions')); ?>
        <div class="inputs">
            <?php echo __v()->formTextarea('video[extensions]', $video['extensions'], array('rows' => 3, 'cols' => 50)); ?>
        </div>
    </div>
</div>
<div id="html5-media-audio-settings">
    <h2><?php echo __('Audio Settings'); ?></h2>
    <div class="field">
        <?php echo __v()->formLabel('audio[types]', __('MIME Types')); ?>
        <div class="inputs">
            <?php echo __v()->formTextarea('audio[types]', $audio['types'], array('rows' => 3, 'cols' => 50)); ?>
        </div>
    </div>
    <div class="field">
        <?php echo __v()->formLabel('audio[extensions]', __('Extensions')); ?>
        <div class="inputs">
            <?php echo __v()->formTextarea('audio[extensions]', $audio['extensions'], array('rows' => 3, 'cols' => 50)); ?>
        </div>
    </div>
</div>
<div id="html5-media-text-settings">
    <h2><?php echo __('Text Settings'); ?></h2>
    <p class="explanation">
    Text files are data like subtitles and chapter names. HTML5 Media will
    include them as tracks of audio or video files with the same original
    filename on the same item.
    </p>
    <div class="field">
        <?php echo __v()->formLabel('text[types]', __('MIME Types')); ?>
        <div class="inputs">
            <?php echo __v()->formTextarea('text[types]', $text['types'], array('rows' => 3, 'cols' => 50)); ?>
        </div>
    </div>
    <div class="field">
        <?php echo __v()->formLabel('text[extensions]', __('Extensions')); ?>
        <div class="inputs">
            <?php echo __v()->formTextarea('text[extensions]', $text['extensions'], array('rows' => 3, 'cols' => 50)); ?>
        </div>
    </div>
</div>
