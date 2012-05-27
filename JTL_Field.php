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

interface JTL_PostField {
    /**
     * return the value of this field
     * @param $post_id int
     */
    public function get($post_id);

    /**
     * display an HTML representation of this field's data
     * @abstract
     * @param $post_id int
     * @return null
     */
    public function display($post_id);

    /**
     * Draw the form fields and associated HTML content for this field
     * @param $post
     * @param $no_wrapper bool Whether or not to print the header and footer for the field form
     */
    public function draw($post, $no_wrapper = false);

    /**
     * Create data representation from posted form information.
     * Should be used by $this->save to
     * @param mixed $existing_data the currently existing PHP data of this field
     * @return mixed
     */
    public function data_from_submission();

    /**
     * Save the form data. Security is handled by JTL_CustomPostType::save_fields
     * @param int $post_id
     * @param mixed $data
     */
    public function save($post_id, $data);

    /**
     * set up any Wordpress actions, filters, or hooks that this field will need.
     * @see JTL_CustomPostType::register()
     * @abstract
     * @return mixed
     */
    public function register_hooks();
}

abstract class JTL_Field implements JTL_PostField
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $label;

    protected $parent;
    protected $input_name;

    /**
     * Tries to return a valid post id given a post object, post id, or null.
     * Returns 0 instead of null
     * @static
     * @param null $post_id
     * @return int
     */
    public static function Rectify_Post_Id($post_id = null) {
        if ($post_id === null) {
            global $the_post;
            if (isset($the_post))
                $post_id = $the_post->ID;
        }
        if (is_object($post_id))
            $post_id = $post_id->ID;

        if ($post_id === null)
            $post_id = 0;

        return $post_id;
    }

    /**
     * Check to see if the given field uses any of the unique names in names
     * @static
     * @param $names array
     * @param $field JTL_Field
     */
    public static function Assert_No_Conflict($field, $names) {
        foreach ($field->used_names() as $name) {
            if (in_array($name, $names))
                throw new Exception('Unique Name Error: field names conflict');
        }
    }

    /**
     * Create a new field with the given name, which will be used as the basis
     * of both the default field label, and the field input ID and NAME attributes.
     * As such, field names must be unique.
     * @param $name string
     */
    public function __construct($name) {
        $this->name = $name;
        $this->input_name = esc_attr($this->name . '_input');
        $this->label = ucwords(str_replace('_', ' ', $this->name));
    }

    public function set_parent($parent) {
        $this->parent = $parent;
    }


    /**
     * Return an array of all the unique names this input uses
     * @return array
     */
    public function used_names() {
        return array($this->name);
    }



    // RETRIEVAL /////////////////////////////////

    public function get($post_id) {
        return get_post_meta($post_id, $this->name, true);
    }


    // ADMIN UI /////////////////////////////////

    public function draw($post, $no_wrapper = false) {
        $post_id = JTL_Field::Rectify_Post_Id($post);
        $data = $this->get($post_id);


        if (! $no_wrapper) $this->draw_header();
        $this->draw_fields_with_data($data);
        if (! $no_wrapper) $this->draw_footer();
    }

    /**
     * Draw the Admin UI for this field, given its data for this post
     * @param mixed $data
     */
    protected function draw_fields_with_data($data) {}

    /**
     * Draw the HTML section header for this field
     * @param $post
     */
    protected function draw_header() {}

    /**
     * Draw the HTML section footer for this field
     * @param $post
     */
    protected function draw_footer() {}


    // POST & SAVE  /////////////////////////////////

    public function save($post_id, $data) {
        update_post_meta($post_id, $this->name, $data);
    }

    // HOOKS
    public function register_hooks() {}
}


