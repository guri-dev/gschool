<?php
require('g-class-students.php');
require('class/g-class-teachers.php');

class GSettings {
	
	public $students_obj;

	function __construct()
	{
		add_action( 'admin_menu', array( $this, 'g_admin_menu' ), 9 );
	}

	function g_admin_menu() {
	    add_menu_page(
	        __( 'G School', 'gschool' ),
	        __( 'G School', 'gschool' ),
	        'manage_options',
	        'gschool_dashboard',
	        array( $this, 'g_dashboard_func' ),
	        'dashicons-chart-bar'
		);
		add_submenu_page('gschool_dashboard', __('Manage Students'), __('Manage Students'), 'edit_themes', 'gschool_students', array( $this, 'gschool_students_render' ));
		add_submenu_page('gschool_dashboard', __('Manage Teachers'), __('Manage Teachers'), 'edit_themes', 'gschool_teachers', array($this,'gschool_teachers_render'));
		add_submenu_page('gschool_dashboard', __('Manage Attendance'), __('Manage Attendance'), 'edit_themes', 'gschool_attendance', 'gschool_attendance_render');
		add_submenu_page('gschool_dashboard', __('Manage Fee'), __('Manage Fee'), 'edit_themes', 'gschool_fee', 'gschool_fee_render');
		add_submenu_page('gschool_dashboard', __('Manage Timetable'), __('Manage Timetable'), 'edit_themes', 'gschool_timetable', 'gschool_timetable_render');
		add_submenu_page('gschool_dashboard', __('Manage Syllabus'), __('Manage Syllabus'), 'edit_themes', 'gschool_syllabus', 'gschool_syllabus_render');
		add_submenu_page('gschool_dashboard', __('Manage Classes'), __('Manage Classes'), 'edit_themes', 'gschool_classes', 'gschool_classes_render');
		add_submenu_page('gschool_dashboard', __('Manage Result'), __('Manage Result'), 'edit_themes', 'gschool_result', 'gschool_result_render');
		$this->students_obj = new Students_List();
		$this->teachers_obj = new Teachers_List();
	}

	public function gschool_students_render()
	{

		if(isset($_GET['gaction']) && !empty($_GET['gaction']))
		{
			$gaction = $_GET['gaction'];
		}
		else
		{
			$gaction = 'dashboard';
		}		

		switch ($gaction) {
			case 'dashboard':
				require("views/gs_students_list.php");
				break;
			case 'new':
				require("views/gs_students_new.php");
				break;	
		}
	}


	public function gschool_teachers_render()
	{

		if(isset($_GET['gaction']) && !empty($_GET['gaction']))
		{
			$gaction = $_GET['gaction'];
		}
		else
		{
			$gaction = 'dashboard';
		}

		switch ($gaction) {
			case 'dashboard':
				require("views/gs_teachers_list.php");
				break;
			case 'new':
				require("views/gs_teachers_new.php");
				break;	
		}
	}


	public function g_dashboard_func()
	{ 
	?>
		<div class="wrap">
			<h2>WP_List_Table Class Example</h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->students_obj->prepare_items();
								$this->students_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}


	function fc_set_option($option_name,$new_value)
	{
		if ( get_option( $option_name ) !== false ) { 
		    update_option( $option_name, $new_value );
		} else {
		    $deprecated = null;
		    $autoload = 'no';
		    add_option( $option_name, $new_value, $deprecated, $autoload );
		}
	}

	public function fc_get_option($option_name)
	{
		if( get_option( $option_name ) !== false ){
		    return get_option( $option_name );
		}
		else
		{
			return '';
		} 
	}


 }
 
 ?>
