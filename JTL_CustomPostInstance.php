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
 * CustomPostInstance
 * help retrieve and show field data from inside a loop
 * create using JTL_CustomPostType->post($id)
 */
class JTL_CustomPostInstance {
    /**
     * @var string custom post type
     */
    public $type;
    /**
     * @var array list of availible fields for this post
     */
    public $fields;
    /**
     * @var int ID of this post
     */
    public $id;

    private $parent;
    private $field_map;

    public function __construct($post_id, $custom_post_obj, $field_map) {
        $this->id = $post_id;
        $this->parent = $custom_post_obj;
        $this->field_map = $field_map;

        $this->fields = array_keys($field_map);
        $this->type = $custom_post_obj->name;
    }


    /**
     * Print the given field as a template tag
     * @param $field_name
     */
    public function the_field($field_name) {
        $this->field_map[$field_name]->display($this->id);
    }
    /**
     * Return a representation of the field for use in PHP
     * @param $field_name string
     * @return mixed
     */
    public function get_field($field_name) {
        return $this->field_map[$field_name]->get($this->id);
    }
}

