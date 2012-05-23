<?php
/**
 * CustomPostAssist - Custom posts with good fields, without spaghetti
 *
 * Copyright (C) 2012 Jake Teton-Landis <just.1.jake@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package CustomPostAssist
 */

/**
 * JTL_Field.php
 *
 * Abstract class.
 * Draws and saves custom fields for Wordpress custom post types
 * See JTL_CustomPostType for usage examples
 */
class JTL_Field
{
    public $name;

    public function Rectify_Post_Id($post_id = null) {
        if ($post_id === null) {
            global $the_post;
            if (isset($the_post))
                $post_id = $the_post->ID;
        }
        if (is_object($post_id))
            $post_id = $post_id->ID;

        return $post_id;
    }


    public function __construct($name) {
        $this->name = $name;
    }


    /**
     * Draw the form fields and associated HTML content for this field
     * @param $post
     */
    public function draw($post = null, $no_wrapper = false) {
        if (! $no_wrapper) $this->draw_header($post);
        $this->draw_fields($post);
        if (! $no_wrapper) $this->draw_footer($post);
    }

    /**
     * Save the form data. Security is handled by JTL_CustomPostType::save_fields
     * @param null $post_id
     */
    public function save($post_id = null) {}

    /**
     * return the value of this field
     */
    public function get($post_id = null) {
        $post_id = JTL_Field::Rectify_Post_Id($post_id);
        return get_post_meta($post_id, $this->name, true);
    }

    /**
     * Print a suitable displayable output for this field
     */
    public function display($post_id = null) {
        echo $this->get($post_id);
    }

    /**
     * Each field type reimplements the private drawing funcitons as needed
     * @param null $post
     */
    protected function draw_fields($post) {}

    /**
     * Draw the static HTML section header for this field
     * @param $post
     */
    protected  function draw_header($post) {
        echo "<p>\n";
    }
    /**
     * Draw the static HTML section footer for this field
     * @param $post
     */
    protected function draw_footer($post) {
        echo "</p>\n";
    }
}


class JTL_SimpleField extends JTL_Field{
    public $label = 'Simple Form Field';

    public function __construct($name) {
        parent::__construct($name);
        $this->input_name = esc_attr($this->name . '_input');
    }

    protected function draw_label($post_id) {
        ?><label for="<?php echo $this->input_name ?>">
            <?php echo esc_attr($this->label); ?>
        </label><?php
    }

    public function draw_input($post_id) {
        $current_data = esc_attr($this->get($post_id));
        printf('<input type="text" id="%s" name="%s" value="%s" />',
                $this->input_name,
                $this->input_name,
                $current_data
        );
    }

    protected function draw_fields($post = null) {
        $post_id = $this->Rectify_Post_Id($post);

        // print label
        $this->draw_label($post_id);

        // print imput
        $this->draw_input($post_id);
    }

    public function save($post_id = null) {
       update_post_meta($post_id, $this->name, $_POST[esc_attr($this->input_name)]);
    }
}

class JTL_DateField extends JTL_SimpleField {
    public $label = 'Date';
    public function draw_input($post_id) {
        $current_data = esc_attr($this->get($post_id));
        printf('<input type="date" id="%s" name="%s" value="%s" />',
                $this->input_name,
                $this->input_name,
                $current_data
        );
    }
}

class JTL_DateRange extends JTL_Field {
    public function __construct($name) {
        parent::__construct($name);
        $this->start = new JTL_DateField($name . '_start');
        $this->start->label = 'Start: ';
        $this->end = new JTL_DateField($name . '_end');
        $this->end->label = 'End: ';
    }

    public function save($post_id = null) {
        $post_id = $this->Rectify_Post_Id($post_id);
        $this->start->save($post_id);
        $this->end->save($post_id);
    }

    public function draw($post = null, $no_wrapper = false) {
        if (! $no_wrapper) $this->draw_header($post);
        $this->start->draw($post, true);
        $this->end->draw($post, true);
        if (! $no_wrapper) $this->draw_footer($post);
    }

    protected function draw_header($post) {
        printf('<p><strong>%s</strong><br />', esc_attr($this->label));
    }

    public function get($post_id = null) {
        return array(
            'start' => $this->start->get($post_id),
            'end' => $this->end->get($post_id)
        );
    }

}


class JTL_HiddenField extends JTL_Field {
    protected function draw_fields($post_id) {
        $current_data = esc_attr($this->get($post_id));
        printf('<input type="hidden" id="%s" name="%s" value="%s" />',
               $this->input_name,
               $this->input_name,
               $current_data
        );
    }
    protected  function draw_label($post_id) {} // do nothing: hidden field needs no label
}



class JTL_MediaAttatchment extends JTL_HiddenField {

    public $button_id = "";

    public function __construct($name) {
        parent::__construct($name);
        $this->button_id = $this->name . '_button';
        add_action('admin_print_styles', array(&$this, 'enqueue_admin_styles'));
        add_action('admin_print_scripts', array(&$this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('jquery');
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style('thickbox');
    }

    protected function draw_label($post) {
        // TODO draw a link to the current media
    }

    protected function draw_input($post_id) {
        parent::draw_input($post_id); // hidden field printed
        // TODO draw a "click to choose new image" button
    }

    protected  function draw_footer($post) {
        // TODO print a script that shows the media box
        ?>
        <script type="text/javascript">
            jQuery(function($){
                "hello worst practices ahoy";
                var field_id  = '<?php echo esc_attr($this->input_name); ?>';
                var button_id = '<?php echo esc_attr($this->button_id); ?>';


                $(button_id).release(function(e){
                    e.preventDefault();
                    tb_show("", "media-upload.php?type=image&TB_iframe=true");

                    // this is the thickbox/media upload "choose" action
                    // we will be swizzling it, so we need a reference to the original
                    var _send_to_editor = window.send_to_editor;

                    // showing the thing, time to swizzle
                    window.send_to_editor = function(html) {
                        var media_url = $('img', html).attr('src');
                        $(field_id).val(media_url);
                        tb_remove();

                        // unswizzle
                        window.send_to_editor = _send_to_editor;
                    }
                });
            });
        </script>
        <?php
        parent::draw_footer($post);
    }
}