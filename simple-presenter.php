<?php
/*
Plugin Name: Simple Presenter
Description: A simple way to manage presentation screens (AKA: Digital Signage)
Version: 1.4.1
Author: Sylvia van Os
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Simple Presenter is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Simple Presenter is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Simple Presenter. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

if (!defined('WPINC')) {
    die;
}

if (is_admin()) {
    add_action('admin_menu', 'simplepresenter_add_menu');
    add_action('admin_init', 'simplepresenter_admin_init');
    add_action("admin_enqueue_scripts", "simplepresenter_enqueue_media_uploader");
}
add_action('parse_request', 'simplepresenter_admin_parse_request');

function simplepresenter_options_page() {
?>
<div>
  <div class="wrap">
    <h1>Simple Presenter</h1>

    <?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'screens'; ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=simplepresenter-plugin&tab=screens" class="nav-tab <?php echo $active_tab == 'screens' ? 'nav-tab-active' : ''; ?>">Screens</a>
        <a href="?page=simplepresenter-plugin&tab=calendars" class="nav-tab <?php echo $active_tab == 'calendars' ? 'nav-tab-active' : ''; ?>">Calendars</a>
        <a href="?page=simplepresenter-plugin&tab=extraslides" class="nav-tab <?php echo $active_tab == 'extraslides' ? 'nav-tab-active' : ''; ?>">Extra Slides</a>
    </h2>

    <form action="options.php" method="post">
      <input type='hidden' name='simplepresenter_options[_active_tab]' value='<?php echo $active_tab ?>' />
      <?php
      settings_fields("simplepresenter_options");
      switch ($active_tab) {
          case "screens":
              do_settings_sections("simplepresenter_screen");
              break;
          case "calendars":
              do_settings_sections("simplepresenter_calendar");
              break;
          case "extraslides":
              do_settings_sections("simplepresenter_extraslides");
              break;
          default:
              echo "<p>Something went wrong</p>";
      }
      submit_button();
      ?>
    </form>
  </div>
</div>
<?php
}

function simplepresenter_enqueue_media_uploader()
{
    wp_enqueue_media();
}

function simplepresenter_add_menu() {
    add_options_page('Simple Presenter', 'Simple Presenter', 'manage_options', 'simplepresenter-plugin', 'simplepresenter_options_page');
}

function simplepresenter_admin_init() {
    $options = get_option('simplepresenter_options', array());

    register_setting('simplepresenter_options', 'simplepresenter_options', 'simplepresenter_options_validate');

    add_settings_section('simplepresenter_screen', 'Screen Settings', 'simplepresenter_screen_text', 'simplepresenter_screen');
    $number = 0;
    foreach (array_keys($options) as $option) {
        if (preg_match('/^screen_(\d+)$/', $option) && !empty($options[$option])) {
            $number = $number + 1;
            add_settings_field(sprintf('screen_%d', $number), sprintf('Screen %d', $number), 'simplepresenter_setting_screen', 'simplepresenter_screen', 'simplepresenter_screen', array('name' => sprintf('screen_%d', $number), 'value' => $options[$option], 'image_value' => $options[$option . '_image_url']));
            add_settings_field(sprintf('screen_%d_text_scale', $number), sprintf('Screen %d text size scale', $number), 'simplepresenter_print_setting_html', 'simplepresenter_screen', 'simplepresenter_screen', array('name' => sprintf('screen_%d_text_scale', $number), 'value' => $options[$option . '_text_scale'] ? $options[$option . '_text_scale'] : "1", 'type' => 'range', 'extra' => 'min="0.1" max="2" step="0.1"'));
            add_settings_field(sprintf('screen_%d_background_color', $number), sprintf('Screen %d background color', $number), 'simplepresenter_print_colorselect_button_html', 'simplepresenter_screen', 'simplepresenter_screen', array('name' => sprintf('screen_%d_background_color', $number), 'value' => $options[$option . '_background_color'] ? $options[$option . '_background_color'] : "#ffffff"));
            add_settings_field(sprintf('screen_%d_text_color', $number), sprintf('Screen %d text color', $number), 'simplepresenter_print_colorselect_button_html', 'simplepresenter_screen', 'simplepresenter_screen', array('name' => sprintf('screen_%d_text_color', $number), 'value' => $options[$option . '_text_color']));
            add_settings_field(sprintf('view_screen_%d_button', $number), sprintf('View screen %d', $number), 'simplepresenter_print_viewscreen_button_html', 'simplepresenter_screen', 'simplepresenter_screen', array('name' => sprintf('screen_%d_view', $number), 'value' => $options[$option]));
            add_settings_field(sprintf('delete_screen_%d_button', $number), sprintf('Delete screen %d', $number), 'simplepresenter_print_deletefield_button_html', 'simplepresenter_screen', 'simplepresenter_screen', sprintf('screen_%d', $number));
            add_settings_field(sprintf('screen_%d_horizontal_line', $number), '', 'simplepresenter_print_hr', 'simplepresenter_screen', 'simplepresenter_screen', sprintf('screen_%d_horizontal_line', $number));
        }
    }
    add_settings_field('add_screen_button', 'Add another screen', 'simplepresenter_print_createfield_button_html', 'simplepresenter_screen', 'simplepresenter_screen', 'screen');

    add_settings_section('simplepresenter_calendar', 'Calendar Settings', 'simplepresenter_calendar_text', 'simplepresenter_calendar');
    $number = 0;
    foreach (array_keys($options) as $option) {
        if (preg_match('/^calendar_url_(\d+)$/', $option) && !empty($options[$option])) {
            $number = $number + 1;
            add_settings_field(sprintf('calendar_url_%d', $number), sprintf('Calendar URL %d', $number), 'simplepresenter_setting_calendar_url', 'simplepresenter_calendar', 'simplepresenter_calendar', array('name' => sprintf('calendar_url_%d', $number), 'value' => $options[$option]));
            add_settings_field(sprintf('calendar_url_%d_type', $number), sprintf('Calendar URL %d type', $number), 'simplepresenter_setting_calendar_url_type', 'simplepresenter_calendar', 'simplepresenter_calendar', array('name' => sprintf('calendar_url_%d_type', $number), 'value' => $options[$option . '_type']));
            add_settings_field(sprintf('calendar_url_%d_extra_settings', $number), sprintf('Calendar URL %d extra settings', $number), 'simplepresenter_setting_calendar_url_extra_settings', 'simplepresenter_calendar', 'simplepresenter_calendar', array('name' => sprintf('calendar_url_%d_extra_settings', $number), 'value' => $options[$option . '_extra_settings']));
            add_settings_field(sprintf('calendar_url_%d_showon', $number), sprintf('Show calendar URL %d on...', $number), 'simplepresenter_setting_calendar_url_showon', 'simplepresenter_calendar', 'simplepresenter_calendar', array('name' => sprintf('calendar_url_%d_showon', $number), 'value' => $options[$option . '_showon']));
            add_settings_field(sprintf('delete_calendar_%d_button', $number), sprintf('Delete calendar URL %d', $number), 'simplepresenter_print_deletefield_button_html', 'simplepresenter_calendar', 'simplepresenter_calendar', sprintf('calendar_url_%d', $number));
            add_settings_field(sprintf('calendar_url_%d_horizontal_line', $number), '', 'simplepresenter_print_hr', 'simplepresenter_calendar', 'simplepresenter_calendar', sprintf('calendar_url_%d_horizontal_line', $number));
        }
    }
    add_settings_field('add_calendar_url_button', 'Add another calendar', 'simplepresenter_print_createfield_button_html', 'simplepresenter_calendar', 'simplepresenter_calendar', 'calendar_url');

    add_settings_section('simplepresenter_extraslides', 'Extra slides', 'simplepresenter_extraslides_text', 'simplepresenter_extraslides');

    $number = 0;
    foreach (array_keys($options) as $option) {
        if (preg_match('/^extraslides_(\d+)$/', $option) && !(empty($options[$option]) && empty($options[sprintf('%s_image_url', $option)]))) {
            $number = $number + 1;
            add_settings_field(sprintf('extraslides_%d', $number), sprintf('Extra slide %d', $number), 'simplepresenter_setting_extraslides', 'simplepresenter_extraslides', 'simplepresenter_extraslides', array('name' => sprintf('extraslides_%d', $number), 'value' => $options[$option], 'image_value' => $options[$option . '_image_url']));
            add_settings_field(sprintf('extraslides_%d_showon', $number), sprintf('Show extra slide %d on...', $number), 'simplepresenter_setting_extraslides_showon', 'simplepresenter_extraslides', 'simplepresenter_extraslides', array('name' => sprintf('extraslides_%d_showon', $number), 'value' => $options[$option . '_showon']));
            add_settings_field(sprintf('extraslides_%d_displaytime', $number), sprintf('Show extra slide %d for...', $number), 'simplepresenter_setting_extraslides_displaytime', 'simplepresenter_extraslides', 'simplepresenter_extraslides', array('name' => sprintf('extraslides_%d_displaytime', $number), 'value' => $options[$option . '_displaytime']));
            add_settings_field(sprintf('delete_extraslide_%d_button', $number), sprintf('Delete extra slide %d', $number), 'simplepresenter_print_deletefield_button_html', 'simplepresenter_extraslides', 'simplepresenter_extraslides', sprintf('extraslides_%d', $number));
            add_settings_field(sprintf('extraslides_%d_horizontal_line', $number), '', 'simplepresenter_print_hr', 'simplepresenter_extraslides', 'simplepresenter_extraslides', sprintf('extraslides_%d_horizontal_line', $number));
        }
    }
    add_settings_field('add_extraslides_url_button', 'Add another slide', 'simplepresenter_print_createfield_button_html', 'simplepresenter_extraslides', 'simplepresenter_extraslides', 'extraslides');
}

function simplepresenter_print_createfield_button_html($type) {
    echo "<input id='simplepresenter_addanotherbutton_" . $type . "' type='button' class='button' value='Add another' />";
?>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#<?php echo "simplepresenter_addanotherbutton_" . $type ?>').click(function(e) {
            e.preventDefault();
            var existingids = $('[id^="simplepresenter_<?php echo $type ?>_"]').filter(function(i) { return $(this).attr('id').match(/\d+$/)});
            var nextnumber = existingids.length ? parseInt($(existingids).last().attr('id').match(/\d+$/)) + 1 : 1;
            $.get("<?php echo get_site_url() ?>/", {simplepresenteradmin: 'true', type: '<?php echo $type ?>', number: nextnumber}).done(function(data) {
                $(data).insertBefore($(e.target).parent().parent());
            });
        });
    });
</script>
<?php
}

function simplepresenter_print_hr($name) {
    echo "<hr id='simplepresenter_" . $name . "'>";
}

function simplepresenter_print_colorselect_button_html($arguments) {
    echo "<input id='simplepresenter_" . $arguments['name'] . "' name='simplepresenter_options[" . $arguments['name'] . "]' type='color' class='button' value='" . $arguments['value'] . "' />";
}

function simplepresenter_print_viewscreen_button_html($arguments) {
    $name = $arguments['name'];
    $value = $arguments['value'];
    $url = get_site_url() . '/?simplepresenter=' . $value;
    echo "<a id='simplepresenter_" . $name . "' href='" . $url . "'>" . $url . "</a>";
}

function simplepresenter_print_deletefield_button_html($name) {
    echo "<input id='simplepresenter_deletebutton_" . $name . "' type='button' class='button' value='Delete this entry' />";
?>
<script>
    jQuery(document).ready(function($){
        $('#<?php echo "simplepresenter_deletebutton_" . $name ?>').click(function(e) {
            e.preventDefault();
            $('[id^="simplepresenter_<?php echo $name ?>"]').each(function(i) {
                $(this).parent().parent().remove();
            });
            $(this).parent().parent().remove();
        });
    });
</script>
<?php
}

function simplepresenter_print_setting_html($arguments) {
    $name = $arguments['name'];
    $value = $arguments['value'];
    $type = $arguments['type'];
    $extra = $arguments['extra'];
    echo "<input id='simplepresenter_$name' name='simplepresenter_options[$name]' size='40' type='$type' value='$value' $extra />";
}

function simplepresenter_print_setting_imageupload($name, $value, $image_value) {
    echo "<input id='simplepresenter_" . $name . "_image_url' name='simplepresenter_options[" . $name . "_image_url]' type='hidden' value='$image_value' />";
    echo "<input id='simplepresenter_" . $name . "_upload_image_button' type='button' class='button' value='Upload Image' />";
    echo "<input id='simplepresenter_" . $name . "_remove_image_button' type='button' class='button' value='Remove Image' /><br/>";
    echo "<img id='simplepresenter_" . $name . "_image_preview' style='max-height: 200px' src='$image_value' ><br/>";
?>
<script type="text/javascript">
// Taken from https://github.com/SufferMyJoy/dobsondev-wordpress-media-upload-tester/blob/master/dobsondev-wordpress-media-upload-tester-script.js
// License: GPL-3 (https://github.com/SufferMyJoy/dobsondev-wordpress-media-upload-tester/blob/master/LICENSE)
// Modified to work for us
jQuery(document).ready(function($){

  var mediaUploader;

  $('#<?php echo "simplepresenter_" . $name . "_remove_image_button" ?>').click(function(e) {
    e.preventDefault();
    $('#<?php echo "simplepresenter_" . $name . "_image_url" ?>').removeAttr("value");
    $('#<?php echo "simplepresenter_" . $name . "_image_preview" ?>').removeAttr("src");
  });

  $('#<?php echo "simplepresenter_" . $name . "_upload_image_button" ?>').click(function(e) {
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
      text: 'Choose Image'
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function() {
      var attachment = mediaUploader.state().get('selection').first().toJSON();
      $('#<?php echo "simplepresenter_" . $name . "_image_url" ?>').attr("value", attachment.url);
      $('#<?php echo "simplepresenter_" . $name . "_image_preview" ?>').attr("src", attachment.url);
    });
    // Open the uploader dialog
    mediaUploader.open();
  });

});
</script>
<?php
}

function simplepresenter_print_setting_tinymce($name, $value) {
    echo "<input id='simplepresenter_" . $name . "' type='hidden' />"; // Extra field with the name we want for consistency for jQuery "add another" button
    echo '<p id="simplepresenter_' . $name . '_explanation_text"><strong>The Add Media button will insert HTML into the text field below. If you just want to show an image, make sure the text field is empty and click the "Upload Image" button instead.</strong></p>';
    wp_editor($value, "simplepresenter_options[" . $name . "]", array('textarea_rows' => 10));
    if (empty($value)) {
        echo "<input id='simplepresenter_" . $name . "_enable_editor_button' type='button' class='button' value='Add Custom HTML (Advanced)' />";
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    document.getElementById('<?php echo "wp-simplepresenter_options[" . $name . "]-wrap" ?>').style.display = "none";
                    document.getElementById('<?php echo "simplepresenter_" . $name . "_explanation_text" ?>').style.display = "none";
                    $('#<?php echo "simplepresenter_" . $name . "_enable_editor_button" ?>').click(function(e) {
                        e.preventDefault();
                        document.getElementById('<?php echo "wp-simplepresenter_options[" . $name . "]-wrap" ?>').style.display = "block";
                        document.getElementById('<?php echo "simplepresenter_" . $name . "_explanation_text" ?>').style.display = "block";
                        $(this).remove();
                    });
                });
            </script>
        <?php
    }
}

function simplepresenter_print_setting_showon_html($name, $value) {
    $options = get_option('simplepresenter_options', array());

    $screens = [];
    foreach (array_keys($options) as $option) {
        if (preg_match('/^screen_(\d+)$/', $option) && !empty($options[$option])) {
            $screens[] = $options[$option];
        }
    }
    if (empty($screens)) {
        echo "<strong>No screens yet. Please add at least one screen first.</strong>";
    } else {
        foreach ($screens as $location) {
            echo "<input id='simplepresenter_$name' name='simplepresenter_options[$name][]' size='40' type='checkbox' value='$location' " . (@in_array($location, $value) ? "checked" : "") . "><label>$location</label> ";
        }
    }
}

function simplepresenter_screen_text() {
    echo '<p>Each screen has an individual URL shown on this page and can contain as much or as little of the content as desired.</p>';
}

function simplepresenter_setting_screen($arguments) {
    simplepresenter_print_setting_imageupload($arguments['name'], $arguments['value'], $arguments['image_value']);
    $arguments['type'] = 'text';
    $arguments['extra'] = empty($arguments['value']) ? '' : 'readonly';
    simplepresenter_print_setting_html($arguments);
}

function simplepresenter_calendar_text() {
    echo '<p>Calendar Settings</p>';
}

function simplepresenter_setting_calendar_url($arguments) {
    $arguments['type'] = $url;
    $arguments['extra'] = '';
    simplepresenter_print_setting_html($arguments);
}

function simplepresenter_setting_calendar_url_type($arguments) {
    $name = $arguments['name'];
    $type = $arguments['value'];
    echo "<select id='simplepresenter_$name' name='simplepresenter_options[$name]'>";
    foreach (array("tribe json") as $system) {
        echo "<option value='$system' " . (@in_array($system, $type) ? "selected" : "") . ">$system</option>";
    }
    echo "</select>";
}

function simplepresenter_setting_calendar_url_extra_settings($arguments) {
    $name = $arguments['name'];
    $value = $arguments['value'];
    foreach (array("ignore SSL errors") as $setting) {
        echo "<input id='simplepresenter_$name' name='simplepresenter_options[$name][]' size='40' type='checkbox' value='$setting' " . (in_array($setting, $value) ? "checked" : "") . "><label>$setting</label> ";
    }
}

function simplepresenter_setting_calendar_url_showon($arguments) {
    simplepresenter_print_setting_showon_html($arguments['name'], $arguments['value']);
}

function simplepresenter_extraslides_text() {
    echo '<p>An extra slide can have a lot of different styles. Here are some tricks to keep in mind:</p>';
    echo '<ol>';
    echo '<li>With "Upload Image" you can upload a feature image. This will fill the whole slide, unless custom HTML is added. If custom HTML is added, the feature image will display on the left half of the screen.</li>';
    echo '<li>If no feature image is set, custom HTML will get the space of the whole slide.</li>';
    echo '<li>For custom HTML content that should fill the slide, consider using high width and height values such as 99999. Simple Presenter will ensure it will stay within the slide frame.</li>';
    echo '<li>If you set "allowfullscreen" on any custom HTML element, the first element on the slide will fullscreen when the user presses f.</li>';
    echo '</ol>';
}

function simplepresenter_setting_extraslides($arguments) {
    simplepresenter_print_setting_imageupload($arguments['name'], $arguments['value'], $arguments['image_value']);
    simplepresenter_print_setting_tinymce($arguments['name'], $arguments['value']);
}

function simplepresenter_setting_extraslides_showon($arguments) {
    simplepresenter_print_setting_showon_html($arguments['name'], $arguments['value']);
}

function simplepresenter_setting_extraslides_displaytime($arguments) {
    $arguments['type'] = 'number';
    $arguments['extra'] = 'placeholder="10"';
    simplepresenter_print_setting_html($arguments);
    echo ' seconds';
}

function simplepresenter_options_validate($input) {
    $newinput = array();

    $knownvalues = get_option('simplepresenter_options', array());

    // Add all values from other tabs to prevent data loss
    foreach (array_keys($knownvalues) as $knownvalue) {
        switch($input['_active_tab']) {
            case "screens":
                if (preg_match('/^screen_(\d+)$/', $knownvalue)) {
                    continue 2;
                }
                break;
            case "calendars":
                if (preg_match('/^calendar_url_(\d+)$/', $knownvalue)) {
                    continue 2;
                }
                break;
            case "extraslides":
                if (preg_match('/^extraslides_(\d+)$/', $knownvalue)) {
                    continue 2;
                }
                break;
        }
        $newinput[$knownvalue] = $knownvalues[$knownvalue];
    }

    // Screens
    $number = 0;
    foreach (array_keys($input) as $inputoption) {
        if (preg_match('/^screen_(\d+)$/', $inputoption) && !empty($input[$inputoption])) {
            var_dump($inputoption);
            $number = $number + 1;
            $newinput[sprintf('screen_%d', $number)] = trim($input[$inputoption]);
            $newinput[sprintf('screen_%d_background_color', $number)] = $input[sprintf('%s_background_color', $inputoption)];
            $newinput[sprintf('screen_%d_text_scale', $number)] = $input[sprintf('%s_text_scale', $inputoption)];
            $newinput[sprintf('screen_%d_text_color', $number)] = $input[sprintf('%s_text_color', $inputoption)];
            $newinput[sprintf('screen_%d_image_url', $number)] = $input[sprintf('%s_image_url', $inputoption)];
        }
    }

    // Calendar
    $number = 0;
    foreach (array_keys($input) as $inputoption) {
        if (preg_match('/^calendar_url_(\d+)$/', $inputoption) && !empty($input[$inputoption])) {
            $number = $number + 1;
            $newinput[sprintf('calendar_url_%d', $number)] = trim($input[$inputoption]);
            $newinput[sprintf('calendar_url_%d_type', $number)] = $input[sprintf('%s_type', $inputoption)];
            $newinput[sprintf('calendar_url_%d_extra_settings', $number)] = $input[sprintf('%s_extra_settings', $inputoption)];
            $newinput[sprintf('calendar_url_%d_showon', $number)] = $input[sprintf('%s_showon', $inputoption)];
        }
    }

    // Extra slides
    $number = 0;
    foreach (array_keys($input) as $inputoption) {
        if (preg_match('/^extraslides_(\d+)$/', $inputoption) && !(empty($input[$inputoption]) && empty($input[sprintf('%s_image_url', $inputoption)]))) {
            $number = $number + 1;
            $newinput[sprintf('extraslides_%d', $number)] = $input[$inputoption];
            $newinput[sprintf('extraslides_%d_image_url', $number)] = $input[sprintf('%s_image_url', $inputoption)];
            $newinput[sprintf('extraslides_%d_showon', $number)] = $input[sprintf('%s_showon', $inputoption)];
            $newinput[sprintf('extraslides_%d_displaytime', $number)] = $input[sprintf('%s_displaytime', $inputoption)];
        }
    }

    // Save
    return $newinput;
}

function simplepresenter_admin_parse_request($wp) {
    if (array_key_exists('simplepresenteradmin', $wp->query_vars)) {
        $fieldtype = $wp->query_vars['type'];
        $number = $wp->query_vars['number'];

        switch ($fieldtype) {
            case 'screen':
                echo "<tr><th scope='row'>Screen $number</th><td>";
                simplepresenter_setting_screen(array('name' => sprintf('screen_%d', $number), 'value' => ''));
                echo "</td></tr>";
                echo "<th scope='row'>Screen $number text scale</th><td>";
                simplepresenter_print_setting_html(array('name' => sprintf('screen_%d_text_scale', $number), 'value' => $options[$option . '_text_scale'] ? $options[$option . '_text_scale'] : "1", 'type' => 'range', 'extra' => 'min="0.1" max="2" step="0.1"'));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Screen $number background color</th><td>";
                simplepresenter_print_colorselect_button_html(array('name' => sprintf('screen_%d_background_color', $number), 'value' => '#ffffff'));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Screen $number text color</th><td>";
                simplepresenter_print_colorselect_button_html(array('name' => sprintf('screen_%d_text_color', $number), 'value' => '#000000'));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Delete screen $number</th><td>";
                simplepresenter_print_deletefield_button_html(sprintf('screen_%d', $number));
                echo "</td></tr>";
                echo "<tr><th></th><td>";
                simplepresenter_print_hr(sprintf('screen_%d_horizontal_line', $number));
                echo "</td></tr>";
                break;
            case 'calendar_url':
                echo "<tr><th scope='row'>Calendar URL $number</th><td>";
                simplepresenter_setting_calendar_url(array('name' => sprintf('calendar_url_%d', $number), 'value' => ''));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Calendar URL $number type</th><td>";
                simplepresenter_setting_calendar_url_type(array('name' => sprintf('calendar_url_%d_type', $number), 'value' => ''));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Calendar URL $number extra settings</th><td>";
                simplepresenter_setting_calendar_url_extra_settings(array('name' => sprintf('calendar_url_%d_extra_settings', $number), 'value' => array()));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Show calendar URL $number on...</th><td>";
                simplepresenter_setting_calendar_url_showon(array('name' => sprintf('calendar_url_%d_showon', $number), 'value' => array()));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Delete calendar URL $number</th><td>";
                simplepresenter_print_deletefield_button_html(sprintf('calendar_url_%d', $number));
                echo "</td></tr>";
                echo "<tr><th></th><td>";
                simplepresenter_print_hr(sprintf('calendar_url_%d_horizontal_line', $number));
                echo "</td></tr>";
                break;
            case 'extraslides':
                echo "<tr><th scope='row'>Extra slide $number</th><td>";
                simplepresenter_setting_extraslides(array('name' => sprintf('extraslides_%d', $number), 'value' => '', 'image_value' => ''));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Show extra slide $number on...</th><td>";
                simplepresenter_setting_extraslides_showon(array('name' => sprintf('extraslides_%d_showon', $number), 'value' => array()));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Show extra slide $number for...</th><td>";
                simplepresenter_setting_extraslides_displaytime(array('name' => sprintf('extraslides_%d_displaytime', $number), 'value' => array()));
                echo "</td></tr>";
                echo "<tr><th scope='row'>Delete extra slide $number</th><td>";
                simplepresenter_print_deletefield_button_html(sprintf('extraslides_%d', $number));
                echo "</td></tr>";
                echo "<tr><th></th><td>";
                simplepresenter_print_hr(sprintf('extraslides_%d_horizontal_line', $number));
                echo "</td></tr>";
                break;
        }

        exit();
    }
}

function simplepresenter_admin_query_vars($vars) {
    $vars[] = 'simplepresenteradmin';
    $vars[] = 'type';
    $vars[] = 'number';
    return $vars;
}

add_filter('query_vars', 'simplepresenter_admin_query_vars');

// Public part
function simplepresenter_calendar_tribejson_format_date($details, $compareto = array(), $hidetime = false) {
    if (!array_key_exists('year', $compareto)) {
        $compareto['year'] = date("Y");
    }

    if (!array_key_exists('month', $compareto)) {
        $compareto['month'] = date("m");
    }

    if (!array_key_exists('day', $compareto)) {
        $compareto['day'] = date("d");
    }

    if ($details['year'] == $compareto['year']) {
        if ($details['month'] == $compareto['month'] && $details['day'] == $compareto['day']) {
            if ($hidetime) {
                return "";
            }
            return sprintf("%s:%s", $details['hour'], $details['minutes']);
        } else {
            if ($hidetime) {
                return sprintf("%s %d", DateTime::createFromFormat('!m', $details['month'])->format("M"), ltrim($details['day'], '0'));
            }
            return sprintf("%s %d %s:%s", DateTime::createFromFormat('!m', $details['month'])->format("M"), ltrim($details['day'], '0'), $details['hour'], $details['minutes']);
        }
    } else {
        if ($hidetime) {
            return sprintf("%s %d %s", DateTime::createFromFormat('!m', $details['month'])->format("M"), ltrim($details['day'], '0'), $details['year']);
        }
        return sprintf("%s %d %s %s:%s", DateTime::createFromFormat('!m', $details['month'])->format("M"), ltrim($details['day'], '0'), $details['year'], $details['hour'], $details['minutes']);
    }
}

function simplepresenter_public_parse_request($wp) {
    if (array_key_exists('simplepresenter', $wp->query_vars)) {
        $options = get_option('simplepresenter_options', array());

        $location = $wp->query_vars['simplepresenter'];
        $screen_option = "";
        foreach (array_keys($options) as $option) {
            if (preg_match('/^screen_(\d+)$/', $option) && !empty($options[$option])) {
                if ($options[$option] == $location) {
                    $screen_option = $option;
                    break;
                }
            }
        }

        if (empty($screen_option)) {
            $slides = ["<h1>" . $location . "</h1><p><strong>Screen does not exist.</strong></p><p><small>If you believe this is an error, please contact your Simple Presenter administrator.</small></p>"];
        } else {
            $slides = [];

            foreach (array_keys($options) as $option) {
                if (isset($options[$option . "_showon"]) && in_array($location, $options[$option . "_showon"])) {
                    if (preg_match('/^calendar_url_(\d+)$/', $option)) {
                        if ($options[$option . "_type"] != "tribe json") {
                            // Nothing else than Tribe calendars supported yet
                            continue;
                        }

                        if (@in_array('ignore SSL errors', $options[$option . "_extra_settings"])) {
                            $arrContextOptions = array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                ),
                            );
                        } else {
                            $arrContextOptions = array();
                        }

                        $json = file_get_contents($options[$option], false, stream_context_create($arrContextOptions));
                        $data = json_decode($json, true);
                        for ($i = 0; $i < 5; $i++) {
                            if (!isset($data['events'][$i])) {
                                 break;
                            }

                            $event = $data['events'][$i];

                            $categories = [];
                            foreach ($event['categories'] as $category) {
                                $categories[] = $category['name'];
                            }

                            $fromtime = simplepresenter_calendar_tribejson_format_date($event['start_date_details'], array(), $event['all_day']);
                            $totime = simplepresenter_calendar_tribejson_format_date($event['end_date_details'], $event['start_date_details'], $event['all_day']);
                            if ($fromtime == $totime) {
                                $timestring = $fromtime;
                            } else {
                                $timestring = sprintf("%s - %s", $fromtime, $totime);
                            }

                            $slides[] = "<h1>" . implode("</br>", $categories) . "</h1><p>" . $timestring . ($event['venue']['venue'] ? " | " : " ") . $event['venue']['venue'] . "</p><p><em>" . $event['title'] . "</em></p><p>" . $event['excerpt'] . "</p>";
                        }
                    } else if (preg_match('/^extraslides_(\d+)$/', $option)) {
                        $slide = "<div data-time='" . $options[$option . "_displaytime"] . "'>";
                        if ($options[$option . "_image_url"]) {
                            $slide = $slide . "<img class='feature_image " . (empty($options[$option]) ? 'fullpage' : '') . "' src='" . $options[$option . "_image_url"] . "'/>";
                        }
                        $slide = $slide . $options[$option] . "</div>";
                        $slides[] = $slide;
                    }
                }
            }

            if (empty($slides)) {
                $slides = ["<h1>" . $location . "</h1><p><strong>There are no messages to display.</strong></p><p><small>If you believe this is an error, please contact your Simple Presenter administrator.</small></p>"];
            }
        }
?>

        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="UTF-8">
        </head>
        <body>

        <div id='simplepresenter_logo'></div>

        <?php foreach ($slides as $slide) { ?>
            <div class='simplepresenter_slide'><?php echo apply_filters('the_content', $slide) ?></div>
        <?php } ?>

        <style>
        html {
            width: 100%;
            height: 100%;
            position: relative;
            font-size: <?php echo ($options[$screen_option . "_text_scale"] ? $options[$screen_option . "_text_scale"] : 1) * 250 ?>%;
            font-family: Verdana, Geneva, sans-serif;
            background-color: <?php echo $options[$screen_option . "_background_color"] ?>;
            color: <?php echo $options[$screen_option . "_text_color"] ?>;
        }
        #simplepresenter_logo {
            z-index: 999;
            background: url(<?php echo $options[$screen_option . "_image_url"] ?>);
            background-repeat: no-repeat;
            background-size: contain;
            height: 15%;
            width: 15%;
            position: absolute;
            top: 3%;
            right: 3%;
        }
        .simplepresenter_slide {
            display: none;
            height: 75%;
            width: 75%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 10px 10px;
            border-radius: 20px 20px 20px 20px;
            overflow: hidden;
        }
        .simplepresenter_slide div {
            width: 100%;
            height: 100%;
        }
        .simplepresenter_slide * {
            max-width: 100%;
            max-height: 100%;
        }
        .simplepresenter_slide:not(img) {
            text-align: left;
        }
        .simplepresenter_slide img.feature_image {
            object-fit: contain;
            float: left;
            max-width: 49%;
            height: 100%;
            padding-right: 2%;
        }
        .simplepresenter_slide img.fullpage {
            float: left;
            height: 100%;
            width: 100%;
            max-width: 100%;
            padding-right: 0%;
            margin: auto;
        }
        </style>

        <script>
        var currentSlideId = 0;
        var slideLength = 10000;
        var nextSlideTimer = null;

        next_slide();

        document.addEventListener('keydown', function (e) {
            switch (e.keyCode) {
                case 37:
                    if (currentSlideId <= 1) {
                        currentSlideId = document.getElementsByClassName("simplepresenter_slide").length - 1;
                    } else {
                        currentSlideId -= 2;
                    }
                    queue_next_slide(1);
                    break;

                case 39:
                    if (currentSlideId >= document.getElementsByClassName("simplepresenter_slide").length) {
                        currentSlideId = 0;
                    }
                    queue_next_slide(1);
                    break;

                case 70:
                    fullscreen_slide_content();
                    break;

                default:
                    return;
            }
        });

        function queue_next_slide(force_time) {
            clearTimeout(nextSlideTimer);
            nextSlideTimer = setTimeout(next_slide, force_time ? force_time : slideLength);
        }

        function fullscreen_slide_content() {
            var slides = document.getElementsByClassName("simplepresenter_slide");
            var currentSlide = slides[currentSlideId - 1];
            var element = currentSlide.querySelector("*[allowfullscreen]")
            if (element) {
                if (element.requestFullScreen) {
                    element.requestFullScreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.webkitRequestFullScreen) {
                    element.webkitRequestFullScreen();
                }
            }
        }

        function next_slide() {
            var slides = document.getElementsByClassName("simplepresenter_slide");

            if (currentSlideId >= slides.length) {
                window.location.reload();
                return;
            }

            for (var i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
                var frames = slides[i].getElementsByTagName("iframe");
                for (var j = 0; j < frames.length; j++) {
                   frames[j].contentWindow.location.href = "about:blank";
                }
            }

            var currentSlide = slides[currentSlideId];
            currentSlide.style.display = "inline-block";

            currentSlideId++;

            var frames = currentSlide.getElementsByTagName("iframe");
            for (var i = 0; i < frames.length; i++) {
                frames[i].contentWindow.location.href = frames[i].src;
            }

            var customSlideTime = currentSlide.children[0].getAttribute("data-time");
            queue_next_slide(customSlideTime ? (customSlideTime * 1000) : null);

            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
        </script>

        </body>
        </html>
<?php
        exit();
    }
}
add_action('parse_request', 'simplepresenter_public_parse_request');

function simplepresenter_public_query_vars($vars) {
    $vars[] = 'simplepresenter';
    return $vars;
}
add_filter('query_vars', 'simplepresenter_public_query_vars');

