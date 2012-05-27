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
 * Quick and easy way to create custom post types without having to
 * re-read the Wordpress documentation and add_action a thousand times
 *
 * you must call CustomPostType->register(); to actually create a custom
 * post type in Wordpress.
 *
 * Usage example:
 * $project = new JTL_CustomPostType('project');
 * $project->add_feature(JTL_CustomPostType::$Basic_Features);
 * $project->fields[0] = new JTL_DateRange('time_span');
 * $project->fields[0]->label = 'Time Span';
 * $project->fields[1] = new JTL_SimpleField('tools');
 * $project->fields[1]->label = 'Tools Used';
 * $project->register();
 *
 */

/**
 * abstract class to hold post feature defenitions
 */
abstract class JTL_PostFeature {
    const Title = 'title';
    const Editor = 'editor';
    const Author = 'author';
    const Thumbnails = 'thumbnail';
    const Excerpt = 'excerpt';
    const Trackbacks = 'trackbacks';
    const Comments = 'comments';
    const Revisions = 'revisions';
    const Page_Attributes = 'page-attributes';
    const Post_Formats = 'post-formats';

    public static $All_Features = array(
        JTL_PostFeature::Title,
        JTL_PostFeature::Editor,
        JTL_PostFeature::Author,
        JTL_PostFeature::Thumbnails,
        JTL_PostFeature::Excerpt,
        JTL_PostFeature::Trackbacks,
        JTL_PostFeature::Comments,
        JTL_PostFeature::Revisions,
        JTL_PostFeature::Page_Attributes,
        JTL_PostFeature::Post_Formats
    );

    public static $Basic_Features = array(
        JTL_PostFeature::Title,
        JTL_PostFeature::Editor,
        JTL_PostFeature::Thumbnails,
        JTL_PostFeature::Revisions
    );
}

class JTL_CustomPostType {
    /**
     * @var string post type name for use in Wordpress
     */
    public $name = "";

    /**
     * @var string Friendly name to show in UIs and things
     */
    public $display_name = "";

    /**
     * @var string Name for the customp post fields box
     */
    public $box_name;

    /**
     * @var bool is this post type public or private?
     */
    public $is_public = false;
    /**
     * @var bool can this post type be nested?
     */
    public $is_hierarchical = false;

    /**
     * @var bool does this post type show up in the Admin UI?
     */
    public $show_ui = true;


   //////////////// private /////////////////

    /**
     * All the fields this custom post type has
     * @var array[JTL_Field]
     */
    private $fields = array();
    private $field_map = array();

    private $labels = array();
    private $was_registered = false;
    private $taxonomies = array();
    private $features = array();

    private $nonce_action = '';
    private $nonce_field_name = '';


    ///////////////////// public functions //////////////////////
    /**
     * @param $name string Initialize a new custom post type with the given Wordpress post type name
     */
    public function __construct($name) {
        $this->name = $name;
        $this->nonce_action = $name . '_post_save';
        $this->nonce_field_name = $name . '_nonce';
        $this->display_name = str_replace('_', ' ', $this->name);
    }

    /**
     * Register this post type with Wordpress. Should be run in the 'init' wordpress action
     */
    public function register() {
        add_action('init', array(&$this, 'register_actual'));
        foreach ($this->fields as $field) {
            $field->register_hooks();
        }
    }

    public function register_actual() {
        if ($this->is_hierarchical)
            $this->add_feature('page-attributes');

        register_post_type(
            $this->name,
            array(
                'labels' => $this->synthesize_labels(),
                'public' => $this->is_public,
                'show_ui' => true,
                'supports' => $this->features,
                'hierarchical' => $this->is_hierarchical
            )
        );
        // prevent some further changes
        $this->was_registered = true;

        // add WP hooks to call our methods at the right times
        if ($this->show_ui) {
            add_action('add_meta_boxes', array(&$this, 'setup_post_meta_box'));
            add_action('save_post', array(&$this, 'save_fields'));
        }
    }

    /**
     * @param $feature string add a feature capability, or a list of features
     */
    public function add_feature($feature) {
        $this->fail_if_registered();
        if (is_array($feature))
            $this->features = array_merge($this->features, $feature);
        else
            $this->features[] = $feature;
    }

    public function get_features() {
        return $this->features;
    }

