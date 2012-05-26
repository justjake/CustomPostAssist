<?php
/**
 * Created by JetBrains PhpStorm.
 * User: just.jake@BERKELEY.EDU
 * Date: 5/24/12
 * Time: 3:30 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Media select field from Wordpress's built-in Media library
 */
class JTL_MediaField extends JTL_SimpleField {

    public $button_id = "";
    private $field_actual;

    public function __construct($name) {
        parent::__construct($name);
        $this->button_id = $this->name . '_button';
        $this->remove_id = $this->name . '_remove_media';
        $this->field_actual = new JTL_HiddenField($name);
        $this->input_name = $this->field_actual->input_name;
    }

    public function add_hooks() {
        add_action('admin_enqueue_styles', array(&$this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('jquery');
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style('thickbox');
    }

    protected function draw_label() {}

    public function draw($post_id = null, $no_wrapper = false) {
        $post_id = JTL_Field::Rectify_Post_Id($post_id);
        $data = $this->get($post_id);

        // draw a hidden field that will actually store our data
        $this->field_actual->draw($post_id, true);

        if (! $no_wrapper) $this->draw_header();
        $this->draw_fields_with_data($data);
        if (! $no_wrapper) $this->draw_footer();
    }

    public function draw_input($data) {
        // print label
        if ($data)
            printf(
                '<a href="%s" target="_blank" class="button" title="Linked image">%s</a> ',
                $data,
                __('View Current Media')
            );
        else
            echo __("No media found");


        printf('<a class="thickbox button" href="media-upload.php?post_id=%d&type=image&TB_iframe=true" id="%s">%s</a>',
            $post_id,
            $this->button_id,
            __('Choose Media')
        );
        if ($data)
            printf('<a class="button" href="#nope" id="%s">%s</a>',
                $this->remove_id,
                __('Remove Current Media')
            );
    }

    protected  function draw_footer($post) {
        $post_id = JTL_Field::Rectify_Post_Id($post);
        if (! $post_id) $post_id = 0;
        ?>
    <script type="text/javascript">
        jQuery(function($){
            "hello worst practices ahoy";
            var field_id  = '#<?php echo esc_attr($this->input_name); ?>';
            var button_id = '#<?php echo esc_attr($this->button_id); ?>';
            var remove_id = '#<?php echo esc_attr($this->remove_id); ?>';
            $(remove_id).click(function(e){
                e.preventDefault();
                $(field_id).val('');
            })

            $(button_id).click(function(e){
                e.preventDefault();
                tb_show("", "media-upload.php?post_id=<?php echo $post_id ?>&TB_iframe=true");

                // this is the thickbox/media upload "choose" action
                // we will be swizzling it, so we need a reference to the original
                var _send_to_editor = window.send_to_editor;

                // showing the thing, time to swizzle
                window.send_to_editor = function(html) {
                    var media_url = $('a', html).attr('href');
                    $(field_id).val(media_url);
                    tb_remove();

                    // unswizzle
                    window.send_to_editor = _send_to_editor;
                }
            });
        });
    </script>
    <?php
        parent::draw_footer();
    }

    public function save($post_id = null) {
        $this->field_actual->save($post_id, $this->field_actual->data_from_submission());
    }

    public function get($post_id = null) {
        return $this->field_actual->get($post_id);
    }
}

