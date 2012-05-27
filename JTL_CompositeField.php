<?php
/**
 * JTL_CompositeField.php
 *
 * @author: Jake
 * Date: 5/27/12
 * Time: 2:48 AM
 */

require_once 'JTL_Field';
require_once 'JTL_SimpleField.php'; // only for the demonstration class below

/**
 * groups and manages multiple JTL_SimpleField derivatives
 */
abstract class JTL_CompositeField extends JTL_Field {

    /**
     * Holds this composite field's subfields for the reference implementation
     * optional, you may use regular object properties to store subfields instead
     * should be a map of field roles to field objects
     * @var array[string => JTL_Field]
     */
    protected $subfields = array();


    /**
     * produce human-friendly label text for the given field
     * @abstract
     * @param $role string position of the label in the group
     * @param $label string for this field group
     * @return string
     */
    abstract protected function subfield_label($role, $label);

    /**
     * inititialize the given subfield by returning an appropriate object
     * @abstract
     * @param $role string subfield role
     * @return JTL_Field
     */
    abstract protected function subfield_init($role);

    /**
     * Construct subfields based on thier role string
     * You should not need to redefine this function. See subfield_init instead
     * @param string $name
     */
    public function __construct($name) {
        parent::__construct($name);
        foreach ($this->subfields as $role => $field) {
            $this->subfields[$role] = $this->subfield_init($role);
        }

        // set default labels I guess
        $this->set_label($this->label);
    }



    /**
     * Draw the subfields in the order that they are defined
     * Reimplement for better UI
     * @param $post
     * @param bool $no_wrapper
     */
    public function draw($post, $no_wrapper = false) {
        if (! $no_wrapper) $this->draw_header();
        foreach ($this->subfields as $role => $field) {
            $field->draw($post, false);
        }
        if (! $no_wrapper) $this->draw_footer();
    }

    // the following functions should not need to be reimplemented

    /**
     * Set appropriate label strings for this and its subfields
     * @param $label string
     */
    public function set_label($label) {
        $this->label = esc_attr($label);

        foreach ($this->subfields as $role => $field) {
            $field->label = $this->subfield_label($role, $label);
        }

        return $this->label;
    }

    public function save($post_id, $data) {
        foreach ($this->subfields as $role => $field) {
            $field->save($post_id, $data[$role]);
        }
    }

    public function get($post_id) {
        $results = array();
        foreach ($this->subfields as $role => $field) {
            $results[$role] = $field->get($post_id);
        }
        return $results;
    }

    public function data_from_submission() {
        $results = array();
        foreach ($this->subfields as $role => $field) {
            $results[$role] = $field->data_from_submission();
        }
        return $results;
    }
}


/**
 * an <a href> sort of thing
 */
class JTL_LinkField extends JTL_CompositeField {
    protected $subfields = array(
        'url' => null,
        'text' => null,
        'title' => null
    );

    protected  function subfield_init($role) {
        // all the fields for this are the same
        return new JTL_SimpleField($this->name . '_' . $role);
    }

    protected function subfield_label($role, $label) {
        switch ($role) {
            case 'url':
                return $this->label . ' URL';
            case 'text':
                return $this->label . ' Link Text';
            case 'title':
                return $this->label . ' Link Title';
        }

    }

    public function display($post_id) {
        $data = $this->get($post_id);
        printf('<a href="%s" title="$s">%s</a>',
            $data['url'],
            $data['title'],
            $data['text']
        );
    }

}