    /**
     * Add a new field to this custom post type.
     * You may supply an array of fields to add, or just one JTL_Field
     * @param $field JTL_Field
     * @throws Exception
     */
    public function add_field($field) {
        // support bulk adding fields in an array
        if (is_array($field)) {
            foreach ($field as $singular_field) {
                $this->add_field($singular_field);
            }
        } else if (is_subclass_of($field, 'JTL_Field')) {
            // no duplicate field names please
            if (isset($this->field_map[$field->name]))
                throw new Exception('Duplicate field error');

            // some fields use field->parent for things
            $field->set_parent($this);

            // actually add the field
            $this->fields[] = $field;
            $this->field_map[ $field->name ] = $field;
        } else {
            throw new  Exception('Type Error: Not of type JTL_Field');
        }
    }

    /**
     * @param $field_name
     * @return JTL_Field
     */
    public function get_field($field_name) {
        return $this->field_map[$field_name];
    }

    public function latest_field() {
        if (count($this->fields))
            return $this->fields[count($this->fields) - 1];
        else
            return null;
    }

    /**
     * add taxonomy support
     * @param $tax_name string
     */
    public function add_taxonomy($tax_name) {
        $this->fail_if_registered();
        $this->taxonomies[] = $tax_name;
    }

    public function get_taxonomies() {
        return $this->taxonomies;
    }

    /**
     * Create an object that represents a single post of this custom type
     * Helps retrieve fields
     */
    public function instance($post_id = null) {
        $post_id = JTL_Field::Rectify_Post_Id($post_id);
        return new JTL_CustomPostInstance($post_id, $this, $this->field_map);
    }

    public function post_is_instance($post_id = null) {
        $post_id = JTL_Field::Rectify_Post_Id($post_id);
        $post = get_post($post_id);
        return $post->post_type == $this->name;
    }


    public function setup_post_meta_box() {
        if (isset($this->box_name))
            $box_name = $this->box_name;
        else
            $box_name = $this->display_name . ' Fields';

        add_meta_box(
            $this->name . '_box',
            $box_name,
            array(&$this, 'draw_fields'),
            $this->name, // post types to show this on
            'normal',
            'core'
        );
    }

    /**
     * Print the Wordpress Admin from for this post type
     * @param null $post
     */
    public function draw_fields($post = null) {
        // first draw nonce field
        wp_nonce_field($this->nonce_action, $this->nonce_field_name);

        // draw fields from array
        for ($i = 0; $i < count($this->fields); $i ++) {
            $this->fields[$i]->draw($post);
        }
    }

    /**
     * Save new data from a Wordpress Admin POST event
     */
    public function  save_fields($post_id = null) {
        // only continue action for our custom post type
        if (! isset($_POST['post_type']) || (! $this->name == $_POST['post_type']))
            return;

        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        // verify Nonce (aka CSRF protection)
        if (! isset($_POST[$this->nonce_field_name]) || ! wp_verify_nonce($_POST[$this->nonce_field_name], $this->nonce_action))
            return;

        // check user permissions
        if (! current_user_can('edit_post', $post_id))
            return;


        // Authentication successful. We can now save the post data
        for ($i = 0; $i < count($this->fields); $i ++) {
            $this->fields[$i]->save($post_id, $this->fields[$i]->data_from_submission());
        }
    }


    /**
     * Sets a label parameter. Should be used with gettext contexted strings
     * @param $label_type string Label position identifier
     * @param $label_name string Label to show in the UI. Please translate.
     */
    public function set_label($label_type, $label_name) {
        $this->fail_if_registered();
        $this->labels[$label_type] = $label_name;
    }

    /**
     * Given whatever label information provided earlier, synthesize the rest.
     * For use when registering the post type.
     * @return array of labels
     */
    private function synthesize_labels() {
        $pretty_name = $this->display_name;
        $generated =  array(
            'name' => ucwords($pretty_name) . 's',
            'singular_name' => ucwords($pretty_name)
        );

        return array_merge($generated, $this->labels);
    }

    /**
     * throw an exception if this post type has already been registerd
     * @throws Exception
     */
    private function fail_if_registered() {
        if ($this->was_registered)
            throw new Exception('this attribute cannot be modified after the post type has been registered');
    }
}


