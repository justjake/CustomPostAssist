<?php
class JTL_MutiField extends JTL_Field {

    /**
     * Stores a field that serves as a template
     * @var JTL_Field
     */
    public $label = '';
    protected $template_field;

    protected function draw_header($post_id) {
        echo '<section class="jtl-multi-field">';
    }
    protected function draw_footer($post_id) {
        echo '</section>';
    }
    protected function draw_fields($post = null) {
        $post_id = JTL_Field::Rectify_Post_Id($post);
        printf("<h1>%s</h1>\n", $this->label);

        $data = $this->get($post_id);
        $data = $data ? $data: array("");
        foreach ($data as $single_field_data) {

        }
    }
    public function save($post_id = null) {
        $post_id = JTL_Field::Rectify_Post_Id($post_id);
        $results = null;
    }

}
