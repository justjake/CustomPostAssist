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
abstract class JTL_Field
{
    public $name;
    protected $parent;
    protected $input_name;

    public static function Rectify_Post_Id($post_id = null) {
        if ($post_id === null) {
            global $the_post;
            if (isset($the_post))
                $post_id = $the_post->ID;
        }
        if (is_object($post_id))
            $post_id = $post_id->ID;

        return $post_id;
    }

    public function set_parent($parent) {
        $this->parent = $parent;
    }

    public function __construct($name) {
        $this->name = $name;
        $this->input_name = esc_attr($this->name . '_input');
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


    // HOOKS
    public function register_hooks() {}
}


class JTL_SimpleField extends JTL_Field{
    public $label = 'Simple Form Field';

    public function __construct($name) {
        parent::__construct($name);
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


class JTL_HiddenField extends JTL_SimpleField {
    protected function draw_fields($post_id) {
        $current_data = esc_attr($this->get($post_id));
        printf('<input type="hidden" id="%s" name="%s" value="%s" />',
               $this->input_name,
               $this->input_name,
               $current_data
        );
    }
    protected function draw_label($post_id) {} // do nothing: hidden field needs no label
    protected function draw_header($null) {}
    protected function draw_footer($null) {}
}



